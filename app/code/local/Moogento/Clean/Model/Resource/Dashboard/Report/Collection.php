<?php

class  Moogento_Clean_Model_Resource_Dashboard_Report_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_clean/dashboard_report');
    }
} 