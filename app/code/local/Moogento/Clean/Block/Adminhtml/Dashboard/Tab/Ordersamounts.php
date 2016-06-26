<?php

class Moogento_Clean_Block_Adminhtml_Dashboard_Tab_Ordersamounts extends Mage_Adminhtml_Block_Dashboard_Graph
{
    public function __construct()
    {
        $this->setHtmlId('orders_amounts');
        parent::__construct();
        $this->setTemplate('moogento/clean/dashboard/graph/ordersamounts.phtml');
    }

    protected function _getPeriod()
    {
        $period = $this->getRequest()->getParam('period');

        if (!$period) {
            $period = Mage::getSingleton('core/cookie')->get('clean_dashboard_period');
        } else {
            Mage::getSingleton('core/cookie')->set('clean_dashboard_period', $period, time()+86400,'/');
        }

        return $period;
    }

    protected function _getTimeRange()
    {
        $start = new DateTime();
        $start->setTimestamp(Mage::getModel('core/date')->timestamp(time()));
        $start->modify('-23hours');

        $end = new DateTime();
        $end->setTimestamp(Mage::getModel('core/date')->timestamp(time()));
        $end->modify('+1hour');

        $interval = new DateInterval('PT1H');
        return new DatePeriod($start, $interval ,$end);
    }

    protected function _prepareData()
    {
        $availablePeriods = array_keys($this->helper('moogento_clean/dashboard')->getDatePeriods());

        $period = $this->_getPeriod();
        $period = $period && in_array($period, $availablePeriods) ? $period : '24h';

        $dates = array();
        $quantityFinal = array();
        $revenueFinal = array();

        $data = $this->_getPeriodData($period);

        list ($dateStart, $dateEnd) = $this->_getDateRange($period);

        while($dateStart < $dateEnd){
            switch ($period) {
                case '24h':
                    $dKey = $dateStart->format('Y-m-d H:00');
                    $d = $dateStart->format('ga');
                    $dateStart->modify('+1hour');
                    break;
                case '7d':
                    $dKey = $dateStart->format('Y-m-d');
                    $d = $dateStart->format('D jS');
                    $dateStart->modify('+1day');
                    break;
                case '1m':
                    $dKey = $dateStart->format('Y-m-d');
                    $d = $dateStart->format('D jS');
                    $dateStart->modify('+1day');
                    break;
                case '1y':
                    $dKey = $dateStart->format('Y-m');
                    $d = $dateStart->format('M');
                    $dateStart->modify('+1month');
                    break;
                case '2y':
                    $dKey = $dateStart->format('Y-m');
                    $d = $dateStart->format("M 'y");
                    $dateStart->modify('+1month');
                    break;
                default:
                    $d = '';
                    $dKey = '';
            }
            $dates[] = $d;

            if (isset($data[$dKey])) {
                $quantityFinal[] = (int)$data[$dKey]['count'];
            } else {
                $quantityFinal[] = 0;
            }
            if (isset($data[$dKey])) {
                $revenueFinal[] = round($data[$dKey]['sum'], 2);
            } else {
                $revenueFinal[] = 0;
            }
        }

        $this->setLabelsData(json_encode($dates));
        $this->setQtysData(json_encode($quantityFinal));
        $this->setSumsData(json_encode($revenueFinal));
    }

    protected function _getPeriodData($period)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable = $period == '24h' ? Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day') : Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $columns = array(
            'sum' => new Zend_Db_Expr('SUM(orders_total)'),
            'count' => new Zend_Db_Expr('SUM(orders_number)'),
        );

        switch ($period) {
            case '24h':
                $columns['group'] = new Zend_Db_Expr('DATE_FORMAT(date, "%Y-%m-%d %H:00")');
                $date = $date->modify('-24hour')->format('Y-m-d H:i:s');
                break;
            case '7d':
                $columns['group'] = new Zend_Db_Expr('DATE_FORMAT(date, "%Y-%m-%d")');
                $date = $date->modify('-7day')->format('Y-m-d H:i:s');
                break;
            case '1m':
                $columns['group'] = new Zend_Db_Expr('DATE_FORMAT(date, "%Y-%m-%d")');
                $date = $date->format('Y-m-01 00:00:00');
                break;
            case '1y':
                $columns['group'] = new Zend_Db_Expr('DATE_FORMAT(date, "%Y-%m")');
                $date = $date->modify('-1year')->format('Y-m-d 00:00:00');
                break;
            case '2y':
                $columns['group'] = new Zend_Db_Expr('DATE_FORMAT(date, "%Y-%m")');
                $date = $date->modify('-2year')->format('Y-m-d 00:00:00');
                break;
        }

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, $columns);

        $select->where('date >= ?', $date);
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $select->group('group');

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['group']] = $row;
        }

        return $result;
    }

    protected function _hasDirtyData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $availablePeriods = array_keys($this->helper('moogento_clean/dashboard')->getDatePeriods());
        $period = $this->_getPeriod();
        $period = $period && in_array($period, $availablePeriods) ? $period : '24h';
        $aggregatesTable = $period == '24h' ? Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day') : Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        switch ($period) {
            case '24h':
                $date = $date->modify('-24hour')->format('Y-m-d H:i:s');
                break;
            case '7d':
                $date = $date->modify('-7day')->format('Y-m-d H:i:s');
                break;
            case '1m':
                $date = $date->format('Y-m-01 H:i:s');
                break;
            case '1y':
                $date = $date->modify('-1year')->format('Y-m-d H:i:s');
                break;
            case '2y':
                $date = $date->modify('-2year')->format('Y-m-d H:i:s');
                break;
        }

        $query = "SELECT max(is_dirty) is_dirty FROM $aggregatesTable WHERE date >= '" . $date . "'" ;

        return $adapter->fetchOne($query);
    }

    public function getTotalsHtml()
    {
        return $this->getLayout()->createBlock('moogento_clean/adminhtml_dashboard_totals')->toHtml();
    }

    protected function _getDateRange($range)
    {
        $dateEnd = new DateTime();
        $dateEnd->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $dateStart = clone $dateEnd;
        // go to the end of a day
        $dateEnd->setTime(23, 59, 59);

        $dateStart->setTime(0, 0 , 0);

        switch ($range)
        {
            case '24h':
                $dateEnd = new DateTime();
                $dateEnd->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

                $dateEnd->modify('+1hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1day');
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6days');
                break;

            case '1m':
                $dateStart->setDate($dateStart->format('Y'), $dateStart->format('m'), Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;
            case '1y':
                $dateStart->modify('-1year');
                break;
            case '2y':
                $dateStart->modify('-2year');
                break;
        }

        return array($dateStart, $dateEnd);
    }
}

