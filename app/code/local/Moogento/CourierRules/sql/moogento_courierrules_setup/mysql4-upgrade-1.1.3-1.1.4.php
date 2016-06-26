<?php

$installer = $this;

$this->startSetup();

if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'shipping_zone';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` CHANGE shipping_zone shipping_zone INT(11) NULL DEFAULT NULL;");
}

$installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD FOREIGN KEY rule_shipping_zone(shipping_zone) REFERENCES `{$this->getTable('moogento_courierrules/zone')}`(id) ON UPDATE SET NULL ON DELETE SET NULL;");

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'custom_shipping_method';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `custom_shipping_method`  varchar(255) NULL DEFAULT NULL;");
}

$this->endSetup();