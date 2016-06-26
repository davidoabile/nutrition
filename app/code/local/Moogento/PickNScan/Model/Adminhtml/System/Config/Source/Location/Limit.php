<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Location_Limit
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '0',
                'label' => Mage::helper('moogento_pickscan')->__('All'),
            ),
        );

        for ($i = 1; $i <= 8; $i++) {
            $options[] = array(
                'value' => $i,
                'label' => Mage::helper('moogento_pickscan')->__('First %d characters', $i),
            );
        }

        return $options;
    }
} 