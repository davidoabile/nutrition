<?php

class Moogento_PickNScan_Block_Adminhtml_System_Config_Form_Button_Preview extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
    * Set template
    */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/pickscan/config/button/preview.phtml');
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
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'id'        => 'moogento_pickscan_background_preview',
                    'label'     => $this->helper('moogento_pickscan')->__('Preview'),
                    'onclick'   => 'javascript:previewBackground(); return false;'
                ));

        return $button->toHtml();
    }
} 