<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminSalesOrderViewInfo  extends Mage_Adminhtml_Block_Sales_Order_View_Info
{     
    public function getOrderCustomData()
    {
        $isInvoice = $this->getIsInvoice();
        $iStoreId = $this->getOrder()->getStoreId();

        $oFront = Mage::app()->getFrontController();
        $params = $oFront->getRequest()->getParams();
        if(!empty($params['order_id']))
        {
            $iOrderId =  $params['order_id'];       
        }
        elseif(!empty($params['invoice_id']))
        {
            $iOrderId = Mage::getModel('sales/order_invoice')->load($params['invoice_id'])->getOrder()->getId();  
              
        }
        else
        {
            return false;
        }
        $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

        if(!empty($isInvoice))
        {
            $aCustomAtrrList = $oAitcheckoutfields->getInvoiceCustomData($iOrderId, $iStoreId);      
        }
        else
        {
            $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($iOrderId, $iStoreId, true, true);    
        }
        
        !$aCustomAtrrList ? $aCustomAtrrList = array() : false;
        
        return $aCustomAtrrList;
    }
    
    // new function
    public function getEditUrl()
    {
        $oFront = Mage::app()->getFrontController();
        
        $iOrderId = $oFront->getRequest()->getParam('order_id');
        
        $order = Mage::getModel('sales/order')->load($iOrderId);
        $orderStore = $order->getStore();
        $orderStoreId = $orderStore->getId();
        $orderWebsiteId = $orderStore->getWebsite()->getId();
        
        /* {#AITOC_COMMENT_END#}
        $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckoutfields')->getLicense()->getPerformer();
        $ruler = $performer->getRuler();
        if (!($ruler->checkRule('store',$orderStoreId,'store') || $ruler->checkRule('store',$orderWebsiteId,'website')))
        {
            return false;
        }
        {#AITOC_COMMENT_START#} */
        
        return $this->getUrl('aitcheckoutfields/index/orderedit', array('order_id' => $iOrderId));
    }
}