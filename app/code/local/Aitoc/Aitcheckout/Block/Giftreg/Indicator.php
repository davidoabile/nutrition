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
class Aitoc_Aitcheckout_Block_Giftreg_Indicator extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if(Mage::helper('aitcheckout/adjgiftregistry')->isEnabled())
        {
            return $this->getLayout()
                ->createBlock('adjgiftreg/indicator')
                ->setTemplate('adjgiftreg/indicator.phtml')
                ->toHtml()
            ;
        }
        return '';
    }
}