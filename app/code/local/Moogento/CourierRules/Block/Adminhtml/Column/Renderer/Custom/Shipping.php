<?php

class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Custom_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $mainValue = $row->getData('shipping_method');

        if($mainValue == '__custom__') {
            return parent::render($row);
        }
        else {
            return "";
        }
    }
}