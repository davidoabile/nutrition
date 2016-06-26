<?php
$installer = $this;
$this->startSetup();

$data = @unserialize(Mage::getStoreConfig('moogento_profiteasy/costs/rules'));
if (is_array($data)) {
    foreach ($data as $row) {
        $model = Mage::getModel('moogento_profiteasy/costs');
        $model->addData($row);
        $model->save();
    }
}

$this->endSetup();