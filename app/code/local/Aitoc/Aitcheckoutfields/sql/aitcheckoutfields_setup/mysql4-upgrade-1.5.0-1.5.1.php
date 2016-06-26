<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `aitoc_order_entity_custom`;
CREATE TABLE IF NOT EXISTS `aitoc_order_entity_custom` (
  `value_id` int(11) NOT NULL auto_increment,
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `UNQ_AITOC_ENTITY_ATTRIBUTE` (`entity_id`,`attribute_id`),
  KEY `FK_aitoc_order_entity_custom_attribute` (`attribute_id`),
  KEY `FK_aitoc_order_entity_custom` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `aitoc_order_entity_custom`
  ADD CONSTRAINT `aitoc_order_entity_custom_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `".$installer->getTable('eav_attribute')."` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_order_entity_custom_ibfk_2` FOREIGN KEY (`entity_id`) REFERENCES `".$installer->getTable('sales_flat_order')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
-- DROP TABLE IF EXISTS `aitoc_custom_attribute_description`;
CREATE TABLE IF NOT EXISTS `aitoc_custom_attribute_description` (
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  UNIQUE KEY `UNQ_AITOC_DESC_ATTRIBUTE` (`store_id`,`attribute_id`),
  KEY `FK_aitoc_attr_custom_attribute` (`attribute_id`),
  KEY `FK_aitoc_desc_custom_attribute` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `aitoc_custom_attribute_description`
  CHANGE COLUMN `attribute_id` `attribute_id` smallint(5) unsigned NOT NULL default '0',
  CHANGE COLUMN `store_id` `store_id` smallint(5) unsigned NOT NULL default '0',
  ADD CONSTRAINT `aitoc_custom_attribute_description_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `".$installer->getTable('core_store')."` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_custom_attribute_description_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `".$installer->getTable('eav_attribute')."` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- DROP TABLE IF EXISTS `aitoc_custom_attribute_need_select`;
CREATE TABLE IF NOT EXISTS `aitoc_custom_attribute_need_select` (
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  UNIQUE KEY `UNQ_AITOC_NEED_SEL_ATTRIBUTE` (`store_id`,`attribute_id`),
  KEY `FK_aitoc_need_sel_custom_attribute_attr` (`attribute_id`),
  KEY `FK_aitoc_need_sel_custom_attribute_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `aitoc_custom_attribute_need_select`
  CHANGE COLUMN `attribute_id` `attribute_id` smallint(5) unsigned NOT NULL default '0',
  CHANGE COLUMN `store_id` `store_id` smallint(5) unsigned NOT NULL default '0',
  ADD CONSTRAINT `aitoc_custom_attribute_need_select_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `".$installer->getTable('core_store')."` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_custom_attribute_need_select_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `".$installer->getTable('eav_attribute')."` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");
    $eavTypeTable = $installer->getTable('eav_entity_type');
    $typeExists = $installer->getConnection()->fetchOne("SELECT count(*) FROM `{$eavTypeTable}` WHERE `entity_type_code`='aitoc_checkout'");
    if(!$typeExists)
    {
        $data = $installer->getConnection()->insert($eavTypeTable, array('entity_type_code'=>'aitoc_checkout'));
    }

$installer->endSetup();