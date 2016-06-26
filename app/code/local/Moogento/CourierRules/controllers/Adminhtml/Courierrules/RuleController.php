<?php


class Moogento_CourierRules_Adminhtml_Courierrules_RuleController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function exportCsvAction()
    {
        $fileName   = 'cr_rules.csv';
        $grid       = $this->getLayout()->createBlock('moogento_courierrules/adminhtml_rule_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function reprocessAction()
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $table = $resource->getTableName('sales/order');
        $query = "UPDATE {$table} SET courierrules = NULL, courierrules_description = NULL, courierrules_processed = NULL, courierrules_rule_id = null, courierrules_tracking = NULL WHERE courierrules_rule_id is NULL";
        $writeConnection->query($query);

        $table = $resource->getTableName('sales/order_grid');
        $query = "UPDATE {$table} SET courierrules_description = NULL, courierrules_processed = NULL, courierrules_rule_id = null, courierrules_tracking = NULL WHERE courierrules_rule_id is NULL";
        $writeConnection->query($query);

        $this->_getSession()->addSuccess(Mage::helper('moogento_courierrules')->__('Orders with no rule assigned were marked for cron reprocessing'));

        $this->_redirectReferer();
    }

    public function reprocessAllAction()
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $collection = Mage::getModel('moogento_courierrules/connector')->getCollection();
        foreach ($collection as $connector) {
            if (!$connector->getCommitted()) {
                $connector->deleteShipment();
                $connector->delete();
            }
        }

        $table = $resource->getTableName('sales/order');
        $query = "UPDATE {$table} SET courierrules = NULL, courierrules_description = NULL, courierrules_processed = NULL, courierrules_rule_id = null, courierrules_tracking = NULL
                  WHERE entity_id NOT IN (select order_id from {$resource->getTableName('sales/shipment')} s INNER JOIN {$resource->getTableName('moogento_courierrules/connector')} connector on connector.shipment_id = s.entity_id) ";

        $writeConnection->query($query);

        $table = $resource->getTableName('sales/order_grid');
        $query = "UPDATE {$table} SET courierrules_description = NULL, courierrules_processed = NULL, courierrules_rule_id = null, courierrules_tracking = NULL WHERE entity_id NOT IN (select order_id from {$resource->getTableName('sales/shipment')} s INNER JOIN {$resource->getTableName('moogento_courierrules/connector')} connector on connector.shipment_id = s.entity_id)";
        $writeConnection->query($query);

        $this->_getSession()->addSuccess(Mage::helper('moogento_courierrules')->__('All orders were marked for cron reprocessing'));

        $this->_redirectReferer();
    }
    
    public function updateCourierRuleOrderAction()
    {
        $ruleId = $this->getRequest()->getPost('rule_id');
        $order_id = $this->getRequest()->getPost('order_id');
        $custom_val = !is_null($this->getRequest()->getPost('input_custom')) ? $this->getRequest()->getPost('input_custom') : "";
        $change_track = !is_null($this->getRequest()->getPost('change_track')) ? $this->getRequest()->getPost('change_track') : false;
        echo $this->_setCRMethod($ruleId, $order_id, $custom_val, $change_track);
    }
    
    public function updateCourierRuleOrdersAction()
    {
        if ($this->getRequest()->isPost()) {
            $orderIds = $this->getRequest()->getPost('order_ids', array());
            $ruleId = $this->getRequest()->getPost('cr_method');
            $custom_val = !is_null($this->getRequest()->getPost('cr_method_custom')) ? $this->getRequest()->getPost('cr_method_custom') : "";
            $change_track = !is_null($this->getRequest()->getPost('cr_method_change_track')) ? $this->getRequest()->getPost('cr_method_change_track') : false;
            foreach($orderIds as $id) {
                $this->_setCRMethod($ruleId, $id, $custom_val, $change_track);
            }
        }
        $this->_redirect('*/sales_order/');
    }
    
    private function _setCRMethod($ruleId, $order_id, $custom_val, $change_track)
    {
        $order = Mage::getModel("sales/order")->load($order_id);
        $helper = Mage::helper('moogento_courierrules');
        switch ($ruleId) {
            case "custom":
                $helper->updateCourierRule($order, NULL, '__custom__', $custom_val);
                $result_answer = '<p data-track="'.$order->getCourierrulesTracking().'">'.$order->getCourierrulesDescription()."</p>";
                $order->save();
                break;
            case "empty":
                $helper->updateCourierRule($order, NULL, NULL, null, false);
                $result_answer = '<p style="color:grey;font-style:italic;" data-track="'.$order->getCourierrulesTracking().'" title="">'.$this->__("Pending Sync")."</p>";
                $order->save();
                break;
            default:
                $rule = Mage::getModel("moogento_courierrules/rule")->load($ruleId);

                $description =
                    $rule->getData('partial_match') ? 
                        'Manual Check - Unclear' :
                        ($rule->getCourierrulesMethod() == Moogento_CourierRules_Model_Rule::CUSTOM_METHOD ? 
                            $rule->getTargetCustom() : 
                            Mage::helper('moogento_courierrules')->getShippingDescription($rule->getCourierrulesMethod()));

                $tracking = null;
                if(is_null($order->getCourierrulesTracking())){
                    $tracking = $this->_saveCourierrulesTracking($order, $rule);
                } else {
                    if($change_track){
                        $tracking = $this->_saveCourierrulesTracking($order, $rule);
                    }
                }

                $helper->updateCourierRule($order, $rule->getId(), $rule->getCourierrulesMethod(), $description, true, $tracking);

                $result_answer = '<p data-track="'.$order->getCourierrulesTracking().'">'.$order->getCourierrulesDescription()."</p>";
        }
        return $result_answer;        
    }
    
    private function _saveCourierrulesTracking($order, $rule)
    {
        if(
            (
                $order->canShip() || 
                Mage::helper('moogento_courierrules')->_orderHasShipmentsWithoutTracks($order)
            ) && 
            $rule->getTrackingId() && 
            !$order->getCourierrulesTracking() && !$rule->getData('partial_match')
        ) {
            return $rule->useTracking();
        }

        return null;
    }
} 