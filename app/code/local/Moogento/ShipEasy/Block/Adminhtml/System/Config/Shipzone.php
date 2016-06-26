<?php


class Moogento_ShipEasy_Block_Adminhtml_System_Config_Shipzone extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
        public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            foreach ($element->getSortedElements() as $field) {
                $html.= $field->toHtml();
            }
        } else {
            $html .= '<p style="color: red;">'.$this->__('<b style="font-size:28px;margin:-2px 0 0;display:inline;float:left">&#10154;</b> You need <a href = "https://moogento.com/courierrules">courierRules</a> installed for this to work.').'</p>';
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}