<?php 

$installer = $this;
 
$installer->startSetup();
 
$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/notification')} (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
      `product_id` int(10) unsigned NOT NULL COMMENT 'ProductId',
      `create_at` datetime DEFAULT NULL COMMENT 'Creation Time',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_clean_out_of_stock_notifications';
");

$installer->endSetup();