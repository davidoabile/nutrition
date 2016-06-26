<?php


class Moogento_Clean_Model_Adminhtml_System_Config_Source_Order_Status extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    public function toOptionArray()
    {
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();

        $options = array();
        foreach ($statuses as $code=>$label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
} 