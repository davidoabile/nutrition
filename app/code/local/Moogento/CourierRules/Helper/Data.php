<?php

class Moogento_CourierRules_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOG_FILE = 'moogento_courierrules.log';

    protected $_rules = null;

    protected $_zones = null;

    protected $_methodData = null;

    protected $_isProductAtributeMultiple = null;

    protected $_attribute = null;

    protected $_dropdownOptions = null;

    public function getZones()
    {
        if (is_null($this->_zones)) {
            $this->_zones = Mage::getModel('moogento_courierrules/zone')->getCollection();
        }

        return $this->_zones;
    }

    public function getZoneById($id)
    {
        return $this->getZones()->getItemById($id);
    }

    /**
     * @return Moogento_CourierRules_Model_Rule[]
     */
    public function getRules()
    {
        if (is_null($this->_rules)) {
            $collection = Mage::getModel('moogento_courierrules/rule')->getCollection();

            $collection->addOrder('sort', Varien_Data_Collection_Db::SORT_ORDER_ASC);
            $collection->addFieldToFilter('active', 1);

            $this->_rules = $collection;
        }

        return $this->_rules;
    }

    /**
     * @param $order Mage_Sales_Model_Order
     */
    public function processOrder($order)
    {
        $suggestion = Mage::getModel('moogento_courierrules/connector_suggestion')->load($order->getId(), 'order_id');
        if ($suggestion->getId()) {
            $suggestion->delete();
        }
        foreach ($this->getRules() as $rule) {
            if ($rule->validate($order)) {

                $description =
                    $rule->getData('partial_match') ? 'Manual Check - Unclear' :
                        ($rule->getCourierrulesMethod() == Moogento_CourierRules_Model_Rule::CUSTOM_METHOD ? $rule->getTargetCustom() : $this->getShippingDescription($rule->getCourierrulesMethod()));
                $tracking = null;
                if (($order->canShip() || $this->_orderHasShipmentsWithoutTracks($order)) && $rule->getTrackingId() && !$order->getCourierrulesTracking() && !$rule->getData('partial_match')) {
                    $tracking = $rule->useTracking();
                }

                $this->updateCourierRule($order, $rule->getId(), $rule->getCourierrulesMethod(), $description, true, $tracking);
                $this->_logProcessing($order);
                return;
            }
        }

        $this->_logProcessing($order);
        $order->setCourierrulesProcessed(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
        $order->save();
    }

    protected function _logProcessing($order)
    {
        $cronLogSetting = (int)Mage::getStoreConfig('courierrules/settings/cron_log');
        if ($cronLogSetting != Moogento_CourierRules_Model_Observer::CRON_NO_LOG) {
            if ($cronLogSetting == Moogento_CourierRules_Model_Observer::CRON_LOG_ALL) {
                Mage::log($this->_getLogMessage($order), null, self::LOG_FILE);
            } else {
                if (!$order->getCourierrulesRuleId()) {
                    Mage::log($this->_getLogMessage($order), null, self::LOG_FILE);
                    if (Mage::getStoreConfigFlag('courierrules/settings/cron_email') && Mage::getStoreConfig('courierrules/settings/cron_email_to')) {
                        $translate = Mage::getSingleton('core/translate');
                        /* @var $translate Mage_Core_Model_Translate */
                        $translate->setTranslateInline(false);

                        Mage::getModel('core/email_template')
                            ->sendTransactional(
                                Mage::getStoreConfig('courierrules/email/cron_empty'),
                                Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY),
                                Mage::getStoreConfig('courierrules/settings/cron_email_to'),
                                null,
                                array(
                                    'order_id'  => $order->getIncrementId(),
                                    'shipping_method' => $order->getShippingDescription(),
                                )
                            );

                        $translate->setTranslateInline(true);
                    }
                }
            }
        }
    }

    public function updateCourierRule($order, $ruleId, $method, $description, $processed = true, $tracking = null)
    {
        $shipmentIds = array();
        foreach ($order->getShipmentsCollection() as $shipment) {
            $shipmentIds[] = $shipment->getId();
        }

        if (count($shipmentIds) && $order->getCourierrules() != $method) {
            $conectorCollection = Mage::getModel('moogento_courierrules/connector')->getCollection();
            $conectorCollection->getSelect()->where('main_table.shipment_id in (' . implode(',', $shipmentIds) . ')');

            foreach ($conectorCollection as $connector) {
                if ($connector->getCommitted()) {
                    throw new Moogento_CourierRules_Model_Exception($this->__('CourierRule cannot be changed because shipment was already committed'));
                }
            }

            foreach ($conectorCollection as $connector) {
                $connector->deleteShipment();
            }
        }


        $order->setCourierrulesRuleId($ruleId);
        $order->setCourierrules($method);
        $order->setCourierrulesDescription($description);
        $order->setCourierrulesProcessed($processed ? date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())) : null);
        $order->setCourierrulesTracking($tracking);
        $order->save();

        $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($order->getCourierrules());
        if($connectorInfo) {
            $service = $connectorInfo['service'];
            if($service->getEnabled() && $service->getAutoCreateShipment()) {
                $this->_createShipments($order);
            }
        }

        foreach ($order->getShipmentsCollection() as $shipment) {
            if ($order->getCourierrulesTracking() && count($shipment->getAllTracks()) == 0) {
                @list($trackingNumber, $carrier) = explode('||', $order->getCourierrulesTracking());
                Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                $order->setCourierrulesTracking('');
                $order->save();
                $shipment->save();
            }
        }
    }

    protected function _createShipments($order)
    {
        // Create shipments
        $qty=array();
        foreach($order->getAllItems() as $eachOrderItem){
            $ItemQty = 0;
            $ItemQty = $eachOrderItem->getQtyOrdered() - $eachOrderItem->getQtyShipped() - $eachOrderItem->getQtyRefunded() - $eachOrderItem->getQtyCanceled();
            $qty[$eachOrderItem->getId()] = $ItemQty;

        }

        $email=true;
        $includeComment=true;
        $comment="test Shipment";
        if ($order->canShip()) {
            $shipment = $order->prepareShipment($qty);
            if ($shipment) {
                $shipment->register();
                $shipment->addComment($comment, $email && $includeComment);
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                    $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                } catch (Mage_Core_Exception $e) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function _getLogMessage($order)
    {
        $message = '';
        if ($order->getCourierrulesRuleId()) {
            $message .= 'PROCESSED: ';
        } else {
            $message .= 'NOMATCH: ';
        }
        $message .= '#' . $order->getIncrementId() . ' Shipping method: ' . $order->getShippingDescription();
        return $message;
    }

    public function _orderHasShipmentsWithoutTracks($order) {
        foreach ($order->getShipmentsCollection() as $shipment) {
            if (count($shipment->getAllTracks()) == 0) {
                return true;
            }
        }

        return false;
    }

    public function getShippingDescription($method)
    {
        $manager = Mage::getSingleton('moogento_courierrules/connector_manager');

        $data = $this->getShippingMethodData();
        if (isset($data[$method])) {
            return $data[$method];
        }
        else {
            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($method);
            if($connectorInfo) {
                $connector = $connectorInfo['connector'];
                $carrier = $connectorInfo['carrier'];
                $service = $connectorInfo['service'];
                return $connector->getName() . ' - ' . $carrier->getLabel() . ' - ' . $service->getLabel();
            }
        }
        return '';
    }

    public function getShippingMethodData()
    {
        if (is_null($this->_methodData)) {
            $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

            $this->_methodData = array(
                Moogento_CourierRules_Model_Rule::CONNECTOR_METHOD => $this->__('Connector suggestion')
            );

            foreach($methods as $_ccode => $_carrier)
            {
                if($_methods = $_carrier->getAllowedMethods())
                {
                    if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title")) {
                        $_title = $_ccode;
                    }

                    foreach($_methods as $_mcode => $_method)
                    {
                        $_code = $_ccode . '_' . $_mcode;
                        $this->_methodData[$_code] = trim($_title . ' - ' . $_method, ' -');
                    }
                }
            }
        }

        return $this->_methodData;
    }

    public function isProductAttributeMultiple($product_attribute)
    {
        $attribute = $this->getAttribute($product_attribute);
        return ($attribute && $attribute->getFrontend()->getInputType() == 'multiselect');
    }

    public function isProductAttributeNumeric($product_attribute)
    {
        $attribute = $this->getAttribute($product_attribute);
        return ($attribute && (
                    ($attribute->getBackendType() == 'int') || 
                    ($attribute->getBackendType() == 'decimal')) && 
                ($attribute->getFrontend()->getInputType() != 'select') && 
                ($attribute->getFrontend()->getInputType() != 'multiselect'));
    }

    public function getAttribute($product_attribute)
    {
        $attributeCode = Mage::getStoreConfig('courierrules/settings/'.$product_attribute);
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
    }

    public function resetAttribute()
    {
        $this->_attribute = null;
    }

    public function getCourierRulesDropdownOptions()
    {
        if (is_null($this->_dropdownOptions)) {
            $this->_dropdownOptions = array();
            foreach (Mage::getModel('moogento_courierrules/rule')->getCollection() as $rule) {
                $text = ($rule->getCourierrulesMethod() == Moogento_CourierRules_Model_Rule::CUSTOM_METHOD ? $rule->getTargetCustom() : $this->getShippingDescription($rule->getCourierrulesMethod()));
                $this->_dropdownOptions[$text] = $text;
            }
        }

        return $this->_dropdownOptions;
    }

    public function getCourierRulesToOptionsArray()
    {
        $result_array = array();
//        foreach (Mage::getModel('moogento_courierrules/rule')->getCollection() as $rule) {
//            $text = ($rule->getCourierrulesMethod() == Moogento_CourierRules_Model_Rule::CUSTOM_METHOD ? $rule->getTargetCustom() : $this->getShippingDescription($rule->getCourierrulesMethod()));
//            $result_array[] = array("label" => $text, "value" => $rule->getId());
//        }
        foreach ($this->getCourierRulesDropdownOptions() as $rule) {
            $result_array[] = array("label" => $rule, "value" => $rule);
        }
        $result_array[] = array("label" => 'Custom value', "value" => 'custom_value');
        return $result_array;
    }
    
    public function getRulesArrayForHTML()
    {
        $rules = $this->getRules();
        $result = array();
        $result["empty"] = "";
        foreach($rules as $rule) {
            $result[$rule->getId()] = $rule->getName();
        }
        $result["custom"] = Mage::helper('moogento_courierrules')->__("Custom");
        return $result;
    }    
    
    public static function setCourierRulesMethodFilter($collection, $column)
    {
        $select = $collection->getSelect();
       
        if(is_null($column->getFilter()->getValue('szy_filter_courierrules_description'))){
            $value = $column->getFilter()->getValue('value');
            $pos = strpos($value, "groups___");
            if($pos === false){
                $select->where("main_table.courierrules_description like ?", "%".$value."%");
            } else {
                $group = str_replace("groups___", "", $value);
                $not_single_groups = unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group'));
                $sql_result = "";
                $sql_array = array();
                foreach ($not_single_groups[$group]["courierrules"] as $key => $elem){
                    if($key!=0) $sql_result .= " OR ";
                    $val = ($elem != "custom_value") ? $elem : $not_single_groups[$group]["custom_value"];
                    $sql_result .= self::quoteInto("main_table.courierrules_description like ?", "%".$val."%");
                }
                $select->where($sql_result);
            }            
        } else {
            $select->where("main_table.courierrules_description like ?","%".$column->getFilter()->getValue('szy_filter_courierrules_description')."%");
        }
        $select->group('main_table.entity_id');  
    }
    
    public static function quoteInto($text)
    {
        // get function arguments
        $args = func_get_args();
 
        // remove $text from the array
        array_shift($args);
 
        // check if the first parameter is an array and loop through that instead
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
 
        // replace each question mark with the respective value
        foreach ($args as $arg) {
            $text = preg_replace('/\?{1}/', Mage::getSingleton('core/resource')->getConnection('core_read')->quote($arg), $text, 1);
        }
 
        // return processed text
        return $text;
    }

    public static function setConnectorFilter($collection, $column)
    {
        $select = $collection->getSelect();

        $value = $column->getFilter()->getValue();

        if ($value) {
            $select->joinLeft(
                array('shipment_connector' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
                'main_table.entity_id = shipment_connector.order_id',
                array()
            );
            $select->joinLeft(
                array('connector' => Mage::getSingleton('core/resource')->getTableName('moogento_courierrules/connector')),
                'connector.shipment_id = shipment_connector.entity_id',
                array()
            );

            switch ($value) {
                case 'not_processed':
                    $select->where('connector.shipment_id is NULL');
                    break;
                case 'created':
                    $select->where('connector.label is NOT NULL');
                    break;
                case 'not_created':
                    $select->where('connector.label is NULL AND connector.status != "DELETED"');
                    break;
                case 'deleted':
                    $select->where('connector.status = "DELETED"');
                    break;
            }
        }
    }

    public function getRefererUrl()
    {
        $request = Mage::app()->getRequest();
        $refererUrl = $request->getServer('HTTP_REFERER');
        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = $request->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }

    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            /**
             * Url must start from base secure or base unsecure url
             */
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }

        return false;
    }
} 