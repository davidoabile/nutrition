<?php

class Moogento_PickNScan_Block_Adminhtml_Config extends Mage_Adminhtml_Block_System_Config_Form
{
    protected function _construct()
    {
        parent::_construct();
        if (!Mage::helper('moogento_pickscan')->isAvailable()) {
            $this->setTemplate('moogento/pickscan/pickpack.phtml');
        }
        return $this;
    }
} 