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


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Custom
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getHtml()
    {
        $html = '<select name="'.$this->_getHtmlName().'[value]" id="'.$this->_getHtmlId().'" class="no-changes">';
        $value = $this->getValue('value');
        foreach ($this->_getOptions() as $option){
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html.='</select>';
        $checked = ($this->getValue('exclude')) ? 'checked="checked"' : '';
        $html .= '<input type="text" name="'.$this->_getHtmlName().'[custom_value_1]" id="custom_value_1" value="' . $this->getValue('custom_value_1') .'" />';
        $html .= '<input type="text" name="'.$this->_getHtmlName().'[date_custom_value_1]" id="date_custom_value_1"  value="' . $this->getValue('date_custom_value_1') .'" />';
        $html .= 'Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
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
        $data = '';

        if ($option['value']) {
            $render_data = Mage::helper('moogento_shipeasy/functions')->renderCustom(
                $this->getColumn()->getIndex(), $option['label']
            );
        }
        if(isset($render_data['flag']) && (strlen($render_data['flag']) > 0))
        {
            $flag = $render_data['flag'];
            $flag = trim($flag);
            $flag = str_replace('{{','',$flag);
            $flag = str_replace('}}','',$flag);
            $flag = str_replace('{','',$flag);
            $flag = str_replace('}','',$flag);
            $image_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/moogento/shipeasy/images/flag_images/'.$flag;
            $data .= ' data-imagesrc="' . $image_url . '"';
        }
        if(isset($render_data['color']) && (strlen($render_data['color']) >0)) {
            $data .= ' data-color="' . $render_data['color'] . '"';
        }

        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.$data.'>'.$this->escapeHtml($option['label']).'</option>';
    }


    public function getCondition()
    {
        $return = array();
        $return['value'] = $this->getValue('value');
        $return['custom_value'] = $this->getValue('custom_value_1');
        $return['date'] = $this->getValue('date_custom_value_1');
        $return['condition'] = $this->getValue('exclude'); 
        return $return;
    }

}
