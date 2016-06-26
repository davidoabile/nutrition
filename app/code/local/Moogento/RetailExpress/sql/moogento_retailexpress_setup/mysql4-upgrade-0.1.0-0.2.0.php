<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'retail_express_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `retail_express_id` varchar(255) NULL DEFAULT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'retail_express_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `retail_express_id` varchar(255) NULL DEFAULT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'retail_express_status';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `retail_express_status` smallint(2) DEFAULT 0;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'retail_express_status';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `retail_express_status` smallint(2) DEFAULT 0;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'retail_express_message';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `retail_express_message` varchar(255) NULL DEFAULT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'retail_express_message';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `retail_express_message` varchar(255) NULL DEFAULT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'retail_express_attempts';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `retail_express_attempts` smallint(2) DEFAULT 0;");
}

$this->endSetup();