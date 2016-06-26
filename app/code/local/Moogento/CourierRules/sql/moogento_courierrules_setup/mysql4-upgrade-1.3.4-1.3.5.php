<?php
$this->startSetup();
$installer = $this;

$installer->getConnection()->dropTable($installer->getTable('moogento_courierrules/connector_suggestion'));
$table = $this->getConnection()
              ->newTable($this->getTable('moogento_courierrules/connector_suggestion'))
              ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'nullable' => false,
                  'primary'  => true,
              ))
              ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                  'nullable' => true,
              ))
              ->addColumn('suggestion', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                  'nullable' => true,
              ))
              ->addIndex($installer->getIdxName('moogento_courierrules/connector_suggestion', array('order_id')),
                  array('order_id'))
              ->addForeignKey($installer->getFkName('moogento_courierrules/connector_suggestion', 'order_id',
                      'sales/order', 'entity_id'),
                  'order_id', $installer->getTable('sales/order'), 'entity_id',
                  Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$this->getConnection()->createTable($table);

$this->endSetup();