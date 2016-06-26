<?php

class Moogento_PickNScan_Block_Adminhtml_System_Config_Form_Button_Generate extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
    * Set template
    */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/pickscan/config/button/generate.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getClickUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order_pickscan/generateTrolleyBarcodes');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                       ->setData(array(
                           'id'        => 'generate_button',
                           'label'     => $this->helper('adminhtml')->__('Generate'),
                           'onclick'   => "javascript:setLocation('" . $this->getClickUrl() .  "'); return false;",
                       ));

        return $button->toHtml();
    }
} 