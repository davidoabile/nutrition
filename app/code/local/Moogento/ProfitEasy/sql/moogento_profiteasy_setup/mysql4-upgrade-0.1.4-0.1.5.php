<?php
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();


$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('moogento_profiteasy/costs')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('moogento_profiteasy/costs')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `enable` tinyint(1) unsigned DEFAULT 0,
  `label` varchar(255) NULL DEFAULT NULL,
  `charge_type` varchar(255) NULL DEFAULT NULL,
  `calculation_type` enum('fixed', 'percent') DEFAULT 'fixed',
  `cost` DECIMAL(10,2) DEFAULT 0,
  `payment` varchar(255) NULL DEFAULT NULL,
  `month` varchar(255) NULL,
  `year` INT (4) NULL,
  `store_costs` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('moogento_profiteasy/costs_order')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('moogento_profiteasy/costs_order')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) unsigned NOT NULL,
  `rule_id` int(10) unsigned NULL,
  `label` varchar(255) NULL DEFAULT NULL,
  `cost` DECIMAL(10,2) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `{$installer->getTable('moogento_profiteasy/costs_order')}`
  ADD CONSTRAINT `{$installer->getFkName('moogento_profiteasy/costs_order', 'order_id', 'sales_order', 'entity_id')}` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales/order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `{$installer->getFkName('moogento_profiteasy/costs_order', 'rule_id', 'moogento_profiteasy/costs', 'id')}` FOREIGN KEY (`rule_id`) REFERENCES `{$installer->getTable('moogento_profiteasy/costs')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();