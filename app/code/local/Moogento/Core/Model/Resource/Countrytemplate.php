<?php

class Moogento_Core_Model_Resource_Countrytemplate extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('moogento_core/countrytemplate', 'id');
    }

}