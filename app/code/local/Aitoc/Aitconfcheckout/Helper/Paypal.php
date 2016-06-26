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
class Aitoc_Aitconfcheckout_Helper_Paypal extends Mage_Core_Helper_Abstract
{
    protected $configs = array();

    public function __construct()
    {
        foreach(array('billing','shipping') as $sType)
        {
            if(!isset($this->_configs[$sType]))
            {
                $this->_configs[$sType] = array();
            }

            $aAllowedFieldHash = Mage::helper('aitconfcheckout')->getAllowedFieldHash($sType);

            foreach ($aAllowedFieldHash as $sKey => $bValue)
            {
                $this->_configs[$sType][$sKey] = $bValue;
            }
        }

    }

    public function checkFieldShow($sType,$sKey)
    {
        if (!$sKey || !isset($this->_configs[$sType]) || !isset($this->_configs[$sType][$sKey]))
        {
            return false;
        }

        if ($this->_configs[$sType][$sKey])
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}