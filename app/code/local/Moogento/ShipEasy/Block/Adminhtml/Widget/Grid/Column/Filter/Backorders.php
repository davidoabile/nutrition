<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Custom.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Backorders
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getHtml()
    {
        $array_options = Mage::helper('moogento_shipeasy/functions')->getValueSetStockOption("backorder_images_of_status");
        $html = '<select name="'.$this->_getHtmlName().'[value]" id="'.$this->_getHtmlId().'" class="no-changes">';
        $value = $this->getValue('value');
        $html .= '<option value=""></option>';
        for($i=0; $i<=2; $i++){
            if(isset($array_options[$i])){
                $array_options[$i]['value'] = $i;
                $html .= $this->_renderOption($array_options[$i], $value);
            }
        }
        $html .= "<script>jQuery('#" . $this->_getHtmlId() . "').ddslick({width: 75});</script>";
        return $html;
    }
    
    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        return htmlspecialchars($value);
    }
    
    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        $data = isset($option["img"]) ? ' data-imagesrc="' . $option["img"] . '"' : "";
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.$data.'>'.$this->escapeHtml($option['label']).'</option>';
    }
}
