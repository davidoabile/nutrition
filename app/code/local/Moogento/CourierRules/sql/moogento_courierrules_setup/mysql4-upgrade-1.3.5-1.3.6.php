<?php
$this->startSetup();
$installer = $this;

$installer->run("ALTER IGNORE TABLE `{$this->getTable('moogento_courierrules/connector')}` ADD UNIQUE INDEX {$installer->getIdxName('moogento_courierrules/connector', array('shipment_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)}(shipment_id);");

$this->endSetup();
 