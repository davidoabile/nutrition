<?php

class Moogento_ProfitEasy_Adminhtml_Order_CostsController extends Mage_Adminhtml_Controller_Action
{
    public function shippingAction()
    {
        $response = array();
        $data = $this->getRequest()->getParams();
        $order = Mage::getModel('sales/order')->load($data['order_id']);
        try {
            if (!strlen($data['shipping_amount'])) {
                $order->setShippingCost(null);
            } else {
                $order->setShippingCost($data['shipping_amount']);
            }
            $order->setData('profit_calculated', 0);
            $order->save();

            $additionalBlock = Mage::app()->getLayout()->createBlock('adminhtml/template');
            $additionalBlock->setTemplate('moogento/profiteasy/profit.phtml');
            $additionalBlock->setOrder($order);
            $response['html'] = $additionalBlock->toHtml();
            $response['success'] = true;
        } catch (Exception $e) {
            $response['msg'] = $e;
            $response['success'] = false;
        }
        $json = json_encode($response);
        $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/json')->setBody($json);
    }

    public function additionalAction()
    {
        $response = array();
        $data = $this->getRequest()->getParams();
        $order = Mage::getModel('sales/order')->load($data['order_id']);
        try {
            $additionalCosts = $this->getRequest()->getPost('additional_costs', array());
            $ids = array();
            foreach ($additionalCosts as $id => $costsData) {
                $model = Mage::getModel('moogento_profiteasy/costs_order')->load($id);
                $model->setOrderId($order->getId());
                if (!$costsData['rule_id']) {
                    $costsData['rule_id'] = null;
                }
                $model->addData($costsData);
                $model->save();
                $ids[] = $model->getId();
            }

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $costsOrderTable = Mage::getSingleton('core/resource')->getTableName('moogento_profiteasy/costs_order');
            if (count($ids)) {
                $query = "DELETE FROM {$costsOrderTable} WHERE order_id = " . $order->getId() . " AND id not in (" . implode(',', $ids) . ")";
            } else {
                $query = "DELETE FROM {$costsOrderTable} WHERE order_id = " . $order->getId();
            }
            $write->query($query);

            $order->setData('profit_calculated', 0);
            $order->save();

            $additionalBlock = Mage::app()->getLayout()->createBlock('adminhtml/template');
            $additionalBlock->setTemplate('moogento/profiteasy/profit.phtml');
            $additionalBlock->setOrder($order);
            $response['html'] = $additionalBlock->toHtml();
            $response['success'] = true;
        } catch (Exception $e) {
            $response['msg'] = $e;
            $response['success'] = false;
        }
        $json = json_encode($response);
        $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/json')->setBody($json);
    }

    public function resetAction()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $costsTable = Mage::getSingleton('core/resource')->getTableName('moogento_profiteasy/costs');
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');

        $query = "DELETE FROM {$costsTable} WHERE 1=1";
        $write->query($query);

        Mage::helper('moogento_profiteasy')->resetDefaults();

        $query = "UPDATE {$orderTable} SET profit_calculated = 0";
        $write->query($query);
        $query = "UPDATE {$orderGridTable} SET profit_calculated = 0";
        $write->query($query);

        $this->_redirectReferer();
    }
}