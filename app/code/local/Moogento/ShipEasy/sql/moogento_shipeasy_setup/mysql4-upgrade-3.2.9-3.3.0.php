<?php

$installer = $this;
$installer->startSetup();

if (!$installer->indexExists('sales/order_item', 'created_at')) {
    $indexName = $this->getIdxName($this->getTable('sales/order_item'), array('created_at'));
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_item')}` ADD INDEX `{$indexName}` (created_at);");
}
if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
    if (!$installer->indexExists('sales/order_item', 'ebay_item_id')) {
        $indexName = $this->getIdxName($this->getTable('sales/order_item'), array('ebay_item_id'));
        $installer->run("ALTER TABLE `{$this->getTable('sales/order_item')}` ADD INDEX `{$indexName}` (ebay_item_id);");
    }
}
$installer->endSetup();