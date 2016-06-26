<?php


class Moogento_CourierRules_Block_Adminhtml_Configuration_Connectors extends Mage_Adminhtml_Block_Widget
{
    protected $_template = 'moogento/courierrules/connectors.phtml';

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
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/jquery.switchButton.js');

        $head->addJs('moogento/courierrules/base.js');
        $head->addJs('moogento/courierrules/connectors.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/general/font-awesome/css/font-awesome.min.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui-1.10.4.custom.min.css');

        return parent::_prepareLayout();
    }

    protected function _getConnectorsJson()
    {
        $config = new Varien_Object();
        $config->setConnectors(array());
        Mage::dispatchEvent('moogento_courierrules_config_connectors', array('config' => $config));

        $connectors = $config->getConnectors();

        usort($connectors, array($this, 'sortConnectors'));

        return Mage::helper('core')->jsonEncode($connectors);
    }

    public function sortConnectors($a, $b)
    {
        if ($a['position'] == $b['position']) return 0;

        return $a['position'] > $b['position'] ? 1 : -1;
    }
}