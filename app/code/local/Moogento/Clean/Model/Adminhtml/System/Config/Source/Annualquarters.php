<?php


class Moogento_Clean_Model_Adminhtml_System_Config_Source_Annualquarters
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => 'relative',    'label' => 'Relative: Q-1 = 3 previous complete months, relative to now'),
            array('value' => 'fixed',       'label' => 'Fixed: Q-1 = 3 static months')
        );

        return $options;
    }
} 