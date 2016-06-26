<?php
$this->startSetup();
$installer = $this;

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_retailexpress/paymentmethod')}` LIKE 'magento_payment';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_retailexpress/paymentmethod')}` ADD COLUMN `magento_payment` varchar(255) NULL DEFAULT NULL;");
}

$this->endSetup();