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
class Aitoc_Aitcheckout_Helper_Aitgiftwrap extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;
    
    /**
     * Check whether the GR module is active or not
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        if($this->_isEnabled === null)
        {
            $this->_isEnabled = ($this->isModuleEnabled('Aitoc_Aitgiftwrap') && Mage::app()->getLayout()->createBlock('aitgiftwrap/giftwrap_onepage')->isShow())?true:false;
        }
        return $this->_isEnabled;
    }
}