<?php
$installer = $this;
$this->startSetup();

$config = new Mage_Core_Model_Config();

$textConfig = Mage::getStoreConfig('moogento_shipeasy/carriers/base_format');
if ($textConfig) {
    $config->saveConfig('moogento_carriers/formats/list', $textConfig);
}

if (file_exists(Mage::getBaseDir('media').DS.'moogento/shipeasy/carriers')) {
    @rename(Mage::getBaseDir('media').DS.'moogento/shipeasy/carriers', Mage::getBaseDir('media').DS.'moogento/core/carriers');
}

$this->endSetup();