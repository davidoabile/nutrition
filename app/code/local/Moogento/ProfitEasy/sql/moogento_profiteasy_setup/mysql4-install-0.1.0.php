<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'shipping_cost';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `shipping_cost` DECIMAL( 12, 4 ) NOT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'profit_amount';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `profit_amount` DECIMAL( 12, 4 ) NOT NULL;");
}

$installer->endSetup(); 