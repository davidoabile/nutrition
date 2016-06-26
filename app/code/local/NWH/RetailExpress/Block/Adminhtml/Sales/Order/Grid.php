<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_RetailExpress_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    protected function _prepareColumns() {

        $this->addColumnAfter('store_id', array(
            'header' => 'Outlets',
            'index' => 'store_id',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getModel('core/store')->getCollection()->toOptionHash(),
                ), 'email');

        parent::_prepareColumns();
    }

}
