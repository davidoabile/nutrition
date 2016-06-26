<?php 

class Moogento_Clean_Model_Resource_Notification extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_clean/notification', 'id');
    }
}
