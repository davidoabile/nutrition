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
class Aitoc_Aitcheckout_Helper_Onlyif_Data extends Aitoc_Aitcheckout_Helper_Abstract
{
    public function saveBilling($currentStep, $customerAddressId)
    {
        if($currentStep == 'payment' && Mage::helper('aitcheckout/aitconfcheckout')->isEnabled() && Mage::helper('customer')->isLoggedIn())
        {
            if (!Mage::getSingleton('checkout/type_onepage')->getQuote()->getBillingAddress()->getData('customer_address_id'))
            {
                if ($addId = Mage::app()->getRequest()->getPost('billing_address_id', false))
                {
                    $customerAddressId = $addId;
                }
                Mage::getSingleton('checkout/type_onepage')->saveBilling(Mage::app()->getRequest()->getPost('billing', array()), $customerAddressId);
            }
        }
    }
}