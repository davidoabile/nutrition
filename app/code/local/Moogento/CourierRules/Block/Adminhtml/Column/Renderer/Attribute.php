<?php

class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $attribute = Mage::helper('moogento_courierrules')->getAttribute('product_attribute');
        $inputType = $attribute->getFrontend()->getInputType();

        if ($inputType == 'multiselect') {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $result = array();
                foreach ($value as $val) {
                    $result[] = $attribute->getSource()->getOptionText($val);
                }
                return implode(',', $result);
        } else if ($attribute->usesSource()) {
            return $attribute->getSource()->getOptionText($value);
        }


        return $value;
    }
} 