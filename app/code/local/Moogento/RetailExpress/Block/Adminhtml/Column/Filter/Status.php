<?php


class Moogento_RetailExpress_Block_Adminhtml_Column_Filter_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select

{
    protected function _getOptions()
    {
        return array(
            array("value" => "", "label" => "&nbsp;"),
            array(
                "value" => Moogento_RetailExpress_Model_Retailexpress_Status::PENDING,
                "label" => Mage::helper('moogento_retailexpress')->__('Pending'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/retailexpress/status_pending.png')
                ),
            ),
            array(
                "value" => Moogento_RetailExpress_Model_Retailexpress_Status::SUCCESS,
                "label" => Mage::helper('moogento_retailexpress')->__('Success'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/retailexpress/status_success.png')
                ),
            ),
            array(
                "value" => Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY,
                "label" => Mage::helper('moogento_retailexpress')->__('Failed / Pending retry'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/retailexpress/status_pending_retry.png')
                ),
            ),
            array(
                "value" => Moogento_RetailExpress_Model_Retailexpress_Status::ERROR,
                "label" => Mage::helper('moogento_retailexpress')->__('Error'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/retailexpress/status_error.png')
                ),
            ),
        );
    }

    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        $data = '';
        if (isset($option['data'])) {
            foreach ($option['data'] as $name => $value) {
                $data .= ' data-' . $name . '="' . $value . '"';
            }
        }
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.$data.'>'.$this->escapeHtml($option['label']).'</option>';
    }

    public function getHtml()
    {
        $html = parent::getHtml();
        $html .= "<script>jQuery('#" . $this->_getHtmlId() . "').ddslick({width: 50});</script>";
        return $html;
    }
} 