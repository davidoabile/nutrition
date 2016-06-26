<?php

class Moogento_PowerLogin_Block_Adminhtml_Permissions_User_Grid extends Mage_Adminhtml_Block_Permissions_User_Grid
{

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter('home_page', array(
            'header'    => Mage::helper('adminhtml')->__('Start Page'),
            'align'     => 'left',
            'index'     => 'home_page'
        ), 'email');
        $this->sortColumnsByOrder();

        return $this;
    }
}
