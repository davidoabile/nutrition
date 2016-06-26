<?php

$installer = $this;
$installer->startSetup();

if (!$installer->columnExists('sales/order_grid', 'szy_company')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `szy_company` VARCHAR(255) NULL DEFAULT NULL;");
    $installer->run(
        "UPDATE `{$this->getTable('sales/order_grid')}` as grid, `{$this->getTable('sales/order_address')}` as address  SET grid.`szy_company` = address.`company`
        WHERE (grid.`entity_id` = address.`parent_id`) and (address.`address_type` = 'billing');"
    );
}
$installer->endSetup();
