<?php

$installer = $this;

$installer->startSetup();

if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'cost_status';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` DROP COLUMN `cost_status`");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'profit_calculated';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `profit_calculated` tinyint(1) DEFAULT 0");
}

$installer->endSetup(); 