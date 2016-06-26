<?php
$installer = $this;
$this->startSetup();

$config = new Mage_Core_Model_Config();

$configData = Mage::getStoreConfig('moogento_shipeasy/grid');

$config->saveConfig('moogento_shipeasy/grid/paid_non_invoiced_amounts', Mage::getStoreConfig('moogento_shipeasy/grid/non_invoiced_amounts'));
$config->saveConfig('moogento_shipeasy/grid/szy_status_status_group', Mage::getStoreConfig('moogento_shipeasy/grid/status_status_group'));

$config->saveConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside', Mage::getStoreConfig('moogento_shipeasy/grid/custom_product_attribute_inside'));
$config->saveConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside', Mage::getStoreConfig('moogento_shipeasy/grid/custom_product_attribute_inside2'));

if (Mage::getStoreConfig('moogento_shipeasy/grid/image_max_number')) {
    $config->saveConfig('moogento_shipeasy/grid/product_image_max_number',
        Mage::getStoreConfig('moogento_shipeasy/grid/image_max_number'));
}
$config->saveConfig('moogento_shipeasy/grid/product_image_show_product_name', Mage::getStoreConfig('moogento_shipeasy/grid/show_product_name'));
$config->saveConfig('moogento_shipeasy/grid/product_image_name_max_number', Mage::getStoreConfig('moogento_shipeasy/grid/name_max_number'));

$this->endSetup();