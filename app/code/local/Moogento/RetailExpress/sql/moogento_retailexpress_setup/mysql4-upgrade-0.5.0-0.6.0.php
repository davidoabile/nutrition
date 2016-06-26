<?php

$this->startSetup();
$installer = $this;

$query = 'SELECT entity_id, magento_payment FROM ' . $installer->getTable('moogento_retailexpress/paymentmethod') . ' WHERE magento_payment IS NOT NULL';

$methods = $installer->getConnection()->fetchAll($query);

foreach ($methods as $data) {
    $query = 'UPDATE ' . $installer->getTable('moogento_retailexpress/paymentmethod') . ' SET magento_payment = ? WHERE entity_id = ?';
    $installer->getConnection()->query($query, array(serialize(array($data['magento_payment'])), $data['entity_id']));
}


$this->endSetup();