<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
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
* @license    http://www.moogento.com/License.html
*/   
class Moogento_CourierRules_Block_Adminhtml_Column_Filter_Courierrulesdescription extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getHtml()
    {
        $html = '<select name="'.$this->_getHtmlName().'[value]" id="'.$this->_getHtmlId().'" class="no-changes">';
        $value = $this->getValue('value'); 
        $flag_custom_value = true;
        
        $html .= '<option value=""></option>';
        $html .= '<optgroup label="Single CourierRules">';
        foreach (Mage::helper('moogento_courierrules')->getCourierRulesDropdownOptions() as $key => $option){
            $html .= $this->_renderOption(array('value' => $key, 'label' => $option), $value);
        }
        $html .= '</optgroup>';

        if (Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')) {
            $not_single_groups
                = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group'));

            if (is_array($not_single_groups)) {
                foreach ($not_single_groups as $key => $option) {
                    $html .= '<optgroup label="' . $key . '">';
                    /*foreach ($option["courierrules"] as $k => $val){
                        if($val == "custom_value"){
                            $html .= $this->_renderOption(array('value' => $option["custom_value"], 'label' => $option["custom_value"]), $value);
                        } else{
                            $html .= $this->_renderOption(array('value' => $val, 'label' => $val), $value);
                        }
                    }*/
                    $html .= $this->_renderOption(array('value' => "groups___" . $key, 'label' => $key), $value);
                    $html .= '</optgroup>';
                }
            }
        }
        if(!is_null($value)){
            if("custom_value" == $value){
                $html .= '<option value="custom_value" selected="selected">Custom value</option>';
                $flag_custom_value = false;
            }
        }
        
        if($flag_custom_value){
            $html .= '<option value="custom_value">Custom value</option>';
        }
        
        $html.='</select>';
        $checked = ($this->getValue('exclude')) ? 'checked="checked"' : '';
        
        $html.='<input type="text" name="'.$this->_getHtmlName().'[szy_filter_courierrules_description]" id="szy_filter_courierrules_description" value="'.$this->getValue('szy_filter_courierrules_description').'"><br/>';
       
        $html.='<script>checkCRCustomValueFilter();</script>';
        //$html.='Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
        return $html;
    }
    
    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        return htmlspecialchars($value);
    }
    
    protected function _renderOption($option, $value)
    {
        $selected = "";
        if(!is_null($value)){
            $selected = (($option['value'] == $value) ? ' selected="selected"' : '' );
        }
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }
    
    

//    public function getCondition()
//    {
//        if(is_null($this->getValue('szy_filter_courierrules_description'))){
//            $pos = strpos($this->getValue('value'), "groups___");
//            if($pos === false){
//                return array('like' => "%".$this->getValue('value')."%");
//            } else {
//                $group = str_replace("groups___", "", $this->getValue('value'));
//                $not_single_groups = unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group'));
//                
//                foreach ($not_single_groups[$group] as $key => $option){
//                    
//                    /*foreach ($option["courierrules"] as $k => $val){
//                        if($val == "custom_value"){
//                            $html .= $this->_renderOption(array('value' => $option["custom_value"], 'label' => $option["custom_value"]), $value);
//                        } else{
//                            $html .= $this->_renderOption(array('value' => $val, 'label' => $val), $value);
//                        }
//                    }*/
//                }
//            }            
//        } else {
//            return array('like' => "%".$this->getValue('szy_filter_courierrules_description')."%");
//        }
        /*if(is_null($this->getValue('szy_filter_courierrules_description'))){
            return array('val' => $this->getValue('value'));
        } else {
            return array('val' => $this->getValue('szy_filter_courierrules_description'));
        }*/
//    }

}
