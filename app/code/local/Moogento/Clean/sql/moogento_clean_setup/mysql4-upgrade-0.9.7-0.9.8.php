<?php
$installer = $this;
$this->startSetup();

$installer->run("UPDATE `{$this->getTable('moogento_clean/aggregates')}` SET is_dirty = 1;");
$installer->run("UPDATE `{$this->getTable('moogento_clean/aggregates_day')}` SET is_dirty = 1;");

$this->endSetup();
