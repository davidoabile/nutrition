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
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate,AdjustWare_Notification% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Notification')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrder_Aittmp extends AdjustWare_Notification_Model_Rewrite_Sales_Order {} 
 }elseif(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrder_Aittmp extends AdjustWare_Deliverydate_Model_Rewrite_FrontSalesOrder {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrder_Aittmp extends Mage_Sales_Model_Order {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrder extends Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrder_Aittmp
{
    protected $_cfmCustomFields;

    /**
     * Get CFM data for this order
     * 
     * @param bool $forceReload Forces tranport object to be reloaded. Default: false.
     * 
     * @return Aitoc_Aitcheckoutfields_Model_Transport
     */    
    public function getCustomFields($forceReload = false)
    {
        if(is_null($this->_cfmCustomFields) || $forceReload)
        {
            $this->_cfmCustomFields = Mage::getModel('aitcheckoutfields/transport')->loadByOrder($this);
        }
        return $this->_cfmCustomFields;
    }
}