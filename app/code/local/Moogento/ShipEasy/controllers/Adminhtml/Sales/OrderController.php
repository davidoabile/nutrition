<?php 
require_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

class Moogento_ShipEasy_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function massCancelAction()
    {
        $cancelType = $this->getRequest()->getParam('cancel_type', 0);
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if ($cancelType) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->canCancel()) {
                    try {
                        $order->cancel()
                            ->save();
                        $countCancelOrder++;
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $countNonCancelOrder++;
                    }
                } else if ($order->canCreditmemo()) {
                    try {
                        $service = Mage::getModel('sales/service_order', $order);
                        $creditmemo = $service->prepareCreditmemo();
                        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                            $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                        }
                        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
                        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);
                        if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                            Mage::throwException(
                                $this->__('Credit memo\'s total must be positive.')
                            );
                        }
                        $creditmemo->setRefundRequested(true);
                        $creditmemo->register();
                        $creditmemo->setEmailSent(true);
                        $creditmemo->getOrder()->setCustomerNoteNotify(true);

                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($creditmemo)
                            ->addObject($creditmemo->getOrder());
                        $transactionSave->save();

                        $creditmemo->sendEmail(true);
                        $countCancelOrder++;
                    }  catch (Exception $e) {
                        Mage::logException($e);
                        $countNonCancelOrder++;
                    }
                } else {
                    $countNonCancelOrder++;
                }
            }
        } else {
            foreach ($orderIds as $orderId) {
                try {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    if ($order->getId()) {
                        $order->setState(
                            Mage_Sales_Model_Order::STATE_CANCELED,
                            true,
                            ''
                        );
                        $order->save();
                        $countCancelOrder++;
                    } else {
                        $countNonCancelOrder++;
                    }

                } catch (Exception $e) {
                    $countNonCancelOrder++;
                }
            }
        }

        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($countNonCancelOrder.' '.Mage::helper('moogento_shipeasy')->__('order(s) cannot be canceled.'));
            } else {
                $this->_getSession()->addError(Mage::helper('moogento_shipeasy')->__('The order(s) cannot be canceled.'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($countCancelOrder.' '.Mage::helper('moogento_shipeasy')->__('order(s) have been canceled.'));
        }
        $this->_redirect('*/*/');
    }
	/**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';

        $grid       = $this->getLayout()->createBlock('moogento_core/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
}
