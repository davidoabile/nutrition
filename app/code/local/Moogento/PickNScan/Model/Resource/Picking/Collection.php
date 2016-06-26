<?php

class Moogento_PickNScan_Model_Resource_Picking_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_pickscan/picking');
    }
} 