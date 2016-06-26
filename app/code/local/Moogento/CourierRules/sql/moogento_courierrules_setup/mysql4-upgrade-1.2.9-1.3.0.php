<?php
$this->startSetup();
$installer = $this;


if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'label';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` CHANGE label label MEDIUMBLOB NULL DEFAULT NULL;");
}

if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector_log')}` LIKE 'created_at';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector_log')}` CHANGE created_at created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
}

$this->endSetup();