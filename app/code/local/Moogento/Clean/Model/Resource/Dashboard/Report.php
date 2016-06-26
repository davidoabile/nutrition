<?php 

class Moogento_Clean_Model_Resource_Dashboard_Report extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_clean/dashboard_report', 'id');
    }
}
