<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Background_Align_Vertical
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'top',
                'label' => Mage::helper('moogento_pickscan')->__('Top')
            ),
            array(
                'value' => 'center',
                'label' => Mage::helper('moogento_pickscan')->__('Center')
            ),
            array(
                'value' => 'bottom',
                'label' => Mage::helper('moogento_pickscan')->__('Bottom')
            ),
        );

        return $options;
    }
} 