<?php

$installer = $this;

$installer->startSetup();
$installer->run("
 ALTER TABLE {$this->getTable('ordersexporttool_profiles')} 
    ADD `file_mail_enabled` INT(1) DEFAULT '0',
    ADD `file_local_enabled` INT(1) DEFAULT '1',
    ADD `file_mail_subject` VARCHAR(200),
    ADD `file_mail_recipients` VARCHAR(300),
    ADD `file_mail_message` TEXT,
    ADD `file_mail_zip` INT(1) DEFAULT '0',
    ADD `file_mail_one_report` INT(1) DEFAULT '0',
    ADD `file_product_filter` INT(1) DEFAULT '0'

 ");

$installer->run("
 ALTER TABLE {$this->getTable('sales_flat_order')} 
    CHANGE export_flag export_flag TEXT;
 ");

$installer->run("
 ALTER TABLE {$this->getTable('sales_flat_order_grid')} 
    CHANGE export_flag export_flag TEXT;
 ");


$installer->endSetup();
