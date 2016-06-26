<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking_aggregated')}` ADD COLUMN `pick_time` int(11) DEFAULT 0;");

$installer->endSetup();
