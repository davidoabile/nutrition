<?php


class Moogento_Clean_Block_Adminhtml_Widget_Grid_Column_Filter_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price
{


    public function getHtml()
    {
        if (!Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_GRID_CSS)) {
            return parent::getHtml();
        }

        $html = '<div class="range-filter input-group' . ($this->getDisplayCurrencySelect() ? ' input-group-select': '' ). '">'
            . '<input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$this->_getHtmlId().'_from" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/>'

            . '<span class="input-group-addon">' . $this->__('>') . '</span>'

            . '<input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$this->_getHtmlId().'_to" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/>';

        if ($this->getDisplayCurrencySelect())
            $html .= $this->_getCurrencySelectHtml();
        $html .= '</div>';

        return $html;
    }

} 