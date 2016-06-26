<?php

class Moogento_CourierRules_Model_Resource_Tracking_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/tracking');
    }

    public function asJson()
    {
        $list = array();

        foreach ($this->getItems() as $item) {
            $list[] = $item->getData();
        }

        return Mage::helper('core')->jsonEncode($list);
    }
} 