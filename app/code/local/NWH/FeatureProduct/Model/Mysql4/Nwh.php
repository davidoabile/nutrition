<?php
class NWH_FeatureProduct_Model_Mysql4_Nwh extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("featureproduct/nwh", "id");
    }
}