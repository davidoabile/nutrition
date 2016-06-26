<?php

class Moogento_CourierRules_Block_Adminhtml_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $displayOriginal = Mage::getStoreConfigFlag('courierrules/settings/shipping_grid_original');

        if ($displayOriginal) {
            $this->addColumnAfter('shipping_description', array(
                'header' => Mage::helper('moogento_courierrules')->__('Shipping Method'),
                'index' => 'shipping_description',
            ), 'shipping_name');
        }


        if (Mage::getStoreConfigFlag('courierrules/settings/shipping_grid')) {
            $this->addColumnAfter('courierrules_description', array(
                'header' => Mage::helper('moogento_courierrules')->__('Courier Rules Method'),
                'index' => 'courierrules_description',
                'type'    => 'options',
                'options' => Mage::helper('moogento_courierrules')->getCourierRulesDropdownOptions(),
            ), $displayOriginal ? 'shipping_description' : 'shipping_name');
        }

        $this->sortColumnsByOrder();

        return $this;
    }
} 