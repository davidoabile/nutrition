<?php


class Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Order_Status extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
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