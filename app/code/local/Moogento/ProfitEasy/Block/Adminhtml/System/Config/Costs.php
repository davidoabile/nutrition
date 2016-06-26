<?php


class Moogento_ProfitEasy_Block_Adminhtml_System_Config_Costs extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/profiteasy/system/config/costs.phtml';

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
        $head->addJs('moogento/general/knockout-sortable.min.js');
        $head->addJs('moogento/general/knockout.bindings.js');

        $head->addJs('moogento/profiteasy/config/costs.js');

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
        return Mage::getResourceModel('moogento_profiteasy/costs_collection')->asJson();
    }

    protected function _getMonthList()
    {
        $list = array();
        for($i = 1; $i <= 12; $i++) {
            $list[$i] = Mage::app()->getLocale()
                            ->date(mktime(null,null,null,$i))
                            ->get(Zend_Date::MONTH_NAME);;
        }

        return $list;
    }

    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();
        return $collection->load();
    }
    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }
    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::getModel('core/store_group')->load($group);
        }
        $stores = $group->getStoreCollection();
        return $stores;
    }
} 