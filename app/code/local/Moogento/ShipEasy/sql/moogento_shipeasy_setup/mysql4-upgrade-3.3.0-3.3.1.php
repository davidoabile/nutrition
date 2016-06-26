<?php

$installer = $this;
$installer->startSetup();

if (!$installer->columnExists('sales/order_grid', 'szy_sku_number')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_sku_number` TINYINT(2) NULL DEFAULT NULL;");

}
if (!$installer->columnExists('sales/order_grid', 'szy_qty')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_qty` DOUBLE(10,4) NULL DEFAULT NULL;");

}

if (!$installer->columnExists('sales/order_grid', 'szy_postcode')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_postcode` VARCHAR(255) NULL DEFAULT NULL;");
}

if (!$installer->columnExists('sales/order_grid', 'szy_custom_product_attribute')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_custom_product_attribute` TEXT NULL DEFAULT NULL;");
}

if (!$installer->columnExists('sales/order_grid', 'szy_custom_product_attribute2')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_custom_product_attribute2` TEXT NULL DEFAULT NULL;");
}

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('moogento_shipeasy/indexer_order')}`;
DROP TABLE IF EXISTS `{$installer->getTable('moogento_shipeasy/indexer_attributes')}`;
");


$installer->endSetup();