<?php

class Moogento_CourierRules_Block_Adminhtml_Form_Element_Multiselect extends Varien_Data_Form_Element_Multiselect
{
    public function getHtmlAttributes()
    {
        $attr = parent::getHtmlAttributes();
        $attr[] = 'data-bind';
        return $attr;
    }
} 