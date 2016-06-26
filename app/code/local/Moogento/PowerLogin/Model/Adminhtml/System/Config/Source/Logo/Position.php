<?php


class Moogento_PowerLogin_Model_Adminhtml_System_Config_Source_Logo_Position
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'label' => Mage::helper('moogento_powerlogin')->__('Screen'),
                'value' => array(
                    array(
                        'value' => 'top_left',
                        'label' => Mage::helper('moogento_powerlogin')->__('Top Left'),
                    ),
                    array(
                        'value' => 'top_center',
                        'label' => Mage::helper('moogento_powerlogin')->__('Top Middle'),
                    ),
                    array(
                        'value' => 'top_right',
                        'label' => Mage::helper('moogento_powerlogin')->__('Top Right'),
                    ),
                    array(
                        'value' => 'bottom_left',
                        'label' => Mage::helper('moogento_powerlogin')->__('Bottom Left'),
                    ),
                    array(
                        'value' => 'bottom_center',
                        'label' => Mage::helper('moogento_powerlogin')->__('Bottom Middle'),
                    ),
                    array(
                        'value' => 'bottom_right',
                        'label' => Mage::helper('moogento_powerlogin')->__('Bottom Right'),
                    ),
                ),
            ),
            array(
                'label' => Mage::helper('moogento_powerlogin')->__('Login Box'),
                'value' => array(
                    array(
                        'value' => 'login_top',
                        'label' => Mage::helper('moogento_powerlogin')->__('Top'),
                    ),
                    array(
                        'value' => 'login_bottom',
                        'label' => Mage::helper('moogento_powerlogin')->__('Bottom'),
                    ),
                ),
            ),
        );

        return $options;
    }
} 