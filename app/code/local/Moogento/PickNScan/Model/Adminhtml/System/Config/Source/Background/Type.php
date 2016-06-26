<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 17.12.14
 * Time: 13:51
 */

class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Type 
{
    const DEFAULT_BG = 'default';
    const CUSTOM = 'custom';
    const COLOR = 'color';
    const NONE = 'none';

    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => self::DEFAULT_BG,
                'label' => Mage::helper('moogento_pickscan')->__('Default pickNScan image')
            ),
            array(
                'value' => self::CUSTOM,
                'label' => Mage::helper('moogento_pickscan')->__('Custom image')
            ),
            array(
                'value' => self::COLOR,
                'label' => Mage::helper('moogento_pickscan')->__('Plain color')
            ),
            array(
                'value' => self::NONE,
                'label' => Mage::helper('moogento_pickscan')->__('None')
            ),
        );

        return $options;
    }
} 