<?php


class Moogento_CourierRules_Model_Resource_Connector_Manifest extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/connector_manifest', 'id');
    }

}