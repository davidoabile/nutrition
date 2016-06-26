<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'despatch_date';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN despatch_date DATE NULL DEFAULT NULL;");
}

$this->endSetup();