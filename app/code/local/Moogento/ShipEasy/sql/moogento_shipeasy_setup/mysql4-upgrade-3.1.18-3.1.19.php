<?php
$installer = $this;
$this->startSetup();

if (!$installer->columnExists('sales/order', 'preshipment_tracking')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `preshipment_tracking` varchar(255) NULL DEFAULT NULL;");
}
if (!$installer->columnExists('sales/order_grid', 'preshipment_tracking')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `preshipment_tracking` varchar(255) NULL DEFAULT NULL;");
}

$this->endSetup();
