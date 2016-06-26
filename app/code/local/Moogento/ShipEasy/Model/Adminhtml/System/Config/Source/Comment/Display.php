<?php


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Comment_Display
{
    const ALL = 'all';
    const ADMIN_ONLY  = 'admin';
    const FRONTEND_ONLY  = 'frontend';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ALL,
                'label'=>Mage::helper('moogento_shipeasy')->__('All comments'),
            ),
            array(
                'value' => self::ADMIN_ONLY,
                'label'=>Mage::helper('moogento_shipeasy')->__('Admin comments only'),
            ),
            array(
                'value' => self::FRONTEND_ONLY,
                'label'=>Mage::helper('moogento_shipeasy')->__('Frontend-visible comments only'),
            ),
        );
    }
} 