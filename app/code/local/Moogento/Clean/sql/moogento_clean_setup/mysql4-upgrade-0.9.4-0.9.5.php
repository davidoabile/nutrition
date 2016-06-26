<?php
$installer = $this;
$this->startSetup();

$visitorsTable = $this->getTable('moogento_clean/aggregates_visitors');
$installer->run("TRUNCATE TABLE {$visitorsTable}");

$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$indexName = $installer->getIdxName('moogento_clean/aggregates_visitors', array('date'));
$indexExists = false;
foreach ($write->fetchAll("SHOW INDEX FROM {$visitorsTable}") as $one) {
    if ($one['Key_name'] == $indexName) {
        $indexExists = true;
    }
}

if ($indexExists) {
    $installer->run("ALTER TABLE {$this->getTable('moogento_clean/aggregates_visitors')} DROP INDEX {$indexName}");
}

$installer->run("ALTER IGNORE TABLE {$this->getTable('moogento_clean/aggregates_visitors')} ADD UNIQUE INDEX {$installer->getIdxName('moogento_clean/aggregates_visitors', array('date'))}(date)");

$this->endSetup();