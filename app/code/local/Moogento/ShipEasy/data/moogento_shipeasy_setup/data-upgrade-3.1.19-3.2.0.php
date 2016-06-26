<?php
$installer = $this;
$this->startSetup();

if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
    $installer->run(
        "UPDATE `{$this->getTable('sales/order_grid')}` SET mkt_order_id = NULL WHERE entity_id in (select magento_order_id from {$this->getTable('M2ePro/Order')} where magento_order_id is not null and component_mode = 'ebay');"
    );
}