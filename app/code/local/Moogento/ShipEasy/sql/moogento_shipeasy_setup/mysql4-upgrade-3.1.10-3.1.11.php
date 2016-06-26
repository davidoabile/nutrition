<?php 

Mage::log('shipEasy installation start 3.1.9-3.1.10 : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');

$installer = $this;
$this->startSetup();

if (!$installer->columnExists('sales/order_grid', 'coupon_code')) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order_grid'), 'coupon_code', 'VARCHAR(255) NULL');
}

$installer->run(<<<SQL

    UPDATE 
        {$this->getTable('sales/order_grid')}
    INNER JOIN
        {$this->getTable('sales/order')}
    ON
        {$this->getTable('sales/order_grid')}.entity_id = {$this->getTable('sales/order')}.entity_id
    SET 
        {$this->getTable('sales/order_grid')}.coupon_code = {$this->getTable('sales/order')}.coupon_code
    
SQL
);

$this->endSetup();
