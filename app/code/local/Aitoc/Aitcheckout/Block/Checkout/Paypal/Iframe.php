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
class Aitoc_Aitcheckout_Block_Checkout_Paypal_Iframe extends Mage_Core_Block_Template
{
    public function isShow()
    {
        return Mage::helper('aitcheckout')->isPaypalAdvancedAvailable();
    }

    protected function _toHtml()
    {
        if($this->isShow())
        {
            $block = $this->getLayout()->createBlock('paypal/iframe', 'paypal.iframe');
            if (Mage::registry('aitcheckout_paypal_review_block_rendering')) {
                Mage::register('aitcheckout_paypal_iframe_block', $block);
            }
            $html = $block->toHtml();
               
            if ($html) {
                return $html . parent::_toHtml();
            }
        }
        return '';
    }
}