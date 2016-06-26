<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('salesrule/rule')}` LIKE 'easycoupon_bar_image';")){
    $installer->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD COLUMN easycoupon_bar_image varchar(255) NULL DEFAULT null;");
}

$installer->endSetup();