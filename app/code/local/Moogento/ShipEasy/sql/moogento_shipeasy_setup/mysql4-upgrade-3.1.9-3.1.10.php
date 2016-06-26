<?php 

Mage::log('shipEasy installation start 3.1.9-3.1.10 : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');

$installer = $this;
$this->startSetup();

if (!$installer->columnExists('sales/order', 'mkt_order_id')) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'mkt_order_id', 'VARCHAR(255) NULL DEFAULT NULL');
}
if (!$installer->columnExists('sales/order_grid', 'mkt_order_id')) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order_grid'), 'mkt_order_id', 'VARCHAR(255) NULL DEFAULT NULL');
}

$this->endSetup();
