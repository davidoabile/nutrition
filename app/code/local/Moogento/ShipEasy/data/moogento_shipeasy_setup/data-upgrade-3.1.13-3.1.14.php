<?php
$installer = $this;
$this->startSetup();

$config = new Mage_Core_Model_Config();

if (Mage::getStoreConfig('moogento_shipeasy/grid/custom_product_attribute_inside') == 'sku') {
    $config->saveConfig('moogento_shipeasy/grid/custom_product_attribute_inside', '');
    $config->saveConfig('moogento_shipeasy/grid/szy_custom_product_attribute_show', '1');
}

if (Mage::getStoreConfig('moogento_shipeasy/grid/custom_product_attribute_inside2') == 'sku') {
    $config->saveConfig('moogento_shipeasy/grid/custom_product_attribute_inside2', '');
    $config->saveConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_show', '1');
}

$this->endSetup();