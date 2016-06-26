<?php


class Moogento_ProfitEasy_Model_Resource_Costs_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_profiteasy/costs');
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
            $data = $item->getData();
            if (isset($data['store_costs'])) {
                $data['store_costs'] = array_values($data['store_costs']);
            } else {
                $data['store_costs'] = array();
            }
            $list[] = $data;
        }

        return Mage::helper('core')->jsonEncode($list);
    }
} 