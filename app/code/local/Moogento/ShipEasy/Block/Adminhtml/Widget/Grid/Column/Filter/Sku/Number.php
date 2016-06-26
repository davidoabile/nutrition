<?php

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Sku_Number extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        return array(
            array('value' => null, 'label' => ''),
            array(
                'value' => 'single',
                'label' => Mage::helper('moogento_shipeasy')->__('Single-SKU Order'),
                'img' => $this->getSkinUrl('moogento/shipeasy/images/szy_sku_single.png'),
            ),
            array(
                'value' => 'multi',
                'label' => Mage::helper('moogento_shipeasy')->__('Multi-SKU Order'),
                'img' => $this->getSkinUrl('moogento/shipeasy/images/szy_sku_multiple.png'),
            ),
        );
    }

    public function getHtml()
    {
        $html = parent::getHtml();
        $html .= "<script>jQuery('#" . $this->_getHtmlId() . "').ddslick({width: " . ($this->getColumn()->getWidth() ? (int)$this->getColumn()->getWidth() : 75) . "});</script>";

        return $html;
    }

    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        $data = isset($option["img"]) ? ' data-imagesrc="' . $option["img"] . '"' : "";
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.$data.'>'.$this->escapeHtml($option['label']).'</option>';
    }
} 