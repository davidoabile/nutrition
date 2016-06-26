<?php


class Moogento_ProfitEasy_Model_Resource_Costs extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_serializableFields   = array(
        'store_costs' => array(
            array(),
            array(),
            true
        ),
        'month' => array(
            array(),
            array(),
            true
        ),
    );

    protected function _construct()
    {
        $this->_init('moogento_profiteasy/costs', 'id');
    }
} 