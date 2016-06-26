<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector_log')}` LIKE 'request_method';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector_log')}` ADD COLUMN request_method varchar(255) NULL DEFAULT NULL;");
}

$this->endSetup();