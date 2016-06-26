<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'shipping_cost';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `shipping_cost` DECIMAL( 12, 4 ) NULL DEFAULT NULL;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('sales/order_grid')}` LIKE 'base_shipping_amount';")){
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD COLUMN `base_shipping_amount` DECIMAL( 12, 4 ) NULL DEFAULT NULL;");
}
$select = $this->getConnection()->select();
$select->join(
    array('order_table' => $this->getTable('sales/order')),
    'order_table.entity_id = order_grid.entity_id',
    array(
        'shipping_cost' => 'shipping_cost',
        'base_shipping_amount' => 'base_shipping_amount',
    )
);

$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$installer->endSetup();
 