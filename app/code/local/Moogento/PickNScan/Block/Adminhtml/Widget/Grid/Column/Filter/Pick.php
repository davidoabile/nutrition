<?php


class Moogento_PickNScan_Block_Adminhtml_Widget_Grid_Column_Filter_Pick extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select

{
    protected function _getOptions()
    {
        return array(
            array("value" => "", "label" => "&nbsp;"),
            array(
                "value" => -1,
                "label" => Mage::helper('moogento_pickscan')->__('Not Assigned'),
                'data' => array(
                    'selectedtext' => Mage::helper('moogento_pickscan')->__('N/A')
                ),
            ),
            array(
                "value" => Moogento_PickNScan_Model_Picking::STATUS_ASSIGNED,
                "label" => Mage::helper('moogento_pickscan')->__('Assigned'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/pickscan/images/pick_notstarted.png')
                ),
            ),
            array(
                "value" => Moogento_PickNScan_Model_Picking::STATUS_STARTED,
                "label" => Mage::helper('moogento_pickscan')->__('Started'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/pickscan/images/pick_incomplete.png')
                ),
            ),
            array(
                "value" => Moogento_PickNScan_Model_Picking::STATUS_COMPLETE_ANOMALIES,
                "label" => Mage::helper('moogento_pickscan')->__('Complete with a sub or a skip'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/pickscan/images/pick_anomalies.png')
                ),
            ),
            array(
                "value" => Moogento_PickNScan_Model_Picking::STATUS_COMPLETE,
                "label" => Mage::helper('moogento_pickscan')->__('Complete'),
                'data'=> array(
                    'imagesrc' => $this->getSkinUrl('moogento/pickscan/images/pick_complete.png')
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