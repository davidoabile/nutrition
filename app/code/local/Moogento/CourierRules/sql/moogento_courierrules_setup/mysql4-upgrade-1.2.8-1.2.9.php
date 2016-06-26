<?php
$this->startSetup();
$installer = $this;

$installer->getConnection()->dropTable($installer->getTable('moogento_courierrules/connector_log'));
$table = $this->getConnection()
              ->newTable($this->getTable('moogento_courierrules/connector_log'))
              ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'nullable' => false,
                  'primary'  => true,
              ))
              ->addColumn('connector_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
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
            ->addColumn('status_message', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                'nullable' => true,
            ))
            ->addColumn('consignment', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                'nullable' => true,
            ))
              ->addColumn('response', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                  'nullable' => true,
              ))
            ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable' => false,
                'default' => new Zend_Db_Expr('CURRENT_TIMESTAMP'),
            ), 'Creation date')
              ->addIndex($installer->getIdxName('moogento_courierrules/connector_log', array('status')),
                  array('status'));

$this->getConnection()->createTable($table);
if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'connector_id';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` DROP COLUMN connector_id;");
}

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'status_message';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN status_message VARCHAR(255) NULL DEFAULT NULL;");
}
if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'consignment';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD COLUMN consignment VARCHAR(255) NULL DEFAULT NULL;");
}
if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'response';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` DROP COLUMN response;");
}

if($installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/connector')}` LIKE 'status';")) {
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/connector')}` CHANGE status status VARCHAR(255) NULL DEFAULT 'QUEUE';");
}

//$installer->run("ALTER IGNORE TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD PRIMARY KEY {$installer->getIdxName('moogento_courierrules/connector', array('shipment_id'))}(shipment_id);");

$this->endSetup();