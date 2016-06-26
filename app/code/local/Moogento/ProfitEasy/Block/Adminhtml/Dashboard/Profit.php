<?php

class Moogento_ProfitEasy_Block_Adminhtml_Dashboard_Profit extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('moogento/profiteasy/dashboard/profit.phtml');
    }

    public function getProfitData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('sales/order');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $start = $date->modify('-48hour')->format('Y-m-d H:00:00');
        $middle = $date->modify('+24hour')->format('Y-m-d H:00:00');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'now' => new Zend_Db_Expr('SUM(if(created_at>"' . $middle . '", profit_amount, 0))'),
            'last' => new Zend_Db_Expr('SUM(if(created_at>"' . $middle . '", 0, profit_amount))')
        ));

        $select->where('created_at >= ?', $start);

        $data = $adapter->fetchRow($select);

        $data['diff'] = $data['now'] - $data['last'];
        if ($data['last'] != 0) {
            $data['percent'] = round($data['now'] / $data['last'] * 100);
        } elseif ($data['diff'] > 0) {
            $data['percent'] = 100;
        } else {
            $data['percent'] = 0;
        }

        return $data;
    }

    public function getStoreProfit()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('sales/order');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $start = $date->modify('-24hour')->format('Y-m-d H:00:00');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'store_id',
            'profit' => new Zend_Db_Expr('SUM(profit_amount)'),
        ));

        $storeIds = explode(',', Mage::getStoreConfig('moogento_profiteasy/dashboard/split_per_store_list'));
        if (count($storeIds)) {
            $select->where('store_id in (?)', $storeIds);
        }

        $select->where('created_at >= ?', $start);
        $select->group('store_id');
        $data = $adapter->fetchAll($select);

        $result = array();
        if (count($storeIds)) {
            foreach ($storeIds as $id) {
                foreach ($data as $row) {
                    if ($row['store_id'] == $id) {
                        $result[$id] = $row['profit'];
                    }
                }
                if (!isset($result[$id])) {
                    $result[$id] = 0;
                }
            }
        } else {
            foreach (Mage::app()->getStores() as $store) {
                foreach ($data as $row) {
                    if ($row['store_id'] == $store->getId()) {
                        $result[$store->getId()] = $row['profit'];
                    }
                }
                if (!isset($result[$store->getId()])) {
                    $result[$store->getId()] = 0;
                }
            }
        }

        return $result;
    }

    protected function _getColor($percent)
    {
        if ($percent > 100) {
            return 'lightgreen';
        } else if ($percent > 75) {
            return 'darkgreen';
        } else if ($percent > 50) {
            return 'yellow';
        }

        return 'red';
    }
}
