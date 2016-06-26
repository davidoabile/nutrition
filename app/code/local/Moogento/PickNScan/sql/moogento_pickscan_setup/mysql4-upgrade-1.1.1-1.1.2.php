<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `box`  varchar(255) NULL DEFAULT NULL;");
$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `trolley_id`  varchar(255) NULL DEFAULT NULL;");

$installer->endSetup();