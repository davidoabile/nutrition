<?php


class Moogento_CourierRules_Block_Adminhtml_Connector_Suggestion extends Mage_Adminhtml_Block_Template
{
    protected function _getCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/connector_suggestion_collection');
        $collection->getSelect()->order('order_id desc');

        $orderId = Mage::app()->getRequest()->getParam('order_id');
        if ($orderId) {
            $collection->addFieldToFilter('order_id', $orderId);
        }

        return $collection;
    }
} 