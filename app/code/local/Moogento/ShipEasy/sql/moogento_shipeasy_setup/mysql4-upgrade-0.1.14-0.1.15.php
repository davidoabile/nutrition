<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        mysql4-upgrade-0.1.14-0.1.15.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('moogento_shipeasy_log')} (
    `id` INT(10) unsigned NOT NULL auto_increment,
    `order_id` INT(10) unsigned NOT NULL,
    `actions_serialized` TEXT DEFAULT NULL,
    `updated_at` DATETIME,
    PRIMARY KEY  (`id`),
    CONSTRAINT `FK_MOOGENTO_ORDER_LOG_ORDER_ID` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
