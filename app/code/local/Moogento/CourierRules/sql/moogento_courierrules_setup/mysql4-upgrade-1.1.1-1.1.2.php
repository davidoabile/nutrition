<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'min_amount';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `min_amount`  float(10,2) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'max_amount';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `max_amount`  float(10,2) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'product_attribute';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `product_attribute`  varchar(255) NULL DEFAULT NULL;");
}

$this->endSetup();