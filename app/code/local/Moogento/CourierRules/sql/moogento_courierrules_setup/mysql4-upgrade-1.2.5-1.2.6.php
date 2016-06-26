<?php

$installer = $this;

$this->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'cost_filter_min';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `cost_filter_min`  FLOAT NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'cost_filter_max';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `cost_filter_max`  FLOAT NULL DEFAULT NULL;");
}

$this->endSetup();