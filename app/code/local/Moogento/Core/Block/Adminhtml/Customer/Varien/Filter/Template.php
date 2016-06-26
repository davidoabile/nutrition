<?php

class Moogento_Core_Block_Adminhtml_Customer_Varien_Filter_Template extends Varien_Filter_Template
{
    public function varDirective($construction)
    {
        if (count($this->_templateVars)==0) {
            // If template preprocessing
            return $construction[0];
        }

        $replacedValue = "";
        if(substr($construction[2], -5) == "|caps"){
            $fild_name = substr($construction[2], 0, -5);
            $replacedValue = strtoupper($this->_getVariable($fild_name, ''));
        } else {
            $replacedValue = $this->_getVariable($construction[2], '');
        }
        return $replacedValue;
    }
}
