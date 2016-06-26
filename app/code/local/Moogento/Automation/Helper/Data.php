<?php
class Moogento_Automation_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function markProcessed($key, $reference)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $flagTable   = Mage::getSingleton('core/resource')->getTableName('moogento_automation/processing_flag');
        $insert = 'INSERT INTO ' . $flagTable . ' VALUES(NULL, "' . $key . '","' . $reference . '")';
        $write->query($insert);
    }
}
	 