<?php

class Moogento_CourierRules_Model_Resource_Zone extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_serializableFields   = array(
        'countries' => array(
            array(),
            array(),
            true
        ),
        'zip_codes' => array(
            array(),
            array(),
            true
        ),
    );

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/zone', 'id');
    }

}