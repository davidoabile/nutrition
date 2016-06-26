<?php


class Moogento_Automation_Block_Adminhtml_System_Config_Status_Update extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/automation/system/config/status/update.phtml';

    public function initForm()
    {
        return $this;
    }

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');

        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/jquery-ui.min.js');
        $head->addJs('moogento/general/chosen.jquery.min.js');
        $head->addJs('moogento/general/jquery.switchButton.js');
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/knockout.bindings.js');

        $head->addJs('moogento/automation/config/status/update.js');

        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui-1.10.4.custom.min.css');

        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _getJson()
    {
        $data = @unserialize(Mage::getStoreConfig('moogento_automation/update/status'));
        if (is_array($data)) {
            $data = array_values($data);
        } else {
            $data = array();
        }

        return Mage::helper('core')->jsonEncode($data);
    }
} 