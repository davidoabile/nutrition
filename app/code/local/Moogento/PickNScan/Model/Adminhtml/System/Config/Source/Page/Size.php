<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Page_Size
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'A4',
                'label' => Mage::helper('moogento_pickscan')->__('A4')
            ),
            array(
                'value' => 'Letter',
                'label' => Mage::helper('moogento_pickscan')->__('Letter')
            ),
            array(
                'value' => 'custom',
                'label' => Mage::helper('moogento_pickscan')->__('Custom')
            ),
        );

        return $options;
    }
} 