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
if (version_compare(Mage::getVersion(), '1.7.0.0', '<'))
{
    class Aitoc_Aitcheckout_Block_Captcha extends Mage_Core_Block_Template
    {
    
    }
}
else
{
    class Aitoc_Aitcheckout_Block_Captcha extends Mage_Captcha_Block_Captcha
    {
    
        protected function _prepareLayout()
        {
            $headBlock = $this->getLayout()->getBlock('head');
			if($headBlock)
			{
				$headBlock->addJs('mage/captcha.js');
			}
			
            return parent::_prepareLayout();
        }    
    
    }
}