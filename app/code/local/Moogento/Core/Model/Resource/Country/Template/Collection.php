<?php

class Moogento_Core_Model_Resource_Country_Template_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('moogento_core/country_template');
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