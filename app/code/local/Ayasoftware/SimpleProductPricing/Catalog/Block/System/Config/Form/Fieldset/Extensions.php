<?php
class Ayasoftware_SimpleProductPricing_Catalog_Block_System_Config_Form_Fieldset_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Field {
	/**
         * Get the Version of the current release
         * @param Varien_Data_Form_Element_Abstract $element
         * @return string
         */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		$version = Mage::getConfig()->getModuleConfig("Ayasoftware_SimpleProductPricing")->version;
		return '<span class="notice"><strong>' . $version . '</strong></span>';	
	}   
}