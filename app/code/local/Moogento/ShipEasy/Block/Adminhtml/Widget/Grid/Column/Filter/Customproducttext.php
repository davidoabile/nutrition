<?php 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Customproducttext extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        $value = $this->getValue();
        $condition = 'like';
        return array($condition=>'%'.$this->_escapeValue($value).'%');
    }
}