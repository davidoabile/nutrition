<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'shipping_cost';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `shipping_cost` DECIMAL( 12, 4 ) NULL DEFAULT NULL;");
} else {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` CHANGE `shipping_cost` `shipping_cost` DECIMAL( 12, 4 ) NULL DEFAULT NULL;");
}

$installer->run("UPDATE `{$this->getTable('sales/order')}` SET shipping_cost = NULL where shipping_cost = 0;");


$installer->endSetup(); 