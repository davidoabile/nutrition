<?php

class Moogento_CourierRules_Model_Resource_Zone_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/zone');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->getItems() as $item) {
            $item->getResource()->unserializeFields($item);
        }

        return $this;
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