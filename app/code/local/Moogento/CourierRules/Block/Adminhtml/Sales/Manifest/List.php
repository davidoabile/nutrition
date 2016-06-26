<?php

class Moogento_CourierRules_Block_Adminhtml_Sales_Manifest_List extends Mage_Adminhtml_Block_Template
{
    protected function _getCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/connector_manifest_collection');
        $collection->getSelect()->order('date desc');

        $connector = Mage::app()->getRequest()->getParam('connector');
        if ($connector) {
            $collection->addFieldToFilter('connector', $connector);
        }

        return $collection;
    }

    protected function _getTitle()
    {
        $connector = Mage::app()->getRequest()->getParam('connector');
        if ($connector) {
            return $this->__('Shipping manifests for %s', strtoupper($connector));
        } else {
            return $this->__('Shipping manifests');
        }
    }
} 