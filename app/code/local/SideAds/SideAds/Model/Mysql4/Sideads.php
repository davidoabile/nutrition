<?php
class SideAds_SideAds_Model_Mysql4_Sideads extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sideads/sideads", "id");
    }
}