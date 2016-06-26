<?php


class Moogento_Clean_Model_Adminhtml_System_Config_Source_Month
{
    public function toOptionArray()
    {
        $options = array();
        $date = new DateTime();
        for($i=1;$i<=12;$i++){
            $date->setDate($date->format('Y'), $i, 1);
            $options[]=array('value' => $i, 'label' => $date->format('F'));
        }
        return $options;
    }
} 