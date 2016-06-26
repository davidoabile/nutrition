<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'quantity_all_items';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `quantity_all_items`  INT(11) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'quantity_free_discount_items';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `quantity_free_discount_items`  INT(11) NULL DEFAULT NULL;");
}

$this->endSetup();