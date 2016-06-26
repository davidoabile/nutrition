<?php


class Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Background_Align_Vertical
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'top',
                'label' => Mage::helper('moogento_powerlogin')->__('Top')
            ),
            array(
                'value' => 'center',
                'label' => Mage::helper('moogento_powerlogin')->__('Center')
            ),
            array(
                'value' => 'bottom',
                'label' => Mage::helper('moogento_powerlogin')->__('Bottom')
            ),
        );

        return $options;
    }
} 