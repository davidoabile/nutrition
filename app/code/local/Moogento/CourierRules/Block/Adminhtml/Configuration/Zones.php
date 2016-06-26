<?php

class Moogento_CourierRules_Block_Adminhtml_Configuration_Zones extends Mage_Adminhtml_Block_Widget
{

    protected $_template = 'moogento/courierrules/zones.phtml';

    public function initForm()
    {
        return $this;
    }

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');

        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/chosen.jquery.min.js');
        $head->addJs('moogento/general/knockout.js');

        $head->addJs('moogento/courierrules/base.js');
        $head->addJs('moogento/courierrules/zones.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/courierrules/courierrules.css');

        return parent::_prepareLayout();
    }

    protected function _getZonesJson()
    {
        $collection = Mage::getModel('moogento_courierrules/zone')->getCollection();

        return $collection->asJson();
    }

    protected function _getCountries()
    {
        return Mage::getModel('directory/country')->getResourceCollection()
                    ->toOptionArray();
    }
}