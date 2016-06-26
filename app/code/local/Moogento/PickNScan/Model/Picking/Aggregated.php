<?php

class Moogento_PickNScan_Model_Picking_Aggregated extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_pickscan/picking_aggregated');
    }

    public function aggregate()
    {
        $this->_getResource()->aggregate();
    }
} 