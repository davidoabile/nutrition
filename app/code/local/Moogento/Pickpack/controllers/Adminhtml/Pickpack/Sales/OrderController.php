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
* File        OrderController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 


//require_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php");

class Moogento_Pickpack_Adminhtml_Pickpack_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/order/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/order/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/order/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/order/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'sales_email/order/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE        = 'sales_email/order_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE  = 'sales_email/order_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY        = 'sales_email/order_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO         = 'sales_email/order_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD     = 'sales_email/order_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED         = 'sales_email/order_comment/enabled';
    /**
     * Class Constructor
     * call the parent Constructor
     */
    
    private $include_orderid_yn;
    private $pdf_name;
    private $date_format;

    protected function _isAllowed()
    {
        return true;
    }
        
    public function __constuct()
    {        
        parent::__construct();
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
 
        
//      $writeConnection->query($query);
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
        $query = "INSERT INTO {$table} ($orderid,{$flagname})
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
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        // ->_addBreadcrumb($this->__('Orders'), $this->__('Trash Orders'));
        return $this;
    }

    public function indexAction()
    {
        parent::indexAction();
    }

    //Order Grid Pdf Invoice
    public function mooinvoiceAction()
    {
        $this->createFlagTable();
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
        if(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation'))
        {
			if(is_null($orderIds))
			{
				$orderIds = array();
				$orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
				$collection = mage::getModel('Orderpreparation/ordertopreparepending')
						->getCollection()
						->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
				foreach ($collection as $item)
					$orderIds[] = $item->getopp_order_id();
			}
		}
        $from_shipment = 'order';
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if (!empty($orderIds)) {
            if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $methodName = 'getPdfDefault'; // (Mage::getStoreConfig('pickpack_options/wonder_invoice/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'invoice');
            //is shipeasy printing and manual printing is yes.
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
            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/invoice_pdf_name');
                     
            if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
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
    
    //Order Grid Pdf Packing Sheet
    public function packAction()
    {   
        $this->createFlagTable();  
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
         if(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation'))
        {
			if(is_null($orderIds))
			{
				$orderIds = array();
				$orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
				$collection = mage::getModel('Orderpreparation/ordertopreparepending')
						->getCollection()
						->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
				foreach ($collection as $item)
					$orderIds[] = $item->getopp_order_id();
			}
		}
        $from_shipment = 'order';
        ///once print only
        $text_orderId = '';
        //$include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if(Mage::helper('pickpack')->isInstalled("Moogento_ShipEasy")){
            $only_print_once = Mage::getStoreConfig("pickpack_options/wonder/only_print_once");
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
            if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $methodName = 'getPdfDefault'; //(Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'pack');

            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            
            //Default store config
            if(Mage::getStoreConfig("pickpack_options/wonder/additional_action_change_order_status_yn") == 1)
            {
                Mage::dispatchEvent(
                    'moo_pp_pack_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
            
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
            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/packsheet_pdf_name');
            
            if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
            //return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    //Order Detail Pdf Packing Sheet
    public function mooordershipmentAction()
    {
        $this->createFlagTable();
        $this->_assignOption();
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        $from_shipment = 'order';
        $inovice_id = '';
        $shipment_ids = '';
        /*get param invoice id if have**/
        if($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');
            /*END*/
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if (!empty($orderIds)) {
            if(($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            $methodName = 'getPdfDefault'; // (Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1) ? 'getPdf2' : 'getPdfDefault';
            //$pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'pack');
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, 'pack', $inovice_id, $shipment_ids);
            
            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => $orderIds)
            );
            
             //Default store config
            if(Mage::getStoreConfig("pickpack_options/wonder/additional_action_change_order_status_yn") == 1)
            {
                Mage::dispatchEvent(
                    'moo_pp_pack_pdf_manual_generate_after',
                    array('order_ids' => $orderIds)
                );
            }
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
            $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/packsheet_pdf_name');
            if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
            //return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }


    //Invoice and Packing sheet
    public function mooinvoicepackAction()
    {
        $this->createFlagTable();
        $this->_assignOption();           
        $orderIds = $this->getRequest()->getPost('order_ids');
         if(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation'))
        {
			if(is_null($orderIds))
			{
				$orderIds = array();
				$orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
				$collection = mage::getModel('Orderpreparation/ordertopreparepending')
						->getCollection()
						->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
				foreach ($collection as $item)
					$orderIds[] = $item->getopp_order_id();
			}
		}
        $flag = false;
        $from_shipment = 'order';
        $text_orderId = '';
        //$include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/pack_invoice_pdf_name');        
        if (!empty($orderIds)) {
            if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
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
                            
                        if(($flag_value1 === false) &&  ($flag_value2 === false))
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
                }
                */
                if($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                elseif($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId .'.pdf', $pdf->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');
                //return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            } 
            else {
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
                        if(($flag_value1 === false) &&  ($flag_value2 === false))
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
                }
                */
                if($this->include_orderid_yn == 'yesdate')
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
                elseif($this->include_orderid_yn == 'yes')
                    return $this->_prepareDownloadResponse($this->pdf_name . $text_orderId .'.pdf', $pdfA->render(), 'application/pdf');
                else
                    return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdfA->render(), 'application/pdf');
                //return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            }
        }

        $this->_redirect('*/*/');
    }

    //Order Detail Pdf Invoice and Packing sheet
    public function mooorderinvoicepackAction()
    {
                $this->createFlagTable();
        $orderIds = array();
        $orderIds[0] = $this->getRequest()->getParam('order_id');
        /*get param invoice id if have*
        */
        $inovice_id = '';
        $shipment_ids = '';
        if($this->getRequest()->getParam('invoice_id'))
            $inovice_id = $this->getRequest()->getParam('invoice_id');
        /*get param shipment id if have**/
        if($this->getRequest()->getParam('shipment_ids'))
            $shipment_ids = $this->getRequest()->getParam('shipment_ids');
        
        $from_shipment = 'order';
        $storeId = Mage::app()->getStore()->getId();
        $option_group = 'general';
        $invoice_or_pack_first = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pdf_invoice_packing_sort', $storeId);
        $text_orderId = '';
        $include_orderid_yn = Mage::getStoreConfig('pickpack_options/general/include_orderid_filename');
        if (!empty($orderIds)) {
            $methodName = 'getPdfDefault';
            if($invoice_or_pack_first == 'invoice')
                $invoice_or_pack_second = 'pack';
            else
                $invoice_or_pack_second = 'invoice';
            $pdfA = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, $invoice_or_pack_first, $inovice_id, $shipment_ids);
            $pdfB = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->$methodName($orderIds, $from_shipment, $invoice_or_pack_second, $inovice_id, $shipment_ids);
            foreach ($pdfB->pages as $page) {
                    $pdfA->pages[] = $page;
           }
           if(count($orderIds) == 1 && ($include_orderid_yn == 'yes' || $include_orderid_yn == 'yesdate'))
                $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();
            //return $this->_prepareDownloadResponse('invoice_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            if($include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            elseif($include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . $text_orderId . '.pdf', $pdfA->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse('invoice+packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdfA->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    //Separated Pick
    public function pickAction()
    {
        $this->createFlagTable();
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');
		 if(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation'))
        {
			if(is_null($orderIds))
			{
				$orderIds = array();
				$orderPreparationIds = $this->getRequest()->getPost('full_stock_orders_order_ids');
				$collection = mage::getModel('Orderpreparation/ordertopreparepending')
						->getCollection()
						->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
				foreach ($collection as $item)
					$orderIds[] = $item->getopp_order_id();
			}
		}
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
                        if($orderId)
                            $insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
                    }
                    else
                    {
                        $update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));                               
                    }
                }
            }
            */
             if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();            
           
           $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/order_separated_picklist_name');            
           if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');                        
            
            //return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    //Separated Pick 2
    public function pick2Action()
    {
        $this->createFlagTable();
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated2')->getPickSeparated($orderIds);
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
                        if($orderId)
                            $insert_flat_value = $this->insertFlagValue($orderId,$flagColumn,1);
                    }
                    else
                    {
                        $update_flat_value = $this->updateFlagValue($orderId,$flagColumn,((int)$flag_value + 1));
                    }
                }
            }
            */
            return $this->_prepareDownloadResponse('orders-summary_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Combined Pick
    public function enpickAction()
    {
        $this->createFlagTable();
        $this->_assignOption();
        $orderIds = $this->getRequest()->getPost('order_ids');

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
           if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();            
           
           $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/order_combined_picklist_name');            
           if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');            
            
            // return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Trolleybox Pick
    public function prodpickAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolley')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    //Address Labels
    public function labelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_label')->getLabel($orderIds);
              if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();            
           
           $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/address_label_sheet_name');            
           if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');            
            // return $this->_prepareDownloadResponse('address-labels_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    //Gift Messages
    public function giftmessageAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_giftmessage')->getGiftmessage($orderIds);
            if(is_object($pdf))
            {
				Mage::dispatchEvent(
					'moo_pp_gift_message_pdf_generate_after',
					array('order_ids' => $orderIds)
				);
            	return $this->_prepareDownloadResponse('gift_message' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/sales_order/');
//         $this->_redirect('*/*/');
    }
    //Gift Cefiticate
    public function giftcetificateAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_giftcetificate')->getGiftcetificate($orderIds);
            if(is_object($pdf))
            {
                Mage::dispatchEvent(
                    'moo_pp_gift_message_pdf_generate_after',
                    array('order_ids' => $orderIds)
                );
                return $this->_prepareDownloadResponse('gift_message' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/sales_order/');
    }
    //Address Labels
    public function labelzebraAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
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
    //Out of stock pick list
    public function stockAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        $csv_or_pdf = Mage::getStoreConfig('pickpack_options/stock/csv_or_pdf');
        
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_stock')->getPickStock($orderIds);
              if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();            
            
            if ($csv_or_pdf == 'pdf') {           
                    $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/out_of_stock_list_name');            
                    if($this->include_orderid_yn == 'yesdate')
                         return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                     elseif($this->include_orderid_yn == 'yes')
                         return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
                     else
                         return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');            
                
                //return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            } else {
                    $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/out_of_stock_list_name');            
                    if($this->include_orderid_yn == 'yesdate')
                         return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.csv', $pdf);
                     elseif($this->include_orderid_yn == 'yes')
                         return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.csv', $pdf);
                     else
                         return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.csv', $pdf);                            
               
                  // return $this->_prepareDownloadResponse('out-of-stock-list_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv', $pdf);
            }
        }
        $this->_redirect('*/*/');
    }

    //CSVOrders
    public function orderscsvAction()
    {
        $non_standard_characters = Mage::getStoreConfig('pickpack_options/general/non_standard_characters');
        $csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
		/*
			In order to convert properly UTF8 data with EURO sign you must use:
			iconv("UTF-8", "CP1252", $data)
		*/
		
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvorders')->getCsvOrders($orderIds, false);
            $fileName = 'orders-csv_' . Mage::getSingleton('core/date')->date('Y-m-d') . '.csv';
         
		    if($non_standard_characters == 'simplified_chinese' || $non_standard_characters == 'traditional_chinese') {
				// @TODO check if need non-utf8 $csv_encoding option here
                header ( 'HTTP/1.1 200 OK' );
                header ( 'Date: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Last-Modified: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Content-Type: application/vnd.ms-excel') ;
                header ( 'Content-Disposition: attachment;filename='.$fileName);
                print chr(255) . chr(254) . mb_convert_encoding($pdf, 'UTF-16LE', 'UTF-8');
                exit;
            }
            elseif($non_standard_characters == 'hebrew') {
				// @TODO check if need non-utf8 $csv_encoding option here
	            header("Pragma: public");
	            header("Expires: 0");
	            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	            header("Cache-Control: private",false);
	            // header("Content-Type: application/octet-stream");
	            header("Content-Type: text/html; charset=UTF-16BE");
	            header ( 'Content-Disposition: attachment;filename='.$fileName);
	            header("Content-Transfer-Encoding: binary");
	            print($pdf); exit;

	        } 
	        else {
	           if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
	           return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
	        }
        }
        $this->_redirect('*/*/');
    }

    //CSV Separated     
    public function pickcsvAction()
    {
        $non_standard_characters = Mage::getStoreConfig('pickpack_options/general/non_standard_characters');
        $csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvseparated')->getCsvPickSeparated($orderIds);
        
            $fileName = 'pick-list-separated-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
             if($non_standard_characters == 'simplified_chinese' || $non_standard_characters == 'traditional_chinese')
            {
                header ( 'HTTP/1.1 200 OK' );
                header ( 'Date: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Last-Modified: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Content-Type: application/vnd.ms-excel') ;
                header ( 'Content-Disposition: attachment;filename='.$fileName);
                print chr(255) . chr(254) . mb_convert_encoding($pdf, 'UTF-16LE', 'UTF-8');
                exit;
            }
            elseif($non_standard_characters == 'hebrew')
            {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                // header("Content-Type: application/octet-stream");
                header("Content-Type: text/html; charset=UTF-16BE");
                header ( 'Content-Disposition: attachment;filename='.$fileName);
                header("Content-Transfer-Encoding: binary");
                print($pdf); exit;

            } 
            else
            {
                if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
                return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
            }
        }
        $this->_redirect('*/*/');
    }

    //CSV Combined
    public function pickcsvcombinedAction()
    {
        $non_standard_characters = Mage::getStoreConfig('pickpack_options/general/non_standard_characters');
        $csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'picklist');
            $fileName = 'pick-list-combined-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
        
             if($non_standard_characters == 'simplified_chinese' || $non_standard_characters == 'traditional_chinese')
            {
                header ( 'HTTP/1.1 200 OK' );
                header ( 'Date: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Last-Modified: ' . date ( 'D M j G:i:s T Y' ) );
                header ( 'Content-Type: application/vnd.ms-excel') ;
                header ( 'Content-Disposition: attachment;filename='.$fileName);
                print chr(255) . chr(254) . mb_convert_encoding($pdf, 'UTF-16LE', 'UTF-8');
                exit;
            }
            elseif($non_standard_characters == 'hebrew')
            {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                // header("Content-Type: application/octet-stream");
                header("Content-Type: text/html; charset=UTF-16BE");
                header ( 'Content-Disposition: attachment;filename='.$fileName);
                header("Content-Transfer-Encoding: binary");
                print($pdf); exit;

            } 
            else
            {
                if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
                return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
            }
        }
        $this->_redirect('*/*/');
    }

    //CSV Manifest
    public function manifestcsvcombinedAction()
    {
        $non_standard_characters = Mage::getStoreConfig('pickpack_options/general/non_standard_characters');
        $csv_encoding = Mage::getStoreConfig('pickpack_options/general_csv/csv_encoding');
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvcombined')->getCsvPickCombined($orderIds, false, 'manifest');
           
		    if($csv_encoding == 'ansi') $pdf =  utf8_decode($pdf);
           
		    if (Mage::getStoreConfig('pickpack_options/csvmanifestcombined/is_excel_yn') == 1) {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.xml';
                //$grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');

                //return $this->_prepareDownloadResponse($fileName, $pdf);
                return $this->_prepareDownloadResponse($fileName, $pdf);

            } else {
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
//                 return $this->_prepareDownloadResponse($fileName, $pdf);
                return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
            }
        }
        $this->_redirect('*/*/');
    }

     /**
     * Notify user
     */
    public function resendmailAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $send_result = $this->sendNewOrderEmail($order);
                if($send_result)
                {       
                    $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                        ->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
                    if ($historyItem) {
                        $historyItem->setIsCustomerNotified(1);
                        $historyItem->save();
                    }
                    $this->_getSession()->addSuccess($this->__('Sent mail sucessfully'));
                }
                else
                    $this->_getSession()->addSuccess($this->__('Failed to resend the order email'));

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {

                $this->_getSession()->addError($this->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function sendNewOrderEmail($order)
    {
        $storeId = $order->getStore()->getId();
            
        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return false;
            return $order;
        }
        
        $emailSentAttributeValue = $order->hasEmailSent()
            ? $order->getEmailSent()
            : Mage::getModel('sales/order')->load($order->getId())->getData('email_sent');
        $order->setEmailSent((bool)$emailSentAttributeValue);
        // Get the destination email addresses to send copies to
        // $copyTo = $order->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        // $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        // if ($copyTo && $copyMethod == 'bcc') {
        //     // Add bcc to customer email
        //     foreach ($copyTo as $email) {
        //         $emailInfo->addBcc($email);
        //     }
        // }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        // if ($copyTo && $copyMethod == 'copy') {
        //     foreach ($copyTo as $email) {
        //         $emailInfo = Mage::getModel('core/email_info');
        //         $emailInfo->addTo($email);
        //         $mailer->addEmailInfo($emailInfo);
        //     }
        // }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $order->setEmailSent(true);
        // $order->_getResource()->saveAttribute($order, 'email_sent');
        return true;
    }

    /*
        public function pickxlcombinedAction(){
            $orderIds = $this->getRequest()->getPost('order_ids');

            if (!empty($orderIds))
            {
                    $pdf = Mage::getModel('sales/order_pdf_invoices')->getXlPickCombined($orderIds);
                $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
                //    $grid       = Mage::getModel('sales/order_pdf_invoices')->getXlPickCombined($orderIds);

                    $fileName = 'combined-shipping-manifest_'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.xml';
                    return $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
            }
            $this->_redirect('*');
        }
    */
    
    public function productSeparatedAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $this->_assignOption();
        if (!empty($orderIds)) {
            $product_filter = array();  
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_productseparated')->getPdf($orderIds,'order',$product_filter,'manual');
            if(is_object($pdf)){

              if(count($orderIds) == 1 && ($this->include_orderid_yn == 'yes' || $this->include_orderid_yn == 'yesdate'))
                    $text_orderId = Mage::getModel("sales/order")->load($orderIds[0])->getRealOrderId();            
           
           $this->pdf_name = Mage::getStoreConfig('pickpack_options/file_name/product_separated_picklist_name');            
           
           if($this->include_orderid_yn == 'yesdate')
                return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '_' . $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            elseif($this->include_orderid_yn == 'yes')
                return $this->_prepareDownloadResponse($this->pdf_name. $text_orderId . '.pdf', $pdf->render(), 'application/pdf');
            else
               return $this->_prepareDownloadResponse($this->pdf_name . Mage::getSingleton('core/date')->date($this->date_format) . '.pdf', $pdf->render(), 'application/pdf');                                            
	          //  return $this->_prepareDownloadResponse('product_separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function cn22Action()
    {
    	if(!Mage::helper('pickpack')->isInstalled('Moogento_Cn22'))
    	{
    		$install_message = '</b>autoCN22 (Post Office customs declaration).</b> To enable this feature, please install <b><a href="https://www.moogento.com/magento-auto-cn22-customs-labels">Moogento autoCN22</a></b>';
    		Mage::getSingleton('core/session')->addNotice($install_message);
    		$this->_redirect('*/sales_order/');
    	}
		
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $product_filter = array();  
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_cn22')->getPdf($orderIds,'order',$product_filter,'manual');
            if(is_object($pdf))
				return $this->_prepareDownloadResponse('cn22_label_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    
    public function trolleyboxAction()
    {
        if(!Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox'))
        {
            $install_message = '</b>Trolleybox Pick List Pdf.</b> To enable this feature, please install <b><a href="https://www.moogento.com/">Moogento Trolleybox</a></b>';
            Mage::getSingleton('core/session')->addNotice($install_message);
            $this->_redirect('*/sales_order/');
        }
        
       $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            //TODO packup
            // $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_trolleybox')->getPickCombined($orderIds, 'trolleybox');
            $pdf = Mage::getModel('trolleybox/pdf')->getPickCombined($orderIds, 'trolleybox');
            return $this->_prepareDownloadResponse('pick-list-trolleybox_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    private function _assignOption()
    {
        $this->include_orderid_yn = Mage::getStoreConfig('pickpack_options/file_name/include_orderid_filename');
        $this->date_format = Mage::getStoreConfig('pickpack_options/file_name/date_format');        
    }
}
