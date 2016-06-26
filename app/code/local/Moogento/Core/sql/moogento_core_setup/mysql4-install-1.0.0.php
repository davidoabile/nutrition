<?php

$this->startSetup();
$installer = $this;
$installer->run("
DROP TABLE IF EXISTS `".$this->getTable('moogento_core/country_template')."`;
");


$query = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `{$this->getTable('moogento_core/country_template')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `enable` smallint(6) DEFAULT NULL COMMENT 'Enable',
  `country_code` varchar(2) DEFAULT NULL COMMENT 'Country_code',
  `sort_number` int(11) DEFAULT NULL COMMENT 'Sort_number',
  `country_template` text COMMENT 'Country_template',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='mage_moogento_core_country_template' AUTO_INCREMENT=1 ;
HEREDOC;
$installer->run($query);

$this->endSetup();