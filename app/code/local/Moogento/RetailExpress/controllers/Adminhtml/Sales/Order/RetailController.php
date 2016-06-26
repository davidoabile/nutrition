<?php


class Moogento_RetailExpress_Adminhtml_Sales_Order_RetailController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function sendAction()
    {
        $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
        $helper = Mage::helper('moogento_retailexpress');

        if ($order->getId()) {
            if (!$order->getRetailExpressStatus()
                || $order->getRetailExpressStatus() == Moogento_RetailExpress_Model_Retailexpress_Status::PENDING
                || $order->getRetailExpressStatus() == Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY
                || $order->getRetailExpressStatus() == Moogento_RetailExpress_Model_Retailexpress_Status::ERROR) {
                $connector = Mage::getModel('moogento_retailexpress/connector');
                $connector->processOrder($order, true);
            } else {
                $this->_getSession()->addError($helper->__('Order was already processed by retailExpress'));
            }
        } else {
            $this->_getSession()->addError($helper->__('Order does not exists'));
        }
        $this->_redirectReferer();
    }

    public function markSuccessAction()
    {
        $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
        $helper = Mage::helper('moogento_retailexpress');

        if ($order->getId()) {
            $order->setRetailExpressStatus(Moogento_RetailExpress_Model_Retailexpress_Status::SUCCESS_MANUAL);
            $order->setRetailExpressMessage('');
            $message = $helper->__('rEx status marked as Success(Manual)');
            $order->addStatusHistoryComment($message, false);
            $order->save();
            $this->_getSession()->addSuccess($message);
        } else {
            $this->_getSession()->addError($helper->__('Order does not exists'));
        }
        $this->_redirectReferer();
    }
}