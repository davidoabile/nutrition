<?php

$this->startSetup();
$installer = $this;
$installer->run("
DROP TABLE IF EXISTS `".$this->getTable('moogento_automation/processing_flag')."`;
");


$query = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `{$this->getTable('moogento_automation/processing_flag')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `key` varchar(100) NOT NULL,
  `reference` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_MAGE_MOOGENTO_AUTOMATION_PROCESSING_FLAG` (`key`, `reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;
$installer->run($query);

$this->endSetup();