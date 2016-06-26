<?php
$installer = $this;
$this->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/aggregates')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
    `date` date NOT NULL COMMENT 'Date',
    `store_id` smallint(6) NOT NULL COMMENT 'Store id',
    `orders_total` decimal(12,4) DEFAULT NULL COMMENT 'Orders total',
    `orders_number` decimal(12,4) DEFAULT NULL COMMENT 'Orders number',
    `orders_revenue` decimal(12,4) DEFAULT NULL COMMENT 'Orders revenue',
    `orders_tax` decimal(12,4) DEFAULT NULL COMMENT 'Orders tax',
    `orders_shipping` decimal(12,4) DEFAULT NULL COMMENT 'Orders shipping',
    `orders_qty` decimal(12,4) DEFAULT NULL COMMENT 'Orders qty',
    `orders_average_product_price` decimal(12,4) DEFAULT NULL COMMENT 'Orders average product price',
    `orders_average_product_number` decimal(12,4) DEFAULT NULL COMMENT 'Orders average product number',
    `is_dirty` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Dirty flag',
    PRIMARY KEY (`id`),
    UNIQUE KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date', 'store_id'))}` (`date`,`store_id`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date'))}` (`date`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('store_id'))}` (`store_id`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('is_dirty'))}` (`is_dirty`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_moogento_clean_aggregates';
");

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/aggregates_day')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
    `date` datetime NOT NULL COMMENT 'Date',
    `store_id` smallint(6) NOT NULL COMMENT 'Store id',
    `orders_total` decimal(12,4) DEFAULT NULL COMMENT 'Orders total',
    `orders_number` decimal(12,4) DEFAULT NULL COMMENT 'Orders number',
    `orders_revenue` decimal(12,4) DEFAULT NULL COMMENT 'Orders revenue',
    `orders_tax` decimal(12,4) DEFAULT NULL COMMENT 'Orders tax',
    `orders_shipping` decimal(12,4) DEFAULT NULL COMMENT 'Orders shipping',
    `orders_qty` decimal(12,4) DEFAULT NULL COMMENT 'Orders qty',
    `orders_average_product_price` decimal(12,4) DEFAULT NULL COMMENT 'Orders average product price',
    `orders_average_product_number` decimal(12,4) DEFAULT NULL COMMENT 'Orders average product number',
    `is_dirty` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Dirty flag',
    PRIMARY KEY (`id`),
    UNIQUE KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date', 'store_id'))}` (`date`,`store_id`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date'))}` (`date`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array( 'store_id'))}` (`store_id`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array( 'is_dirty'))}` (`is_dirty`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_moogento_clean_aggregates_day';
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('moogento_clean/aggregates_visitors')};

    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/aggregates_visitors')} (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
      `date` datetime NOT NULL COMMENT 'Date',
      `visitors` int(11) NOT NULL DEFAULT '0' COMMENT 'Visitor number',
      PRIMARY KEY (`id`),
      UNIQUE KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date'))}` (`date`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_moogento_clean_aggregates_visitors';
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('moogento_clean/aggregates_bestsellers')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_clean/aggregates_bestsellers')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
    `date` date NOT NULL COMMENT 'Date',
    `store_id` smallint(6) NOT NULL COMMENT 'Store id',
    `sku` varchar(255) DEFAULT NULL COMMENT 'Orders total',
    `amount` decimal(12,4) DEFAULT NULL COMMENT 'Orders number',
    `is_dirty` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Dirty flag',
    PRIMARY KEY (`id`),
    UNIQUE KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date','store_id','sku'))}` (`date`,`store_id`,`sku`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('date'))}` (`date`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('store_id'))}` (`store_id`),
    KEY `{$installer->getIdxName('moogento_clean/aggregates', array('is_dirty'))}` (`is_dirty`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='mage_moogento_clean_aggregates_bestsellers' ;
");

$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$indexName = $installer->getIdxName('log/url_table', array('visit_time'));
$indexExists = false;
foreach ($write->fetchAll("SHOW INDEX FROM {$this->getTable('log/url_table')}") as $one) {
    if ($one['Key_name'] == $indexName) {
        $indexExists = true;
    }
}

if (!$indexExists) {
    $installer->run("ALTER IGNORE TABLE `{$this->getTable('log/url_table')}` ADD INDEX {$indexName}(visit_time);");
}

$indexName = $installer->getIdxName('sales/order_item', array('sku'));
$indexExists = false;
foreach ($write->fetchAll("SHOW INDEX FROM {$this->getTable('sales/order_item')}") as $one) {
    if ($one['Key_name'] == $indexName) {
        $indexExists = true;
    }
}

if (!$indexExists) {
    $installer->run("ALTER IGNORE TABLE `{$this->getTable('sales/order_item')}` ADD INDEX {$indexName}(sku);");
}

$this->endSetup();