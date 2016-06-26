<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        InvoiceController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

// require_once("Mage/Adminhtml/controllers/Sales/InvoiceController.php");
$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/InvoiceController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_InvoiceController extends Mage_Adminhtml_Sales_InvoiceController
{
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Class Constructor
     * call the parent Constructor
     */

    public function __constuct()
    {
        parent::__construct();
    }


    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        // ->_addBreadcrumb($this->__('Orders'), $this->__('Trash Orders'));
        return $this;
    }

	 /**********************************PICKPACK AUTO TABLE********************************/ 	
    protected function createFlagTable()
    {
    	 $resource = Mage::getSingleton('core/resource');
     
		/**
		 * Retrieve the write connection
		 */
		$writeConnection = $resource->getConnection('core_write');

		/**
		 * Retrieve our table name
		 */
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');
		
		$query = "CREATE TABLE IF NOT EXISTS ".$table." (
				`id` int(11) unsigned NOT NULL auto_increment,
				`orderid` varchar(255) NOT NULL default '',
				`pp_invoice_printed` int(2) NULL,
				`pack_printed` int(2) NULL,
				`separate_printed` int(2) NULL,
				`combined_printed` int(2) NULL,
				`manual_pp_invoice_printed` int(2) NULL,
				`manual_pack_printed` int(2) NULL,
				`manual_separate_printed` int(2) NULL,
				`manual_combined_printed` int(2) NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
 		
// 		$writeConnection->query($query);
    }
    
	protected function getFlagValue($orderid = null,$flagname = null)
	{
		$resource = Mage::getSingleton('core/resource');
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');
		$readConnection = $resource->getConnection('core_read');
   		$query = 'SELECT '.$flagname.' FROM ' . $table . ' WHERE orderid = '
             .(int)$orderid . ' LIMIT 1';
    	$value = $readConnection->fetchOne($query);
  		return $value;
	}
	
	protected function updateFlagValue($orderid = null,$flagname = null,$value = null)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');  		
  		$newSku = 'new-sku';
		$query = "UPDATE {$table} SET {$flagname} = {$value} WHERE orderid = "
				 . (int)$orderid;
		$writeConnection->query($query);
	}
	
	protected function updateFlagValues($orderid = null,$flagname1 = null,$value1 = null,$flagname2 = null,$value2 = null)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');  		
  		$newSku = 'new-sku';
		$query = "UPDATE {$table} SET ({$flagname1} = {$value1},{$flagname2} = {$value2}) WHERE orderid = "
				 . (int)$orderid;
		$writeConnection->query($query);
	}
	
	protected function insertFlagValue($orderid = null,$flagname = null,$value = null)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');  		
  		$newSku = 'new-sku';
		$query = "INSERT INTO {$table} (orderid,{$flagname})
		VALUES ({$orderid}, {$value})";
		$writeConnection->query($query);
	}
	
	protected function insertFlagValues($orderid = null,$flagname1 = null,$value1 = null,$flagname2 = null,$value2 = null)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$table = $resource->getTableName('moogento_pickpack_flagautoaction');  		
  		$newSku = 'new-sku';
		$query = "INSERT INTO {$table} (orderid,{$flagname1},{$flagname2})
		VALUES ({$orderid}, {$value1},{$value2})";
		$writeConnection->query($query);
	}
	
   /**********************************END PICKPACK AUTO TABLE*****************************/ 
    public function indexAction()
    {
        parent::indexAction();
    }

    protected function _getOrderIds($invoice_ids)
    {
        $order_ids = array();
        foreach ($invoice_ids as $invoice_id) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $order_ids[] = $invoice->getOrderId();
        }
        return $order_ids;
    }

    public function mooinvoiceAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        $from_shipment = 'invoice';
        if (!empty($orderIds)) {
            $methodName ='getPdfDefault';// (Mage::getStoreConfig('pickpack_options/wonder_invoice/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'invoice');

            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
			/*
			if(!empty($pdf))
            {
            	$flagColumn = 'manual_pp_invoice_printed';
            	foreach($orderIds as $orderId)
            	{
            		$flag_value = $this->getFlagValue($orderId,$flagColumn);
                        // if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
					if($flag_value === false)
					{
						$insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
					}
					else
					{
						$update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));								
					}
            	}
            }*/
            return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

public function labelzebradetailAction()
    {
        $orderId = array();
        if($this->getRequest()->getParam('order_id'))
            $orderId[0] = $this->getRequest()->getParam('order_id');
        if (!empty($orderId)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra($orderId);
            Mage::dispatchEvent(
                'moo_pp_zebra_pdf_generate_after',
                array('order_ids' => $orderId)
            );
            return $this->_prepareDownloadResponse('zebra_labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    public function packAction()
    {
    	
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));
        $from_shipment = 'invoice';

        if (!empty($orderIds)) {
            $methodName = (Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'pack');

            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            if(!empty($pdf))
            {
            	$flagColumn = 'manual_pack_printed';
            	foreach($orderIds as $orderId)
            	{
            		$flag_value = $this->getFlagValue($orderId,$flagColumn);
                        // if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
					if($flag_value === false)
					{
						$insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
					}
					else
					{
						$update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));								
					}
            	}
            }

            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function mooinvoicepackAction()
    {
        $this->createFlagTable();
        $invoice_arr = array();
        $invoice_arr[] = $this->getRequest()->getParam('invoice_ids');
        $orderIds = $this->_getOrderIds($invoice_arr);     

        $flag = false;
        $from_shipment = 'invoice'.'|'.$this->getRequest()->getParam('invoice_ids');


        if (!empty($orderIds)) {
            if (Mage::getStoreConfig('pickpack_options/general/combined_orders_grouped_by_id_yn') == 1) {
                $pdf = new Zend_Pdf();

                foreach ($orderIds as $order_id) {
                    $order_id_array = array($order_id);

                    $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($order_id_array, $from_shipment, 'invoice');
                    $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($order_id_array, $from_shipment, 'pack');
                    foreach ($pdfB->pages as $page) {
                        $pdfA->pages[] = $page;
                    }
                    foreach ($pdfA->pages as $page) {
                        $pdf->pages[] = $page;
                    }
                }
                /*
                if(!empty($pdf))
				{
					$flagColumn1 = 'manual_pp_invoice_printed';
					$flagColumn2 = 'manual_pack_printed';					
					foreach($orderIds as $orderId)
					{
						$flag_value1 = $this->getFlagValue($orderId,$flagColumn1);
						$flag_value2 = $this->getFlagValue($orderId,$flagColumn2);
							// if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
						
						if(($flag_value1 === false) && 	($flag_value2 === false))
						{
							$insert_flat_values = $this->insertFlagValues($orderId,$flagColumn1,1,$flagColumn2,1);
						}
						else
						{
							if($flag_value1 === false)
							{
								$insert_flat_value2 = $this->insertFlagValue($orderId,$flagColumn1,1);
							}
							else
							{
								$update_flat_value2 = $this->updateFlagValue($orderId,$flagColumn1,((int)$flag_value1 + 1));								
							}
					
							if($flag_value2 === false)
							{
								$insert_flat_value2 = $this->insertFlagValue($orderId,$flagColumn2,1);
							}
							else
							{
								$update_flat_value2 = $this->updateFlagValue($orderId,$flagColumn2,((int)$flag_value2 + 1));								
							}
						}
					}
				}*/

                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');

                foreach ($pdfB->pages as $page) {
                    $pdfA->pages[] = $page;
                }
                /*
                if(!empty($pdfA))
				{
					$flagColumn1 = 'manual_pp_invoice_printed';
					$flagColumn2 = 'manual_pack_printed';					
					foreach($orderIds as $orderId)
					{
						$flag_value1 = $this->getFlagValue($orderId,$flagColumn1);
						$flag_value2 = $this->getFlagValue($orderId,$flagColumn2);
							// if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
						
						if(($flag_value1 === false) && 	($flag_value2 === false))
						{
							$insert_flat_values = $this->insertFlagValues($orderId,$flagColumn1,1,$flagColumn2,1);
						}
						else
						{
							if($flag_value1 === false)
							{
								$insert_flat_value2 = $this->insertFlagValue($orderId,$flagColumn1,1);
							}
							else
							{
								$update_flat_value2 = $this->updateFlagValue($orderId,$flagColumn1,((int)$flag_value1 + 1));								
							}
					
							if($flag_value2 === false)
							{
								$insert_flat_value2 = $this->insertFlagValue($orderId,$flagColumn2,1);
							}
							else
							{
								$update_flat_value2 = $this->updateFlagValue($orderId,$flagColumn2,((int)$flag_value2 + 1));								
							}
						}
					}
				}*/

                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
            }
        }

        $this->_redirect('*/*/');
    }

    public function pickAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated($orderIds);
            if(!empty($pdf))
            {
            	$flagColumn = 'manual_separate_printed';
            	foreach($orderIds as $orderId)
            	{
            		$flag_value = $this->getFlagValue($orderId,$flagColumn);
                        // if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
					if($flag_value === false)
					{
						$insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
					}
					else
					{
						$update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));								
					}
            	}
            }
            return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    public function enpickAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds, 'order_combined');
            if(!empty($pdf))
            {
            	$flagColumn = 'manual_combined_printed';
            	foreach($orderIds as $orderId)
            	{
            		$flag_value = $this->getFlagValue($orderId,$flagColumn);
                        // if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
					if($flag_value === false)
					{
						$insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
					}
					else
					{
						$update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));								
					}
            	}
            }
            return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function prodpickAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    public function stockAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));
        $csv_or_pdf = Mage::getStoreConfig('pickpack_options/stock/csv_or_pdf');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
            if ($csv_or_pdf == 'pdf') {
                return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
				$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
				if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
				
                return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv', $pdf);
            }
        }
        $this->_redirect('*/*/');
    }

    public function labelAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
            return $this->_prepareDownloadResponse('address-labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function orderscsvAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, 'order');
            $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
			
            return $this->_prepareDownloadResponse($fileName, $pdf);
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated2($orderIds);
            $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
			
            return $this->_prepareDownloadResponse($fileName, $pdf);
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvcombinedAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'picklist');
            $fileName = 'pick-list-combined-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
			
            return $this->_prepareDownloadResponse($fileName, $pdf);
        }
        $this->_redirect('*/*/');
    }

    public function manifestcsvcombinedAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('invoice_ids'));

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'manifest');

            if (Mage::getStoreConfig('pickpack_options/csvmanifestcombined/is_excel_yn') == 1) {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.xml';
                return $this->_prepareDownloadResponse($fileName, $pdf);
            } else {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
				$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
				if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
				
                return $this->_prepareDownloadResponse($fileName, $pdf);
            }
        }
        $this->_redirect('*/*/');
    }
}
