<?php

class Moogento_RetailExpress_Block_Adminhtml_System_Config_Hint extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function setForm($form)
    {}
    public function setConfigData($data)
    {}


    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return '<span id="' . $element->getHtmlId() . '" style="background: green; color: white; padding: 3px 5px;">' . Mage::helper('moogento_retailexpress')->__('With these settings all orders will be sent to RetailExpress') . '</span>';
    }
}