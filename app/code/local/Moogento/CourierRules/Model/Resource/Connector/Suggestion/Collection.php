<?php


class Moogento_CourierRules_Model_Resource_Connector_Suggestion_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/connector_suggestion');
    }

} 