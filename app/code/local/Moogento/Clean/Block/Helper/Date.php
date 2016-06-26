<?php


class Moogento_Clean_Block_Helper_Date extends Varien_Data_Form_Element_Date
{
    public function getElementHtml()
    {
        if (!Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_MAIN_CSS)) {
            return parent::getElementHtml();
        }
        $this->addClass('input-text');

        $outputFormat = $this->getFormat();
        if (empty($outputFormat)) {
            throw new Exception('Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().');
        }

        $html = '<div id="' . $this->getHtmlId() . '" class="input-append date">'
            . '<input value="'. $this->_escape($this->getValue()) .'" name="' . $this->getName() . '"' . $this->serialize($this->getHtmlAttributes()) .' data-format="' . $outputFormat . '" type="text" />'
            . '<span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>'
          . '</div>';

        $html .= '<script type="text/javascript">'
            . 'jQuery(function() { jQuery("#' . $this->getHtmlId() . '").datetimepicker({
                pickTime: false
            });});'
            . '</script>';

        $html .= $this->getAfterElementHtml();

        return $html;
    }
} 