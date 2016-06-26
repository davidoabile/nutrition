<?php

/** @noinspection PhpIncludeInspection */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php';

class Moogento_CourierRules_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Hold order
     */
    public function processCourierRulesAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('moogento_courierrules')->processOrder($order);
                $this->_getSession()->addSuccess(
                    $this->__('The order courier rules was processed.')
                );
            }
            catch (Moogento_CourierRules_Model_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order courier rules could not be processed.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    public function massProcessCourierRulesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $processed = 0;
        $notProcessed = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            try {
                Mage::helper('moogento_courierrules')->processOrder($order);
                $processed++;
            } catch (Moogento_CourierRules_Model_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $notProcessed++;
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('Courier rules for order #%s could not be processed.', $order->getIncrementId()));
                $notProcessed++;
            }
        }
        if ($notProcessed) {
            $this->_getSession()->addError($this->__('%s order(s) have not been processed', $notProcessed));
        }
        if ($processed) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been processed.', $processed));
        }
        $this->_redirect('*/*/');
    }
} 