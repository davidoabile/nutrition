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
* File        ShipmentController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

//require_once 'app/code/core/Mage/Adminhtml/controllers/Sales/ShipmentController.php';
$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/ShipmentController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_ShipmentController extends Mage_Adminhtml_Sales_ShipmentController
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
            ->_setActiveMenu('sales/shipment')
            ->_addBreadcrumb($this->__('Shipment'), $this->__('Shipment')); //
        // ->_addBreadcrumb($this->__('Shipment'), $this->__('Trash Shipment'));
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

    public function mooinvoiceAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->getRequest()->getPost('shipment_ids');
        $flag = false;
        $from_shipment = 'shipment';
        if (!empty($orderIds)) {
            if (Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
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
            } else {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
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
				}
				*/
                return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _getOrderIds($shipment_ids)
    {
        $order_ids = array();
        foreach ($shipment_ids as $shipment_id) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
            $order_ids[] = $shipment->getOrderId();
        }
        return $order_ids;
    }


    public function packAction()
    {	
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));		
        $flag = false;
//         $from_shipment = implode(',',$this->getRequest()->getPost('shipment_ids'));//'shipment';
		$from_shipment = 'shipment'.'|'.implode(',',$this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            if (Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');
                /*
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
				*/
                return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');
                /*
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
				*/
                return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }
    
     public function labelzebraAction()
    {	
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));		
        $only_print_once = Mage::getStoreConfig("pickpack_options/label_zebra/only_print_once");
        if(Mage::helper('pickpack')->isInstalled("Moogento_ShipEasy")){
            if($only_print_once == 1){
                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                foreach ($orderIds as $key => $orderId) {
                    $print_yn = $resource->getValueColumnSe($orderId, "szy_custom_attribute3");
                    if($print_yn != ''){
                        if(($key = array_search($orderId, $orderIds)) !== false) {
                            unset($orderIds[$key]);
                        }
                    }
                }
                
            }
        }
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra($orderIds);
            Mage::dispatchEvent(
                'moo_pp_zebra_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            return $this->_prepareDownloadResponse('zebra_labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Order Detail Pdf Invoice
    public function mooorderinvoiceAction()
    {
                $this->createFlagTable();
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        /*get param invoice id if have*
        */
        $inovice_id = '';
        $shipment_ids = '';
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');
        
        $from_shipment = 'order';
        if (!empty($orderIds)) {
            if(($include_orderid_yn == 'yes' || $include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $methodName = 'getPdfDefault'; // (Mage::getStoreConfig('pickpack_options/wonder_invoice/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            //$pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'invoice');
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'invoice', $inovice_id, $shipment_ids);
            
            Mage::dispatchEvent(
                'moo_pp_invoice_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            
            //Default store config
            if(Mage::getStoreConfig("pickpack_options/wonder_invoice/additional_action_change_order_status_yn") == 1)
            {
                Mage::dispatchEvent(
                    'moo_pp_invoice_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
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
            }
            */
            //return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            if($include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse('invoice_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
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
    public function moopackAction()
    {	

    	$shipment_arr = array();
    	$shipment_arr[] = $this->getRequest()->getParam('shipment_ids');
        $orderIds = $this->_getOrderIds($shipment_arr);		
        $flag = false;
		$from_shipment = 'shipment'.'|'.implode(',',$shipment_arr);
		$inovice_id = '';
		$shipment_ids = '';
		/*get param invoice id if have**/
		if($this->getRequest()->getParam('invoice_id'))
			$inovice_id = $this->getRequest()->getParam('invoice_id');
		/*get param shipment id if have**/
		if($this->getRequest()->getParam('shipment_ids'))
			$shipment_ids = $this->getRequest()->getParam('shipment_ids');
			/*END*/
			
        if (!empty($orderIds)) {
            if (Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack', $inovice_id, $shipment_ids);
                /*
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
				*/
                return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack', $inovice_id, $shipment_ids);
                /*
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
				*/
                return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }


    public function mooinvoicepackAction()
    {
    	$this->createFlagTable();
       	$shipment_arr = array();
    	$shipment_arr[] = $this->getRequest()->getParam('shipment_ids');
        $orderIds = $this->_getOrderIds($shipment_arr);		

        $flag = false;
        $from_shipment = 'shipment'.'|'.$this->getRequest()->getParam('shipment_ids');

        if (!empty($orderIds)) {
            $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
            $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'pack');

            foreach ($pdfB->pages as $page) {
                $pdfA->pages[] = $page;
            }

            return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
        }

        $this->_redirect('*/*/');
    }

    public function pickAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated($orderIds);
            /*
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
				*/
            return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function enpickAction()
    {
    	$this->createFlagTable();
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds, 'order_combined');
            /*
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
			*/
            return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function prodpickAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function stockAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
			
            return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function labelAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
            return $this->_prepareDownloadResponse('address-labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function orderscsvAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
		
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, false);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
            $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
			
            return $this->_prepareDownloadResponse($fileName, $pdf);
        }
        $this->_redirect('*/*/');
    }

    public function pickcsvAction()
    {
        $orderIds = $this->_getOrderIds($this->getRequest()->getPost('shipment_ids'));
		
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated2($orderIds);
			$csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
			if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
            $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
            return $this->_prepareDownloadResponse($fileName, $pdf);
        }
        $this->_redirect('*/*/');
    }
}
