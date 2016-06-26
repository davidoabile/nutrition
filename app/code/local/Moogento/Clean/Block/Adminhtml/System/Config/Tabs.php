<?php
class Moogento_Clean_Block_Adminhtml_System_Config_Tabs extends Mage_Adminhtml_Block_System_Config_Tabs
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $head = $this->getLayout()->getBlock('head');
        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/jquery.cookie.js');
        $head->addJs('moogento/clean/adminhtml/config.js');
        $head->addCss('config.css');
    }
}
