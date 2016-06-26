<?php

$installer = $this;
$installer->startSetup();

if (!$installer->columnExists('sales/order_grid', 'customer_email_list')) {
    $this->getConnection()->addColumn(
        $this->getTable('sales/order_grid'),
        'customer_email_list',
        Varien_Db_Ddl_Table::TYPE_TEXT.' default NULL'
    );

    //Left column: order_grid | right: order_table
    $select = $this->getConnection()->select();
    $select = $this->getConnection()->select();
    $select->join(
        array('order_table'=>$this->getTable('sales/order')),
        'order_table.entity_id = order_grid.entity_id',
        array()
    );
    $select->joinLeft(
        array('customer_table'=>$this->getTable('customer_entity')),
        'customer_table.entity_id = order_table.customer_id',
        array('customer_email_list' => 'if(order_table.customer_email != customer_table.email, CONCAT(customer_table.email, " ", order_table.customer_email), order_table.customer_email)')
    );
    $this->getConnection()->query(
        $select->crossUpdateFromSelect(
            array('order_grid' => $this->getTable('sales/order_grid'))
        )
    );
}
$installer->endSetup();