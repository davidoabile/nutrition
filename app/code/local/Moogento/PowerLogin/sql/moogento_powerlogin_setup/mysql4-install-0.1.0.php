<?php

$installer = $this;
$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('admin/user')}` LIKE 'home_page';")){
    $installer->run("ALTER TABLE `{$this->getTable('admin/user')}` ADD COLUMN `home_page`  VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('admin/role')}` LIKE 'home_page';")){
    $installer->run("ALTER TABLE `{$this->getTable('admin/role')}` ADD COLUMN `home_page`  VARCHAR(255) NULL DEFAULT NULL;");
}

$installer->endSetup();
	 