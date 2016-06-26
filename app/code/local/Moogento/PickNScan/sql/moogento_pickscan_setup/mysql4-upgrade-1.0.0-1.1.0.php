<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('moogento_pickscan/picking_aggregated'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
    ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Store Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'User Id')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
    ), 'Orders count')
    ->addColumn('items_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
    ), 'Items count')
    ->addColumn('substituted_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
    ), 'Substituted count')
    ->addColumn('ignored_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
    ), 'Ignored count')

    ->addIndex($installer->getIdxName('moogento_pickscan/picking_aggregated', array('period', 'store_id', 'user_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('period', 'store_id', 'user_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('moogento_pickscan/picking_aggregated', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('moogento_pickscan/picking_aggregated', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('moogento_pickscan/picking_aggregated', array('user_id')),
        array('user_id'))
    ->addForeignKey($installer->getFkName('moogento_pickscan/picking_aggregated', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Picking Aggregated Data');

$installer->getConnection()->createTable($table);

$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `items_count`  int(11) NOT NULL DEFAULT 0;");
$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `substituted_count`  int(11) NOT NULL DEFAULT 0;");
$installer->run("ALTER TABLE `{$this->getTable('moogento_pickscan/picking')}` ADD COLUMN `ignored_count`  int(11) NOT NULL DEFAULT 0;");


$installer->endSetup();
