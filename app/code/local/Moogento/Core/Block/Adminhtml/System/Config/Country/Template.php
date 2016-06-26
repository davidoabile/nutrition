<?php


class Moogento_Core_Block_Adminhtml_System_Config_Country_Template extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/core/system/config/countrytemplate.phtml';

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
        $head->addJs('moogento/general/knockout-sortable.min.js');


        $head->addJs('moogento/core/countrytemplate/countrytemplate.js');

        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui.min.css');
        $head->addCss('moogento/general/font-awesome/css/font-awesome.min.css');
        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _getJson()
    {
        $value = array();

        $templates = Mage::getModel("moogento_core/country_template")->getCollection()->setOrder('sort_number', 'ASC');

        $value = $templates->getData();

        if (!$value) {
            $value = array();
        }
        return Mage::helper('core')->jsonEncode($value);
    }
}