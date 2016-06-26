<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'active';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `active`  tinyint(1) NOT NULL DEFAULT 1;");
}

$this->endSetup();