<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('salesrule/rule')}` LIKE 'easycoupon_bar_message';")){
    $installer->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD COLUMN easycoupon_bar_message text NULL DEFAULT null;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('salesrule/rule')}` LIKE 'easycoupon_bar_color';")){
    $installer->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD COLUMN easycoupon_bar_color varchar(10) NULL DEFAULT null;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('salesrule/rule')}` LIKE 'easycoupon_bar_background';")){
    $installer->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD COLUMN easycoupon_bar_background varchar(10) NULL DEFAULT null;");
}

$installer->endSetup();