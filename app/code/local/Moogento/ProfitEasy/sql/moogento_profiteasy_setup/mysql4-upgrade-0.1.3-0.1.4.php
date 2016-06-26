<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'profit_calculated';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `profit_calculated` tinyint(1) DEFAULT 0");
}

$installer->endSetup(); 