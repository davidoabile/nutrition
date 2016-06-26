<?php


class Moogento_Clean_Block_Adminhtml_Widget_Grid_Column_Filter_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Date
{
    public function getHtml()
    {
        if (!Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_GRID_CSS)) {
            return parent::getHtml();
        }

        $htmlId = $this->_getHtmlId() . time();
        $format = 'dd/MM/yy';

        $html = '<div class="range-filter input-group range-filter-date">'
            . '<input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$htmlId.'_from"'
            . ' data-format="' . $format . '" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/>'

            . '<span class="input-group-addon">' . $this->__('to') . '</span>'

            . '<input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$htmlId.'_to"'
            . ' data-format="' . $format . '" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/>'
            . '</div>';
        $html.= '<input type="hidden" name="'.$this->_getHtmlName().'[locale]"'
            . 'value="'.$this->getLocale()->getLocaleCode().'"/>';
        $html.= '<script type="text/javascript">
            jQuery(function() {
                jQuery("#'.$htmlId.'_from").datetimepicker({
                    pickTime: false
                });
                jQuery("#'.$htmlId.'_to").datetimepicker({
                    pickTime: false
                });
            });
            </script>';
        return $html;
    }
} 