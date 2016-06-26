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
class Aitoc_Aitcheckout_Block_Checkout_Deliverydate extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_show = null;

    public function isShow()
    {   
        if(is_null($this->_show))
        {
            $this->_show = ($this->helper('aitcheckout')->isModuleEnabled('AdjustWare_Deliverydate') && Mage::getStoreConfigFlag('checkout/adjdeliverydate/enabled'));
        }
        return $this->_show;
    }
    
    protected function _toHtml()
    {
        if($this->isShow())
        {
            return $this->getLayout()
                ->createBlock('adjdeliverydate/container')
                ->setTemplate('aitcheckout/checkout/deliverydate.phtml')
                ->toHtml()
            ;
        }
        return '';
    }
}