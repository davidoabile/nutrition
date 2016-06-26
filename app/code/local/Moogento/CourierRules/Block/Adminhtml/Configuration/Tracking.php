<?php

class Moogento_CourierRules_Block_Adminhtml_Configuration_Tracking extends Mage_Adminhtml_Block_Widget
{

    protected $_template = 'moogento/courierrules/tracking.phtml';

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
        $head->addJs('moogento/courierrules/tracking.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/courierrules/courierrules.css');

        return parent::_prepareLayout();
    }

    protected function _getPoolsJson()
    {
        $collection = Mage::getModel('moogento_courierrules/tracking')->getCollection();

        return $collection->asJson();
    }

    protected function _getConfig()
    {
        return Mage::helper('core')->jsonEncode(Mage::getStoreConfig('courierrules/tracking'));
    }

} 