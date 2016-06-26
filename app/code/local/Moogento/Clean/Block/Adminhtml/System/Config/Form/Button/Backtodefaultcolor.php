<?php
class Moogento_Clean_Block_Adminhtml_System_Config_Form_Button_Backtodefaultcolor extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/clean/system/config/form/button/backtodefaultcolor.phtml');
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
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_notifications/resetColor');
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
                'id'        => 'save_button',
                'label'     => $this->helper('moogento_clean')->__('Set Default Colors'),
                'onclick'   => 'javascript:saveCondition(); return false;'
            ));

        return $button->toHtml();
    }
} 