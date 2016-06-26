<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_profiteasy/costs_order')}` LIKE 'calculation_type';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_profiteasy/costs_order')}` ADD COLUMN `calculation_type` enum('fixed', 'percent') DEFAULT 'fixed'");
}

$installer->endSetup(); 