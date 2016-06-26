<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Cn22.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Cn22
    extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Actions
{
    protected function _getInstallShippingZonesMessage()
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        $html = '<tr><td colspan="' . $colspan . '" >Additional feature from <b>Courierrules Module <b/><br/> <span style="color:#ff0000" >To enable automated features, please install </span> <b><a href="https://www.moogento.com/courierrules" target="_blank">Courierrules</a></b></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerHeaderShippingZones($title='')
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
           
        $html = '<tr><td colspan="' . $colspan . '" >Additional shipping zones filter feature from <b><a href="https://www.moogento.com/courierrules" target="_blank">Moogento Courierrules Module</a><b/><br/> Pickpack will NOT print Cn22 label for the orders which shipping country is in the filter list.<br/><span style="color:#ff0000" ></td></tr>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }
    

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        foreach ($element->getSortedElements() as $field) {
            if (
            ($field->getId() == 'cn22_options_custom_section_filter_shipping_zone_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')))
                {
                    $html .= $this->_getFieldsContainerHeaderShippingZones();
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Shipping zone filter','warehouse_column',1);
                    
                }
            } 

            if (
            ($field->getId() == 'cn22_options_custom_section_auto_check_3_1')
            )
            {
                    $html .= $this->_getFieldsContainerHeaderWithClass('Shipment contents declaration','custom_cn22_group');
            } 
			
            $html .= $field->toHtml();

            
             if (
            ($field->getId() == 'cn22_options_custom_section_filter_shipping_zone')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')))
                    $html .= $this->_getFieldsContainerFooter();
            } 

            if (
            ($field->getId() == 'cn22_options_custom_section_auto_check_3_4_position')
            )
            {
                    $html .= $this->_getFieldsContainerFooter();
            } 
            
        }
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
