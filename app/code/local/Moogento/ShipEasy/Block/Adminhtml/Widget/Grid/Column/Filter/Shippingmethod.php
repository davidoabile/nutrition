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
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Shippingmethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
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
        
        $html.='<input type="text" name="'.$this->_getHtmlName().'[szy_filter_shippingmethod]" id="szy_filter_shippingmethod" value="'.$this->getValue('szy_filter_shippingmethod').'"><br/>';
        
        $html.='Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
        return $html;
    }
    
    
    protected function _getOptions()
    {
        $emptyOption = array('value' => null, 'label' => '');

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions) ) {
            $options = array($emptyOption);
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
            $options[] = array('value' => 'szy_shipping_custom_value', 'label' => 'Custom value');
            return $options;
        }
        return array();
    }
    
    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        return htmlspecialchars($value);
    }
    
    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }
    
    

    public function getCondition()
    {
        $return = array();
        $return['value'] = $this->getValue('value');
        $return['custom_value'] = $this->getValue('szy_filter_shippingmethod');
        $return['condition'] = $this->getValue('exclude'); 
        return $return;
    }

}
