<?php

class Moogento_EasyCoupon_Block_Adminhtml_Shortlink extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'moogento_easycoupon';
        $this->_controller = 'adminhtml_shortlink';
        $this->_headerText = Mage::helper('moogento_easycoupon')->__('Shortlinks');
        parent::__construct();
        $this->_removeButton('add');
    }
}
