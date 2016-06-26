<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'cost_status';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `cost_status` DECIMAL( 12, 4 ) NOT NULL;");
}

$installer->endSetup(); 