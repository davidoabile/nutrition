<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 18.08.14
 * Time: 7:58
 */

class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Product_Title
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'name',
                'label' => Mage::helper('moogento_pickscan')->__('Product name'),
            ),
            array(
                'value' => 'sku',
                'label' => Mage::helper('moogento_pickscan')->__('SKU'),
            ),
            array(
                'value' => 'custom',
                'label' => Mage::helper('moogento_pickscan')->__('Custom attribute'),
            )
        );
    }
} 