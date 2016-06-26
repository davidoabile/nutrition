<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `packstation`  varchar(255) NULL DEFAULT NULL;");

$installer->endSetup();