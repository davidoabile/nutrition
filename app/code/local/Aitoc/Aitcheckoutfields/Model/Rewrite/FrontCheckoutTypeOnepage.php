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
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate,AdjustWare_Giftreg,Aitoc_Aitcheckout% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitcheckout')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage_Aittmp extends Aitoc_Aitcheckout_Model_Rewrite_Checkout_Type_Onepage {} 
 }elseif(Mage::helper('core')->isModuleEnabled('AdjustWare_Giftreg')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage_Aittmp extends AdjustWare_Giftreg_Model_Rewrite_FrontCheckoutTypeOnepage {} 
 }elseif(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage_Aittmp extends AdjustWare_Deliverydate_Model_Rewrite_FrontCheckoutTypeOnepage {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage_Aittmp extends Mage_Checkout_Model_Type_Onepage {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage extends Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage_Aittmp
{
    protected function _saveCustomData($data)
    {
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }
        return $this;
    }

    // overwrite parent
    public function saveBilling($data, $customerAddressId)
    {
        $this->_saveCustomData($data);
        return parent::saveBilling($data, $customerAddressId);
    }

    // overwrite parent
    public function saveShipping($data, $customerAddressId)
    {
        $canSave = true;
        if ($this->getAitcheckoutfieldsHelper()->checkIfAitocAitcheckoutIsActive())
        {
            $billing = Mage::app()->getRequest()->getPost('billing', array());
            $canSave = empty($billing['use_for_shipping']);
            
        }
        $this->_saveCustomData($data);
        return ($canSave ? parent::saveShipping($data, $customerAddressId) : $this);
    }

    // overwrite parent
    public function saveShippingMethod($shippingMethod)
    {
        $oReq = Mage::app()->getFrontController()->getRequest();
        
        $data = $oReq->getPost('shippmethod');
        $this->_saveCustomData($data);
        
    /************** AITOC DELIVERY DATE COMPATIBILITY MODE: START ********************/
        
        $val = Mage::getConfig()->getNode('modules/AdjustWare_Deliverydate/active');
        if ((string)$val == 'true')
        {
            $errors = Mage::getModel('adjdeliverydate/step')->process('shippingMethod');
            if ($errors)
                return $errors;
        }
    
    /************** AITOC DELIVERY DATE COMPATIBILITY MODE: FINISH ********************/

        return parent::saveShippingMethod($shippingMethod);
    }
    
    // overwrite parent
    public function savePayment($data)
    {
        $return = parent::savePayment($data);
        $this->_saveCustomData($data);

        return $return;
    }

    // overwrite parent
    public function saveOrder()
    {
        // set review attributes data
        
        $oReq = Mage::app()->getFrontController()->getRequest();
        foreach ($oReq->getParams() as $_param)
        {
            if(is_array($_param) && Mage::helper('aitcheckoutfields')->checkIfAitocAitcheckoutIsActive())
            {
                Mage::helper('aitcheckout/aitcheckoutfields')->saveCustomData($_param); 
            }
        }
        $data = $oReq->getPost('customreview');
        $this->_saveCustomData($data);
       
        $oResult = parent::saveOrder();

        // save attribute data to DB
        
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        
        $iOrderId = $this->getCheckout()->getLastOrderId();
        
        if ($iOrderId)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

            $oAttribute->saveCustomOrderData($iOrderId, 'onepage');
            $oAttribute->clearCheckoutSession('onepage');
        }
        
        Mage::dispatchEvent('aitcfm_order_save_after', array('order' => $order, 'checkoutfields' => $order->getCustomFields()));
        
        return $oResult;
    }
    
    // overwrite parent
    protected function _involveNewCustomer()
    {
        parent::_involveNewCustomer();
        
        $customerId = $this->getQuote()->getCustomer()->getId();
        Mage::getModel('aitcheckoutfields/aitcheckoutfields')->saveCustomerData($customerId, true);
    }
    
    /**
     *
     * @return Aitoc_Aitcheckoutfields_Helper_Data
     */
    public function getAitcheckoutfieldsHelper()
    {
        return Mage::helper('aitcheckoutfields');
    }
}