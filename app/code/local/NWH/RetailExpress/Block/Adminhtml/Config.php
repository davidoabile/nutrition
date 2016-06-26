<?php

class NWH_RetailExpress_Block_Adminhtml_Config extends Mage_Core_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        $this->setTemplate('nwh/nwh_stocklevels.phtml');
        parent::__construct();
    }

    //Label to be shown in the tab
    public function getTabLabel() {
        return Mage::helper('core')->__('Stock Levels');
    }

    public function getTabTitle() {
        return Mage::helper('core')->__('Stock Levels');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getTabUrl() {
        return $this->getUrl('*/*/stocklevels', array('_current' => true));
    }

    public function getTabClass() {
        return 'ajax';
    }

}
