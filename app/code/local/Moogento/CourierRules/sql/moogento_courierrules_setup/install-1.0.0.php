<?php
$this->startSetup();

$table = $this->getConnection()
    ->newTable($this->getTable('moogento_courierrules/zone'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))

    ->addColumn('countries', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))

    ->addColumn('zip_codes', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ));

$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('moogento_courierrules/rule'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))
    ->addColumn('sort', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default' => 1,
    ))
    ->addColumn('scope', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))
    ->addColumn('shipping_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))
    ->addColumn('shipping_zone', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))
    ->addColumn('min_weight', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'default' => null,
    ))
    ->addColumn('max_weight', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'default' => null,
    ))
    ->addColumn('courierrules_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))
    ->addColumn('target_custom', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))
    ;

$this->getConnection()->createTable($table);

$this->endSetup();
