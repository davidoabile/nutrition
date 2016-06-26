<?php

$installer = $this;

$this->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('moogento_courierrules/cron_processing'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Id')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => true,
    ), 'Date')
    ->addColumn('mail_sent', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default' => 0,
    ), 'Date');

$this->getConnection()->createTable($table);

$this->endSetup();