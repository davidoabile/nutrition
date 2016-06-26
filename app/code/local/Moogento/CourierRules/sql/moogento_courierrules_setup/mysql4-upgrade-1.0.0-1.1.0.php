<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'courierrules';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `courierrules`  VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'courierrules_description';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `courierrules_description` VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'courierrules_processed';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `courierrules_processed` DATETIME NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'shipping_description';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `shipping_description` VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'courierrules_description';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `courierrules_description` VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/shipment_grid')}` LIKE 'shipping_description';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/shipment_grid')}` ADD COLUMN `shipping_description` VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/shipment_grid')}` LIKE 'courierrules_description';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/shipment_grid')}` ADD COLUMN `courierrules_description` VARCHAR(255) NULL DEFAULT NULL;");
}
$installer->run("
    UPDATE `{$this->getTable('sales/order_grid')}` og
        INNER JOIN `{$this->getTable('sales/order')}` o on o.entity_id = og.entity_id
        SET og.shipping_description = o.shipping_description
");

$installer->run("
    UPDATE `{$this->getTable('sales/shipment_grid')}` sg
        INNER JOIN `{$this->getTable('sales/order')}` o on o.entity_id = sg.order_id
        SET sg.shipping_description = o.shipping_description
");

$this->endSetup();