<?php


class Moogento_Core_Sales_OrderController extends Mage_Adminhtml_Controller_Action
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
} 