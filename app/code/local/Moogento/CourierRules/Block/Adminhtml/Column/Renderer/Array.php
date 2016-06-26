<?php

class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Array extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        return is_array($value) ? implode(($this->getColumn()->getSeparator() ? $this->getColumn()->getSeparator() : ', '), $value) : $value;
    }
} 