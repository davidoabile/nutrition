<?php


class Moogento_PickNScan_Block_Adminhtml_System_Config_Form_Auth extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    public function initForm()
    {
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/pickscan/config/auth.phtml');
    }

    protected function _getUsers()
    {
        return Mage::getResourceModel('admin/user_collection');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
} 