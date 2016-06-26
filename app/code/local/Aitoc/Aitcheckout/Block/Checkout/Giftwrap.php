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
class Aitoc_Aitcheckout_Block_Checkout_Giftwrap extends Mage_Core_Block_Template
{
    public function isShow()
    {
        return (Mage::helper('aitcheckout/aitgiftwrap')->isEnabled());
    }

    protected function _toHtml()
    {
        if($this->isShow())
        {
            $html = $this->getLayout()
                ->createBlock('aitgiftwrap/giftwrap_onepage')
                ->setTemplate('aitgiftwrap/giftwrap.phtml')
                ->toHtml();
               
            if($html)
            {
                return $html . parent::_toHtml();
            }
        }
        return '';
    }
}