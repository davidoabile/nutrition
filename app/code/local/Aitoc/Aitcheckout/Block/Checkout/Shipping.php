<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Block_Checkout_Shipping extends Aitoc_Aitcheckout_Block_Checkout_Step
{
    protected $_stepType = 'Shipping';
    
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();    
    }
    
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }
    
    public function customerHasAddresses()
    {
        if (Mage::helper('aitcheckout/adjgiftregistry')->getGiftAddressId()){
            return true;
        }
        return parent::customerHasAddresses();
    }
}