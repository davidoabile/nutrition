<?php

/**
 * NWH
 *
 * NOTICE OF LICENSE
 *
 * David
 
 */
/**
 * Install product link types
 */
$this->startSetup();
$this->run(" ALTER TABLE `{$this->getTable('core/store')}` " .
           " ADD `state` VARCHAR(60) NULL DEFAULT NULL ," .
           " ADD `fastway_rfcode` VARCHAR(60) NULL DEFAULT NULL," .
           " ADD `fastway_colours` VARCHAR(100) NULL DEFAULT NULL");
$this->run("ALTER TABLE `sales_flat_order` ADD `channelid` INT NOT NULL DEFAULT '0' AFTER `ebizmarts_magemonkey_campaign_id`, ADD `fastway_colour` VARCHAR(50) NULL DEFAULT NULL AFTER `channelid`");
(new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/carriers/fastway_api', '53ec71366356216ca1e462a7bc2051ac');
$this->run("CREATE TABLE IF NOT EXISTS`nwh_stock_levels` (
            `seqno` int(11) NOT NULL,
            `sku` varchar(20) CHARACTER SET latin1 NOT NULL,
            `qty` decimal(10,4) DEFAULT '0.0000',
            `show_web` char(1) CHARACTER SET latin1 NOT NULL DEFAULT 'Y',
            `lookup` char(1) CHARACTER SET latin1 NOT NULL DEFAULT 'N',
            `postcode_range` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
            `channelid` int(11) NOT NULL DEFAULT '0',
            `product_id` int(11) NOT NULL DEFAULT '0',
            `stock_on_order` decimal(10,4) NOT NULL DEFAULT '0.0000',
            `auto_update` varchar(1) CHARACTER SET latin1 NOT NULL DEFAULT 'Y',
            `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `dump` text NOT NULL,
            `store_id` int(4) NOT NULL DEFAULT '0'
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
  );
(new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/newsletter/code', '$5OFF');
(new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/sync/interval', '+5 minutes');
(new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/sync/enabled', 'Y');
$this->endSetup();
