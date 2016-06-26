<?php

$installer = $this;
$installer->startSetup();

if (!$installer->columnExists('sales/order_grid', 'email_from_admin')) {
    $this->getConnection()->addColumn(
        $this->getTable('sales/order_grid'),
        'email_from_admin',
        Varien_Db_Ddl_Table::TYPE_TEXT.' default NULL'
    );
}
if (!$installer->columnExists('sales/order', 'email_from_admin')) {
    $this->getConnection()->addColumn(
        $this->getTable('sales/order'),
        'email_from_admin',
        Varien_Db_Ddl_Table::TYPE_TEXT.' default NULL'
    );
}
$installer->endSetup();