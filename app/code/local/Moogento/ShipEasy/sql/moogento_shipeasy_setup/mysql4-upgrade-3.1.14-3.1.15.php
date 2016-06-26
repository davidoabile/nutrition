<?php
$installer = $this;
$this->startSetup();

if ($installer->columnExists('sales/order', 'szy_weight')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` DROP COLUMN `szy_weight`;");
}

$this->endSetup();