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
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCustomerCustomer extends Mage_Customer_Model_Customer
{
    protected $_cfmCustomFields;

    protected function _beforeSave()
    {
        $oReq = Mage::app()->getFrontController()->getRequest();
        
        $data = $oReq->getPost('aitreg');
        
        if($data && !Mage::registry('aitoc_customer_saved') && !Mage::registry('aitoc_customer_to_save'))
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'register');
            }
            Mage::register('aitoc_customer_to_save', true);
        }
         
        return parent::_beforeSave();
    }
    protected function _afterSave()
    {
        $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        if(Mage::registry('aitoc_customer_to_save') && !Mage::registry('aitoc_customer_saved'))
        {
            $customerId = $this->getId();
            $oAttribute->saveCustomerData($customerId);
            $oAttribute->clearCheckoutSession('register');
            Mage::unregister('aitoc_customer_to_save');
            Mage::register('aitoc_customer_saved', true);
            
            Mage::dispatchEvent('aitcfm_customer_save_after', array('customer' => $this, 'checkoutfields' => $this->getCustomFields()));
        }
        $oAttribute->clearCheckoutSession('register');
        return parent::_afterSave();       
    }
    
    /**
     * Get CFM data for this customer
     * 
     * @param bool $forceReload Forces tranport object to be reloaded. Default: false.
     * 
     * @return Aitoc_Aitcheckoutfields_Model_Transport
     */    
    public function getCustomFields($forceReload = false)
    {
        if(is_null($this->_cfmCustomFields) || $forceReload)
        {
            $this->_cfmCustomFields = Mage::getModel('aitcheckoutfields/transport')->loadByCustomer($this);
        }
        return $this->_cfmCustomFields;
    }
    
    
    /**
     * Clean password's validation data (password, new_password, password_confirmation)
     *
     * @return Mage_Admin_Model_User
     */
    public function cleanPasswordsValidationData()
    {
        $this->setData('current_password', null);
        $this->setData('new_password', null);
        $this->setData('password_confirmation', null);
        $this->setData('password', null);
        $this->setData('confirmation', null);
        return $this;
    }
    
}