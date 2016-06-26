<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `status`  int(2) NOT NULL DEFAULT 1;");

$installer->endSetup();
