<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Size
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'auto',
                'label' => Mage::helper('moogento_pickscan')->__('Auto')
            ),
            array(
                'value' => 'cover',
                'label' => Mage::helper('moogento_pickscan')->__('Fullsize')
            ),
            array(
                'value' => 'contain',
                'label' => Mage::helper('moogento_pickscan')->__('Proportional resize by X/Y')
            ),
        );

        return $options;
    }
} 