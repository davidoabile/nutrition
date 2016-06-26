<?php
$this->startSetup();
$installer = $this;

$installer->getConnection()->dropTable($installer->getTable('moogento_courierrules/connector'));
$table = $this->getConnection()
              ->newTable($this->getTable('moogento_courierrules/connector'))
              ->addColumn('connector_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'nullable' => false,
                  'primary'  => true,
              ))
              ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'nullable' => false,
              ))
              ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
              ->addColumn('connector_data', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                  'nullable' => true,
              ))
              ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
              ->addColumn('response', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                  'nullable' => true,
              ))
              ->addColumn('tracking_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => true,
              ))
              ->addColumn('label', Varien_Db_Ddl_Table::TYPE_BLOB, null, array(
                  'nullable' => true,
              ))
              ->addIndex($installer->getIdxName('moogento_courierrules/connector', array('status')),
                  array('status'))
              ->addForeignKey($installer->getFkName('moogento_courierrules/connector', 'shipment_id', 'sales/shipment',
                      'entity_id'),
                  'shipment_id', $installer->getTable('sales/shipment'), 'entity_id',
                  Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$this->getConnection()->createTable($table);

$this->endSetup();