<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'label_2';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN label_2 MEDIUMBLOB NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'label_3';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN label_3 MEDIUMBLOB NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'label_4';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN label_4 MEDIUMBLOB NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'label_5';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN label_5 MEDIUMBLOB NULL DEFAULT NULL;");
}

$this->endSetup();
 