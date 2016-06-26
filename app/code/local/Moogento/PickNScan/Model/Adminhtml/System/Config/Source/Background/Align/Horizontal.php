<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Align_Horizontal
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'left',
                'label' => Mage::helper('moogento_pickscan')->__('Left')
            ),
            array(
                'value' => 'center',
                'label' => Mage::helper('moogento_pickscan')->__('Center')
            ),
            array(
                'value' => 'right',
                'label' => Mage::helper('moogento_pickscan')->__('Right')
            ),
        );

        return $options;
    }
} 