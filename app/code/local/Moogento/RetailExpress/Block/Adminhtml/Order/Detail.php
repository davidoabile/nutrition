<?php


class Moogento_RetailExpress_Block_Adminhtml_Order_Detail extends Mage_Adminhtml_Block_Template
{
    protected $_template = 'moogento/retailexpress/order/detail.phtml';

    protected function _getRetailStatus()
    {
        $statuses = Moogento_RetailExpress_Model_Retailexpress_Status::toOptionArray();
        if (isset($statuses[$this->getOrder()->getData('retail_express_status')])) {
            return $statuses[$this->getOrder()->getData('retail_express_status')];
        }

        return '';
    }

    protected function _getRetailUrl()
    {
        return Mage::helper('moogento_retailexpress')->getRetailViewUrl($this->getOrder());
    }
} 