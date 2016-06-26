<?php
$installer = $this;
$this->startSetup();

if (!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('admin/user')}` LIKE 'gravatar_url';")) {
    $installer->run("ALTER TABLE `{$this->getTable('admin/user')}` ADD COLUMN `gravatar_url` VARCHAR(255) NULL DEFAULT NULL;");
}

$this->endSetup();
