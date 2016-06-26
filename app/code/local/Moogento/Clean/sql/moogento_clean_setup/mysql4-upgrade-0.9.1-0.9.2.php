<?php
$installer = $this;
$this->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/dashboard_report')} (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
      `data_name` varchar(255) NOT NULL COMMENT 'Data name',
      `value` text COMMENT 'Value',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_clean_dashboard_reports';
");

$this->endSetup();