<?php
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('moogento_easycoupon/shortlink')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('moogento_easycoupon/shortlink')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shortlink` VARCHAR(10) NOT NULL,
  `website` VARCHAR(50) NULL,
  `coupon` VARCHAR(50) NULL,
  `target` VARCHAR(50) NULL,
  `skus` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_MAGE_MOOGENTO_EASYCOUPON_SHORTLINK` (`shortlink`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->endSetup();