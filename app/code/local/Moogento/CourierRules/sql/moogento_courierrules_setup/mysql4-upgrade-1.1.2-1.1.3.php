<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'courierrules_rule_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `courierrules_rule_id`  int(11) NULL DEFAULT NULL;");
}

$this->endSetup();