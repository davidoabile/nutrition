<?php
$installer = $this;
$this->startSetup();

if (!$installer->columnExists('sales/order_item', 'ebay_item_id')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_item')}` ADD COLUMN `ebay_item_id` VARCHAR(255) NULL DEFAULT NULL;");
}

$this->endSetup();
