<?php

class Moogento_Clean_Block_Adminhtml_Dashboard_Tab_Dailysells extends Mage_Adminhtml_Block_Dashboard_Graph
{
    /**
     * Initialize object
     *
     * @return void
     */
    public function __construct()
    {
        $this->setHtmlId('year_sells');
        parent::__construct();
        $this->setTemplate('moogento/clean/dashboard/graph/dailysells.phtml');
    }

    protected function _prepareData()
    {
        $daysArray = array(
            1 => $this->__("Sunday"),
            2 => $this->__("Monday"),
            3 => $this->__("Tuesday"),
            4 => $this->__("Wednesday"),
            5 => $this->__("Thursday"),
            6 => $this->__("Friday"),
            7 => $this->__("Saturday"),
        );

        $this->setLabelsData(json_encode(array_values($daysArray)));
        
        $arrayDaily = array();
        foreach($daysArray as $index => $day){
            $arrayDaily[$index] = 0;
        }

        $winnerIndex = 0;
        $winner = 0;
        foreach ($this->_getDayData() as $row){
            $arrayDaily[$row['day']] = round($row['avg'], 2);
            if ($winner < $row['avg']) {
                $winner = $row['avg'];
                $winnerIndex = $row['day'];
            }
        }
        if (isset($daysArray[$winnerIndex])) {
            $this->setWinner($daysArray[ $winnerIndex ]);
        }
        $this->setDailyData(json_encode(array_values($arrayDaily)));

        $hours = array();
        for ($i = 0; $i < 24; $i++) {
            $hours[] = $i;
        }

        $this->setHourlyLabelsData(json_encode($hours));

        $hourlySells = $this->_getHourData();
        $hourly = array();
        $winner = 0;
        $winnerValue = 0;
        foreach($hours as $hour){
            $hourly[$hour] = 0;
        }

        foreach ($hourlySells as $row) {

            $hourly[ $row['hour'] ] = round( $row['avg'], 2);
            if ($winnerValue < $row['avg']) {
                $winnerValue = $row['avg'];
                $winner      = $row['hour'];
            }
        }

		$winner = date("g:i a", strtotime($winner.':00'));

        $this->setHourWinner($winner);
        $this->setHourlyData(json_encode($hourly));

    }

    protected function _getDayData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'day' => new Zend_Db_Expr('DAYOFWEEK(date)'),
            'avg' => new Zend_Db_Expr('AVG(orders_total)'),
        ));

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->modify('-365day');

        $select->where('date >= ?', $date->format('Y-m-d H:i:s'));
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $select->group('DAYOFWEEK(date)');

        return $adapter->fetchAll($select);
    }

    protected function _getHourData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'hour' => new Zend_Db_Expr('HOUR(date)'),
            'avg' => new Zend_Db_Expr('AVG(orders_total)'),
        ));

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->modify('-365day');

        $select->where('date >= ?', $date->format('Y-m-d H:i:s'));
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $select->group('HOUR(date)');

        return $adapter->fetchAll($select);
    }

    protected function _hasDirtyData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');
        $aggregatesDayTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');

        $query = "SELECT max(is_dirty) FROM (SELECT max(is_dirty) is_dirty FROM $aggregatesTable UNION SELECT max(is_dirty) is_dirty FROM $aggregatesDayTable) t";

        return $adapter->fetchOne($query);
    }
}

