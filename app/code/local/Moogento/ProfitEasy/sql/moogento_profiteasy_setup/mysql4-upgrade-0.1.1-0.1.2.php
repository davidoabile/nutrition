<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'profit_amount';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `profit_amount` DECIMAL( 12, 4 ) NOT NULL;");
}

$installer->endSetup();
 