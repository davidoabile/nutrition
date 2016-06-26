<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */  
$this->startSetup();
try{
    $tableName = Mage::getSingleton('core/resource')->getTableName('productalert/stock');
    $fieldsSql = 'SHOW COLUMNS FROM ' . $tableName;
    $cols = $this->getConnection()->fetchCol($fieldsSql);

    if (!in_array('store_id', $cols))
    {
        $this->run("
            ALTER TABLE `{$tableName}` ADD COLUMN `store_id` INT NULL;
        ");
    }

    $tableName = Mage::getSingleton('core/resource')->getTableName('productalert/price');
    $fieldsSql = 'SHOW COLUMNS FROM ' . $tableName;
    $cols = $this->getConnection()->fetchCol($fieldsSql);

    if (!in_array('store_id', $cols))
    {
        $this->run("
            ALTER TABLE `{$tableName}` ADD COLUMN `store_id` INT NULL;
        ");
    }
}catch(Exception $exc){
     Mage::log($exc->getMessage());
}

$this->endSetup(); 