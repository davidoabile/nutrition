<?php
/** @noinspection PhpIncludeInspection */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php';

class Moogento_Core_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';

        $grid       = $this->getLayout()->createBlock('moogento_core/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('moogento_core/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function massCancelAction()
    {
        if ($this->getRequest()->isPost()) {

            $orderIds     = $this->getRequest()->getPost('order_ids', array());
            $countSuccess = 0;
            $countFail    = 0;

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            foreach ($orderIds as $id) {
                try {
                    if (Mage::helper('moogento_core')->changeOrderStatus($id, Mage_Sales_Model_Order::STATE_CANCELED, '', $notifyCustomer)) {
                        $countSuccess++;
                    } else {
                        $countFail++;
                    }
                } catch (Exception $e) {
                    $countFail++;
                }
            }
            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) cannot be cancelled.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) cannot be cancelled.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) have been cancelled.'));
            }

        }
        $this->_redirect('*/sales_order/');
    }
} 