<?php

$this->startSetup();

$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/tracking')}` LIKE 'warn_low';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/tracking')}` ADD COLUMN `warn_low`  int(11) NULL DEFAULT 5;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order')}` LIKE 'courierrules_tracking';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD COLUMN `courierrules_tracking`  varchar(255) NULL DEFAULT null;");
}

$this->endSetup();