<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 19.08.14
 * Time: 22:31
 */

class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Order_Status
{
    public function toOptionArray()
    {
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($statuses as $code=>$label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
} 