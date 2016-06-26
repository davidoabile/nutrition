<?php 

class Moogento_Clean_Model_Notification extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('moogento_clean/notification');
    }
}
