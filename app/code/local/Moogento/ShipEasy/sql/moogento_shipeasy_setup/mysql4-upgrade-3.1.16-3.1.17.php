<?php
$installer = $this;
$this->startSetup();
//Left column: order_grid | right: order_table
$select = $this->getConnection()->select();
$select->join(
    array('order_table'=>$this->getTable('sales/order')),
    'order_table.entity_id = order_grid.entity_id',
    array('szy_shipping_method' => 'shipping_method', 'szy_shipping_description' => 'shipping_description')
);

$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$this->endSetup();
