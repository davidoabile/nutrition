<?php
$this->startSetup();
$installer = $this;

$installer->getConnection()->dropTable($installer->getTable('moogento_courierrules/connector_manifest'));
$table = $this->getConnection()
              ->newTable($this->getTable('moogento_courierrules/connector_manifest'))
              ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'nullable' => false,
                  'primary'  => true,
              ))
              ->addColumn('connector', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
              ->addColumn('carrier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
              ->addColumn('file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
                ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
                    'nullable' => true,
                ), 'Date')
              ->addColumn('printed', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                  'nullable' => true,
                  'default' => 0,
              ))
                ->addColumn('returned', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                    'nullable' => true,
                    'default' => 0,
                ))

              ->addIndex($installer->getIdxName('moogento_courierrules/connector_manifest', array('connector')),
                  array('connector'));

$this->getConnection()->createTable($table);