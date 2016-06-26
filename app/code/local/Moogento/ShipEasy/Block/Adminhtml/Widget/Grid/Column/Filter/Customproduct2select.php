<?php 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Customproduct2select extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getCondition()
    {
        $value = $this->getValue();
        $condition = 'like';
        return array($condition=>'%'.$this->_escapeValue($value).'%');
    }
    
    protected function _getOptions()
    {
        $attribute_code = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside');
        $attribute_code = (is_null($attribute_code) || $attribute_code == "") ? "sku" : $attribute_code;

        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
        $options = $attribute->getSource()->getAllOptions(false);
        array_unshift($options, array("value" => "", "label" => ""));
        foreach ($options as $key => $option) {
            $options[$key]['value'] = $option['label'];
        }
        return $options;
        
    }
}