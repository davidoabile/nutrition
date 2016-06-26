<?php

class Moogento_Core_Model_Resource_Country_Template extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('moogento_core/country_template', 'id');
    }

}