<?php
$this->startSetup();
$this->run("DROP TABLE IF EXISTS {$this->getTable('moogento_retailexpress/paymentmethod')}");
$table = $this->getConnection()
              ->newTable($this->getTable('moogento_retailexpress/paymentmethod'))
              ->addColumn(
                  'entity_id',
                  Varien_Db_Ddl_Table::TYPE_INTEGER,
                  null,
                  array(
                      'identity' => true,
                      'nullable' => false,
                      'primary'  => true,
                  ),
                  'RetailExpress Payment method ID'
              )
              ->addColumn(
                  'name',
                  Varien_Db_Ddl_Table::TYPE_TEXT, 255,
                  array(
                      'nullable' => false,
                  ),
                  'Name'
              )
              ->addColumn(
                  'retail_express_id',
                  Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                  array(
                      'nullable' => false,
                      'unsigned' => true,
                  ),
                  'Retail express ID'
              )
              ->addColumn(
                  'loyalty_enabled',
                  Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
                  array(
                      'nullable' => false,
                  ),
                  'Loyalty Enabled'
              )
              ->addColumn(
                  'pos_enabled',
                  Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
                  array(
                      'nullable' => false,
                  ),
                  'POS enabled'
              )
              ->addColumn(
                  'loyalty_ratio',
                  Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4',
                  array(
                      'nullable' => false,
                  ),
                  'Loyalty Ratio'
              )
              ->addColumn(
                  'status',
                  Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
                  array(),
                  'Enabled'
              )
              ->addColumn(
                  'updated_at',
                  Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                  null,
                  array(),
                  'RetailExpress Payment method Modification Time'
              )
              ->addColumn(
                  'created_at',
                  Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                  null,
                  array(),
                  'RetailExpress Payment method Creation Time'
              )
              ->setComment('RetailExpress Payment method Table');
$this->getConnection()->createTable($table);
$this->endSetup();