<?php


class Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Background_Align_Horizontal
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'left',
                'label' => Mage::helper('moogento_powerlogin')->__('Left')
            ),
            array(
                'value' => 'center',
                'label' => Mage::helper('moogento_powerlogin')->__('Center')
            ),
            array(
                'value' => 'right',
                'label' => Mage::helper('moogento_powerlogin')->__('Right')
            ),
        );

        return $options;
    }
} 