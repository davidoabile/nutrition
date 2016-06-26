<?php


class Moogento_CourierRules_Block_Adminhtml_Column_Filter_Connector extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        return array(
            array("value" => "", "label" => "&nbsp;"),
            array(
                "value" => 'not_processed',
                "label" => Mage::helper('moogento_courierrules')->__('Not processed'),
                'data' => array(
                    'selectedtext' => Mage::helper('moogento_courierrules')->__('N/P')
                ),
            ),
            array(
                "value" => 'created',
                "label" => Mage::helper('moogento_courierrules')->__('Label created'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/courierrules/images/label_ready.png')
                ),
            ),
            array(
                "value" => 'not_created',
                "label" => Mage::helper('moogento_courierrules')->__('Label not created'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/courierrules/images/label_problem.png')
                ),
            ),
            array(
                "value" => 'deleted',
                "label" => Mage::helper('moogento_courierrules')->__('Label deleted'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/courierrules/images/label_deleted.png')
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