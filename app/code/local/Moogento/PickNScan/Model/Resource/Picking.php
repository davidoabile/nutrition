<?php

class Moogento_PickNScan_Model_Resource_Picking extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement    = false;
    protected $_useIsObjectNew       = false;

    protected function _construct()
    {
        $this->_init('moogento_pickscan/picking', 'entity_id');
    }
} 