<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'committed';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN committed tinyint(1) DEFAULT 0;");
}

$this->endSetup();