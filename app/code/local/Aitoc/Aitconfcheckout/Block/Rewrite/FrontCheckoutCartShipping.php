<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CC Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitconfcheckout
 * @version      1.0.9 - 2.1.23
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitconfcheckout_Block_Rewrite_FrontCheckoutCartShipping extends Mage_Checkout_Block_Cart_Shipping
{
    /**
     * Get address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return Mage::helper('aitconfcheckout/onepage')->getAddress(parent::getAddress());
    }    
}