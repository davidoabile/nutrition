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
class Aitoc_Aitconfcheckout_Model_Rewrite_PaypalApiNvp extends Mage_Paypal_Model_Api_Nvp
{
    public function callDoExpressCheckoutPayment()
    {
        if(
            !Mage::getStoreConfig('aitconfcheckout/shipping/active') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/address') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/city') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/region') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/country') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/postcode') ||
            !Mage::getStoreConfig('aitconfcheckout/shipping/telephone')
          )
        {
            $this->setSuppressShipping(true);
        }

        parent::callDoExpressCheckoutPayment();
    }
}