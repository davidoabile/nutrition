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
* File        Contact.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 


class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Courierrulesdescription
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $rules = Mage::helper('moogento_courierrules')->getRules();

        if (is_null($row->getCourierrulesProcessed())) {
            $result = '<p style="color:grey;font-style:italic;" data-track="'.$row->getCourierrulesTracking().'" title="">'.$this->__("Pending Sync")."</p>";
        } else {
            if (!$row->getCourierrulesDescription()) {
                $result = '<p style="color:grey" title="' . $this->__("No Matching courierRule") . '">'.$this->__("n/a")."</p>";
            } else {
                $result = '<p data-track="'.$row->getCourierrulesTracking().'">'.$row->getCourierrulesDescription().'</p>';
            }
        }

        if (strpos($row->getCourierrulesDescription(), 'suggestion') !== false) {
            $suggestion = Mage::getModel('moogento_courierrules/connector_suggestion')->load($row->getId(), 'order_id');
            if ($suggestion->getId()) {
                $result .= '<a target="_blank" href="' . $this->getUrl('*/courierrules_connector/suggestions',
                        array('order_id' => $row->getId())) . '">' . Mage::helper('moogento_courierrules')
                                                                         ->__('Check suggestions') . '</a>';
            }
        }
        $result .= '<select class="chosen-select" name="'.'courierrules_description_'.$row->getEntityId().'">';
        if(is_null($row->getCourierrulesRuleId()) && is_null($row->getCourierrules())){
            $result .= '<option value="empty" selected="selected">     </option>';
        } else {
            $result .= '<option value="empty">     </option>';
        }
        foreach($rules as $rule) {
            if($rule->getId() == $row->getCourierrulesRuleId()){
                $result .= '<option value="'.$rule->getId().'" selected="selected">';
            } else {
                $result .= '<option value="'.$rule->getId().'" >';
            }
            $result .= $rule->getName();
            $result .= '</option>';
        }
        if(is_null($row->getCourierrulesRuleId()) && !is_null($row->getCourierrulesDescription())){
            $input_value = $row->getCourierrulesDescription();
            $result .= '<option value="custom" selected="selected">Custom</option>';
        } else {
            $input_value = "";
            $result .= '<option value="custom">Custom</option>';
        }
        $result .= '</select>';
        $result .= '<input style="display:none;" name="courierrules_input" value="'.$input_value.'" class="input-text courierrules_custom" type="text" data-url="'.$this->getUrl('*/courierrules_rule/updateCourierRuleOrder').'" ></input>';
        $result .= '<span class="edit-icon courierrulesdescription"></span>';
        return $result;
    }
}
