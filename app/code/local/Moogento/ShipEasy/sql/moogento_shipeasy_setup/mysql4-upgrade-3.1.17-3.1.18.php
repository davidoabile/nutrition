<?php
$installer = $this;
$this->startSetup();

if (!$installer->columnExists('sales/order_item', 'in_stock_at_create_moment')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_item')}` ADD COLUMN `in_stock_at_create_moment` tinyint(1) NOT NULL DEFAULT 2;");
}

$this->endSetup();
