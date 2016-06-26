<?php

class  Moogento_Clean_Model_Resource_Notification_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('moogento_clean/notification');
    }
} 