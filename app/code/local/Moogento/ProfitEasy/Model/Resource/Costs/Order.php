<?php


class Moogento_ProfitEasy_Model_Resource_Costs_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_profiteasy/costs_order', 'id');
    }
} 