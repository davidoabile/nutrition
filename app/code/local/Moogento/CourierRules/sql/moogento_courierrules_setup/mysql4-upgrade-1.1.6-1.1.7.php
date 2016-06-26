<?php
$this->startSetup();
$installer = $this;
$table = $this->getConnection()
    ->newTable($this->getTable('moogento_courierrules/tracking'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ))

    ->addColumn('codes', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ));

$this->getConnection()->createTable($table);

if(!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable('moogento_courierrules/rule')}` LIKE 'tracking_id';")){
    $installer->run("ALTER TABLE `{$this->getTable('moogento_courierrules/rule')}` ADD COLUMN `tracking_id`  int(11) NULL DEFAULT NULL;");
}

$this->endSetup();