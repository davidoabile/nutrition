<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 19.08.14
 * Time: 22:31
 */

class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Order_Flag
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '',
                'label' => ''
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('moogento_pickscan')->__('Flags')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('moogento_pickscan')->__('Custom')
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('moogento_pickscan')->__('Printed?')
            ),
        );



        return $options;
    }
} 