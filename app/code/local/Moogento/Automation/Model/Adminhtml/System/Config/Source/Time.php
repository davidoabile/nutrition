<?php

class Moogento_Automation_Model_Adminhtml_System_Config_Source_Time
{
    public function toOptionArray()
    {
        $options = array();

        for ($i = 0; $i < 24; $i++) {
            $options[] = array(
                'value' => $i,
                'label' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
            );
        }

        return $options;
    }
}