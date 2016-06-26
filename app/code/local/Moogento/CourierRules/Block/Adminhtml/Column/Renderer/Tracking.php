<?php

class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Tracking extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        return Mage::getModel('moogento_courierrules/tracking')->load($value)->getName();
    }
} 