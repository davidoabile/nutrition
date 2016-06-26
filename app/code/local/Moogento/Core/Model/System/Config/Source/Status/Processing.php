<?php


class Moogento_Core_Model_System_Config_Source_Status_Processing
{
    CONST MAGENTO_DEFAULT = 'default';
    CONST CUSTOM = 'custom';

    public function toOptionArray()
    {
        $helper = Mage::helper('moogento_core');
        return array(
            self::MAGENTO_DEFAULT => $helper->__('Magento default'),
            self::CUSTOM => $helper->__('Custom'),
        );
    }
} 