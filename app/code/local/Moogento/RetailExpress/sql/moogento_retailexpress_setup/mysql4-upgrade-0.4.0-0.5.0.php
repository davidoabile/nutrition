<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_retailexpress/paymentmethod')}` LIKE 'magento_payment';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_retailexpress/paymentmethod')}` ADD COLUMN `magento_payment` TEXT NULL DEFAULT NULL;");
} else {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_retailexpress/paymentmethod')}` CHANGE `magento_payment` `magento_payment` TEXT NULL DEFAULT NULL;");
}


$this->endSetup();