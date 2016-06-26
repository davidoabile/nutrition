<?php

class NWH_RetailExpress_Block_Adminhtml_System_Store_Edit_Form extends Mage_Adminhtml_Block_System_Store_Edit_Form {

    protected function _prepareForm() {
        parent::_prepareForm();
        $helper = Mage::helper('nwh_retailexpress');
        $options = $helper->getCurlFastWay('/psc/listrfs?CountryCode=1&', ['redisKey' => 'fastWayRFCodes']);
        $fieldValues = ['value' => '', 'label' => ''];
        //fetch channels and warehouses from NWH API
        $WarehouseCollection = $helper->getCurl(NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/outlets')['data'];
        $channelCollection = $helper->getCurl(NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/channels')['data'];
        
        foreach ($options['result'] as $k => $v) {
            $fieldValues[] = ['value' => $v['FranchiseCode'], 'label' => $v['FranchiseName']];
        }
        $warehouseValues =  ['value' => '-1', 'label' => ''];
        foreach ($WarehouseCollection as $k => $v) {
            $warehouseValues[] = ['value' => $v['seqno'], 'label' => $v['name']];
        }
        
         $channelValues =  ['value' => '-1', 'label' => ''];
        foreach ($channelCollection as $k => $v) {
            $channelValues[] = ['value' => $v['channelid'], 'label' => $v['name']];
        }
        
        if (Mage::registry('store_type') == 'store') {
            $storeModel = Mage::registry('store_data');
            $fieldset = $this->getForm()->getElement('store_fieldset');
            $fieldset->addField('channel_id', 'select', array(
                'name' => 'store[channel_id]',
                'label' => Mage::helper('core')->__('Rex Channel ID'),
                'required' => true,
                'values' => $channelValues,
                'value' => $storeModel->getData('channel_id')
            ));
            $fieldset->addField('state', 'text', array(
                'name' => 'store[state]',
                'label' => Mage::helper('core')->__('Outlet State'),
                'required' => true,
                'value' => $storeModel->getData('state')
            ));
            $fieldset->addField('fastway_rfcode', 'select', array(
                'name' => 'store[fastway_rfcode]',
                'label' => Mage::helper('core')->__('FastWay RFCode'),
                'required' => true,
                'values' => $fieldValues,
                'value' => $storeModel->getData('fastway_rfcode')
            ));
            $fieldset->addField('fastway_colours', 'text', array(
                'name' => 'store[fastway_colours]',
                'label' => Mage::helper('core')->__('FastWay Colours'),
                'required' => false,
                'value' => $storeModel->getData('fastway_colours'),
                'notice' => 'Comma separated list of allowed colours'
            ));
            $fieldset->addField('warehouse_id', 'select', array(
                'name' => 'store[warehouse_id]',
                'label' => Mage::helper('core')->__('Rex Outlet ID'),
                'required' => false,
                'values' => $warehouseValues,
                'value' => $storeModel->getData('warehouse_id'),
                'notice' => 'Retail Express outlet ID'
            ));
        }
        return $this;
    }

}
