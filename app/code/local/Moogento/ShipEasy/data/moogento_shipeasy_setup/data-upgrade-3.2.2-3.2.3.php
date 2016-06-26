<?php
$installer = $this;
$this->startSetup();

$config = new Mage_Core_Model_Config();

$textConfig = Mage::getStoreConfig('moogento_shipeasy/carriers/base_format');
if ($textConfig) {
    try {
        $textConfig = unserialize(trim($textConfig));
        if (is_array($textConfig)) {
            foreach ($textConfig as $key => $row) {
                $textConfig[$key]['link'] = str_replace('#', '#tracking#', $row['link']);
            }
        }
        $config->saveConfig('moogento_shipeasy/carriers/base_format', serialize($textConfig));
    } catch (Exception $e) {
    }
}

$this->endSetup();