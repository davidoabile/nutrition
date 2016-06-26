<?php


class Moogento_ProfitEasy_Block_Adminhtml_Sales_Order_Grid_Column_Renderer_Shipping_Costs
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    public function render(Varien_Object $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $currency_code = $this->_getCurrencyCode($row);

        if (!$currency_code) {
            return $data;
        }

        $html = '<div class="input-group">';
        $html .= '<input id="order_id_' . $this->getColumn()->getIndex() . '_' . $row->getData('entity_id') . '" type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'data-id="' . $row->getData('entity_id') . '" ';
        $html .= 'data-url="' . $this->getColumn()->getUpdateUrl() . '" ';
        $html .= 'value="' . round($row->getData($this->getColumn()->getIndex()), 2) . '"';
        $html .= 'style="max-width: 100px;" ';
        $html .= 'class="input-text order-shipping-costs ' . $this->getColumn()->getInlineCss() . '"/>';
        $html.= '<span class="input-group-addon">'.Mage::app()->getLocale()->currency($currency_code)->getSymbol() .'</span>';
        $html.= '</div>';
        return $html;
    }
} 