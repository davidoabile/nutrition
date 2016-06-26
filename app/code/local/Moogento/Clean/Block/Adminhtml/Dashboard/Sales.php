<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard sales statistics bar
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Moogento_Clean_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales
{
    protected $_24HourData = null;

    protected $_todayColor = '#46BFBD';
    protected $_yesterdayColor = '#444';
    protected $_nowHighlightColor = 'lightgreen';
    protected $_lastHighlightColor = 'orange';
    protected $_donutWidth = '120';
    protected $_donutHole = '80';
    protected $_todayLabel;
    protected $_yesterdayLabel;

    protected $_currency = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_todayLabel = Mage::helper('moogento_clean')->__('Today');
        $this->_yesterdayLabel = Mage::helper('moogento_clean')->__('Yesterday');
    }

    protected function _prepareLayout()
    {
        if (Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_THEME) == 'extended') {
            $head = $this->getLayout()->getBlock('head');
            $head->addJs('moogento/clean/adminhtml/dashboard.js');
        }
    }

    public function _formatOutputDonut($label, $now, $then, $link_data, $isCurrency = false, $sign = '', $signLeft = true)
    {
        $type       = preg_replace("/[^a-z0-9]/", '', str_replace('&nbsp;', '', $label));
        $donutVar  = <<<DONUTVAR
            var donut_{$type} = new Chart(document.getElementById("{$type}_doughy").getContext("2d")).Doughnut(donutData_{$type},{
                responsive: false,
                segmentShowStroke: false,
                percentageInnerCutout: {$this->_donutHole},
                tooltipFontSize : 12
            });
DONUTVAR;

        $nowCalc = $now;
        $thenCalc = $then;
        if ($now != 0 || $then != 0) {
            if ($then == 0) {
                $nowCalc = 100;
            } else {
                $nowCalc = round($now/$then * 100);
                if ($nowCalc > 100) {
                    $nowCalc = 100;
                }
                $thenCalc = 100 - $nowCalc;
            }
        }
        $donutData = <<<DONUTDATA
            var donutData_{$type} = [
            {
                value : {$nowCalc},
                color : "{$this->_todayColor}",
                highlight: "{$this->_nowHighlightColor}",
                label: "{$this->_todayLabel}"
            },
            {
                value : {$thenCalc},
                color : "{$this->_yesterdayColor}",
                highlight: "{$this->_lastHighlightColor}",
                label: "{$this->_yesterdayLabel}"
            }
            ];
DONUTDATA;

        $result = '<div class="moo_chart_body" data-link="'. $link_data .'">'
                    . '<div class="donut-center">'
                    . '<span class="today" style="color:' . $this->_todayColor . ';">'
                        . ($signLeft ? $sign : '')
                        . ($isCurrency ? $this->_formatCurrencyValue($now) : $this->_shortenNumber($now))
                        . ($signLeft ? '' : $sign)
                    . '</span>'
                    . '<br/>'
                    . '<em>' . $this->__($label) . '</em>'
                    . '</div>'
                    . '<span class="before" style="color:' . $this->_yesterdayColor . ';">'
                        . ($signLeft ? $sign : '')
                        . ($isCurrency ? $this->_formatCurrencyValue($then) : $this->_shortenNumber($then))
                        . ($signLeft ? '' : $sign)
                  . '</span>'
                  . '<canvas id="' . $type . '_doughy" width="' . $this->_donutWidth . '"></canvas>'
                  . '<script>' . $donutData . $donutVar . '</script>'
                . '</div>'
                ;

        return $result;
    }

    public function getTotalsList()
    {
        $result = array();
        foreach (array(1,7,30,365, 'all') as $period) {
            $result = array_merge($result, $this->_getTotalsForPeriod($period));
        }

        return $result;
    }

    public function getQuartersNamesList()
    {
        $result = array();
        switch(Mage::getStoreConfig('moogento_clean/dashboard/show_annual_quarters')){
            case 'relative':
                $result = $this->getQuartersNames();
                break;
            case 'fixed':
                $result = $this->getQuartersNamesWithFixedStart();
                break;
        }        

        return $result;
    }
    
    public function getQuatersList()
    {
        $result = array();
        switch(Mage::getStoreConfig('moogento_clean/dashboard/show_annual_quarters')){
            case 'relative':
                foreach (array('Q1', 'Q2', 'Q3', 'Q4') as $period) {
                    $result = array_merge($result, $this->_getQuartersForPeriod($period));
                }
                break;
            case 'fixed':
                
                $array_of_quaters = $this->getQuatersListWithFixedStart();
                $date = new DateTime();
                $start_date = new DateTime();
                $now_date = new DateTime();
                $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
                $date->setDate($date->format('Y'), $date->format('m'), '1');
                $now_month = (int) $date->format('m');
                $start_month = (int) Mage::getStoreConfig('moogento_clean/dashboard/fixed_annual_quarters_month');
                $start_date->setDate($date->format('Y'), $start_month, '1');
                $now_date->setDate($date->format('Y'), $now_month, '1');
                $flag = false; 
                
                if($start_date > $now_date){ 
                    $flag_type = true; 
                } else {
                    $flag_type = false;
                }
                    
                foreach (array('Q1', 'Q2', 'Q3', 'Q4') as $period) {
                    if(in_array($now_month, $array_of_quaters[$period])) $flag = true;
                    $result = array_merge($result, $this->_getQuartersForPeriodWithFixedStart($period, $array_of_quaters, $flag, $flag_type));
                }
                break;
        }        

        return $result;
    }

    public function getQuatersListWithFixedStart()
    {
        $date = new DateTime();
        $start_month = (int) Mage::getStoreConfig('moogento_clean/dashboard/fixed_annual_quarters_month');
        $now_month = (int) $date->format('m')-1;
//        $start_month = 4;
        $array_of_month = array();
        for($i=0; $i<12; $i++){
            $array_of_month[$i] = $start_month + $i;
            if($array_of_month[$i]>12) 
                $array_of_month[$i] = $array_of_month[$i] - 12;
        }
        $array_of_quaters = array();
        $counter = 0;
        for($i=1; $i<=4; $i++){
            $array_of_quaters['Q'.$i] = array();
            for($j=1; $j<=3; $j++, $counter++){
                $array_of_quaters['Q'.$i][] = $array_of_month[$counter];
            }
        }
        return $array_of_quaters;
    }
    
    protected function _getQuartersForPeriodWithFixedStart($period, $array_of_quaters, $flag, $flag_type)
    {
                
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->setDate($date->format('Y'), $date->format('m'), '1');
        if($flag_type) $date->modify('-1year');
        if($flag) $date->modify('-1year');
        
        $start_period = new DateTime();
        $end_period = new DateTime();
        $now_month = $date->format('m');
        $start_month = (int) Mage::getStoreConfig('moogento_clean/dashboard/fixed_annual_quarters_month');
        
        $start_period->setDate($date->format('Y'), $start_month, 1);
        $end_period->setDate($date->format('Y'), $start_month, 1); 
        
        $razn = $array_of_quaters[$period][0] - $start_month;
        if($razn < 0) $razn += 12;
        $start_period->modify($razn.'month');
        
        $razn = $array_of_quaters[$period][2] - $start_month;
        if($razn < 0) $razn += 12;
        $end_period->modify($razn.'month');
        $end_period->modify('+1month');
        
        $end_period->modify('-1day');
        
        $start_now = $start_period->format('Y-m-d');
        $end_now = $end_period->format('Y-m-d');  
        
        $start_period->setDate($start_period->format('Y')-1, $start_period->format('m'), '1');
        $end_period->modify('+1day');        
        $end_period->setDate($end_period->format('Y')-1, $end_period->format('m'), $end_period->format('d'));
        $end_period->modify('-1day');   
        $start_pre = $start_period->format('Y-m-d');
        $end_pre = $end_period->format('Y-m-d');  
       
        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        
        $string_of_cond_now = ' DATE_FORMAT(date,"%Y-%m-%d") >="' . $start_now . '" AND DATE_FORMAT(date,"%Y-%m-%d") <="' . $end_now . '"';
        $string_of_cond_pre = ' DATE_FORMAT(date,"%Y-%m-%d") >="' . $start_pre . '" AND DATE_FORMAT(date,"%Y-%m-%d") <="' . $end_pre . '"';

        $select->from($aggregatesTable, array(
            'avg_order_totals/now/'         . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), null)) / SUM(if('.$string_of_cond_now.', ifnull(orders_number, 0), null))'),
            'avg_order_totals/last/'        . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), null)) / SUM(if('.$string_of_cond_pre.', ifnull(orders_number, 0), null))'),
            'avg_product_cost/now/'         . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_now.', ifnull(orders_average_product_price, 0), null))'),
            'avg_product_cost/last/'        . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_pre.', ifnull(orders_average_product_price, 0), null))'),
            'avg_qty_items_in_order/now/'   . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_now.', ifnull(orders_average_product_number, 0), null))'),
            'avg_qty_items_in_order/last/'  . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_pre.', ifnull(orders_average_product_number, 0), null))'),

            'avg_order_day/now/'            . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), 0)) / 90'),
            'avg_order_day/last/'           . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), 0)) / 90'),
            
            'order_totals/now/'             . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), 0))'),
            'order_totals/last/'            . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), 0))'),
            'order_qty/now/'                . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_number, 0), 0))'),
            'order_qty/last/'               . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_number, 0), 0))'),
        ));

        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

//        echo '<pre>';
//        var_dump($start_now);
//        var_dump($end_now);
//        var_dump($start_pre);
//        var_dump($end_pre);
//        echo $select;
//        echo '</pre>';

        return $adapter->fetchRow($select);
    }

    protected function getQuartersNamesWithFixedStart()
    {
        $array_of_quaters = $this->getQuatersListWithFixedStart();
        $result = array();
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->setDate($date->format('Y'), $date->format('m'), '1');
        $now_month = $date->format('m');
        $start_month = (int) Mage::getStoreConfig('moogento_clean/dashboard/fixed_annual_quarters_month');

        $start_period = new DateTime();
        $end_period = new DateTime();
        
        $start_period->setDate($date->format('Y'), $start_month, '1');
        $end_period->setDate($date->format('Y'), $now_month, '1');
        $flag = false; 

        if($start_period > $end_period){ 
            $flag_type = true; 
        } else {
            $flag_type = false;
        }
        
        foreach($array_of_quaters as $index=>$quater){
            $result[$index] = '';
            foreach($quater as $val){
                $date->setDate($date->format('Y'), $val, '1');
                $result[$index] .= $date->format('M').'/';
            }
            $result[$index] = substr($result[$index], 0, -1);
        }
        
        foreach($array_of_quaters as $index=>$quater){
            if(in_array($now_month, $quater)) $flag = true;
            
            $date = new DateTime();
            $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
            $date->setDate($date->format('Y'), $date->format('m'), '1');
            
            if($flag_type) $date->modify('-1year');
            if($flag) $date->modify('-1year');

            $start_period->setDate($date->format('Y'), $start_month, 1);
            $end_period->setDate($date->format('Y'), $start_month, 1); 

            $razn = $quater[0] - $start_month;
            if($razn < 0) $razn += 12;
            $start_period->modify($razn.'month');

            $razn = $quater[2] - $start_month;
            if($razn < 0) $razn += 12;
            $end_period->modify($razn.'month');
            $end_period->modify('+1month');

            $end_period->modify('-1day');
            
            $start_year = $start_period->format('Y');
            $end_year = $end_period->format('Y');
            
            if($start_year == $end_year){
                $result[$index] .= ' '.$end_year;
            } else {
                $result[$index] .= ' '.$start_year."-".$end_year;
            }
        }

        return $result;
    }

    public function getAverages()
    {
        $result = array();
        foreach (array(1,7,30,365, 'all') as $period) {
            $result = array_merge($result, $this->_getAveragesForPeriod($period));
        }

        return $result;
    }

    public function _shortenNumber($number)
    {
        $round = 0;
        $result = array(
            'value' => 0,
            'additional' => ''
        );

        $number = preg_replace("/[^\.0-9]/","",$number);

        if ((float) $number > 1000) {
            $number   = ($number / 1000);
            $round  = 1; // if we're talking thousands, 1 decimal place is probably good
            $result['additional'] = 'k';
        }
        $result['value'] = round($number, $round);

        return $result['value'] . $result['additional'];
    }

    protected function _formatCurrency($now, $then = false)
    {
        return $this->_formatOutput($now, $then, true);
    }

    protected function _formatOutput($now, $then = false, $isCurrency = false)
    {
		$thenText = '';
        $result = "";

        if ($then !== false) {
            if ($now > $then) {
                $result .= '<span style="color:green;">';
            } elseif ($now < $then) {
                $result .= '<span style="color:red;">';
            } else {
                $result .= "<span>";
            }

            if ($isCurrency) {
                $thenText = $this->_formatCurrencyValue($then);
            } else {
                $thenText = $this->_shortenNumber($then);
            }
        }


        if ($isCurrency) {
            $nowText = $this->_formatCurrencyValue($now);
        } else {
            $nowText = $this->_shortenNumber($now);
        }

        $result .= '<span>';
        $result .= $nowText;
        $result .= '</span>';

        if ($thenText !== '') {
            $result .= '<br /><span class="clean_dash_previous"> (';
            $result .= $thenText;
            $result .= ')</span>';
        }

        return $result;
    }

    public function getNowHour()
    {
        $now_time = new DateTime();
        $now_time->setTimestamp(Mage::getModel('core/date')->timestamp(time()));

        return date('H', $now_time->getTimestamp());;
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

    public function getOverviewDiagramTime()
    {
        $result   = array();

        foreach($this->_getTimeRange() as $date){
            $result[] = $date->format("ga");
        }

        return json_encode($result);
    }

    public function getOverviewOrderCountForGraph()
    {
        $data = $this->_get24HourData();
        return json_encode($data['qty']);
    }

    public function getOverviewOrderSumForGraph()
    {
        $data = $this->_get24HourData();
        return json_encode($data['totals']);
    }

    public function getOverviewVisitorsForGraph()
    {
        $data = $this->_get24HourData();
        return json_encode($data['visitors']);
    }

    public function getOverviewConversionForGraph()
    {
        $data = $this->_get24HourData();
        return json_encode($data['conversion']);
    }

    public function getOverviewDataForDoughnutGraph()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
        $aggregatesVisitorsTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_visitors');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $today = $date->modify('-23hour')->format('Y-m-d H:00:00');
        $yesterday = $date->modify('-24hour')->format('Y-m-d H:00:00');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'order_totals_today' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", orders_total, 0))'),
            'order_totals_yesterday' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", 0, orders_total))'),
            'order_count_today' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", orders_number, 0))'),
            'order_count_yesterday' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", 0, orders_number))'),
        ));

        $select->where('date >= ?', $yesterday);

        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $result = $adapter->fetchRow($select);

		$result['order_totals_today'] = round($result['order_totals_today'],2);
		$result['order_totals_yesterday'] = round($result['order_totals_yesterday'],2);
        $result['order_count_today'] = (int)$result['order_count_today'];
        $result['order_count_yesterday'] = (int)$result['order_count_yesterday'];

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesVisitorsTable, array(
            'visitors_today' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", visitors, 0))'),
            'visitors_yesterday' => new Zend_Db_Expr('SUM(if(date>="' . $today . '", 0, visitors))'),
        ));

        $select->where('date >= ?', $yesterday);

        $result = array_merge($result, $adapter->fetchRow($select));
        $result['visitors_today'] = (int)$result['visitors_today'];
        $result['visitors_yesterday'] = (int)$result['visitors_yesterday'];

        if ($result['order_count_today'] && $result['visitors_today']) {
            $result['conversions_today'] = round($result['order_count_today'] / $result['visitors_today'] * 100);
        } else {
            $result['conversions_today'] = 0;
        }
        if ($result['order_count_yesterday'] && $result['visitors_yesterday']) {
            $result['conversions_yesterday'] = round($result['order_count_yesterday'] / $result['visitors_yesterday'] * 100);
        } else {
            $result['conversions_yesterday'] = 0;
        }

        return json_encode($result);
    }

    protected function _get24HourData()
    {
        if (is_null($this->_24HourData)) {
            $this->_24HourData = array(
                'totals' => array(),
                'qty' => array(),
                'visitors' => array(),
                'conversion' => array(),
            );

            $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
            $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
            $aggregatesVisitorsTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_visitors');

            $date = new DateTime();
            $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
            $date->modify('-23hour');

            /** @var Varien_Db_Select $select */
            $select = $adapter->select();
            $select->from($aggregatesTable, array(
                'sum' => new Zend_Db_Expr('SUM(orders_total)'),
                'number' => new Zend_Db_Expr('SUM(orders_number)'),
                'hour' => new Zend_Db_Expr('HOUR(date)'),
            ));

            $select->where('date >= ?', $date->format('Y-m-d H:00:00'));

            if ($storeId = Mage::app()->getRequest()->getParam('store')) {
                $select->where('store_id = ?', $storeId);
            }

            $select->group('hour');

            $ordersData = $adapter->fetchAll($select);

            $ordersDataPrepared = array();
            foreach ($ordersData as $row) {
                $ordersDataPrepared[(int)$row['hour']] = $row;
            }

            /** @var Varien_Db_Select $select */
            $select = $adapter->select();
            $select->from($aggregatesVisitorsTable, array(
                'sum' => new Zend_Db_Expr('SUM(visitors)'),
                'hour' => new Zend_Db_Expr('HOUR(date)'),
            ));

            $select->where('date >= ?', $date->format('Y-m-d H:00:00'));

            $select->group('hour');

            $visitorsData = $adapter->fetchAll($select);
            $visitorsDataPrepared = array();
            foreach ($visitorsData as $row) {
                $visitorsDataPrepared[(int)$row['hour']] = $row;
            }

            foreach ($this->_getTimeRange() as $hour) {
                $hour = (int)$hour->format('H');

                $totals = 0;
                $qty = 0;
                $visitors = 0;
                $conversion = 0;
                if (isset($ordersDataPrepared[$hour])) {
                    $totals = round($ordersDataPrepared[$hour]['sum'], 2);
                    $qty = (int)$ordersDataPrepared[$hour]['number'];
                }
                if (isset($visitorsDataPrepared[$hour])) {
                    $visitors = $visitorsDataPrepared[$hour]['sum'];
                }
                if ($qty && $visitors) {
                    $conversion = round($qty / $visitors * 100);
                }
                $this->_24HourData['totals'][] = $totals;
                $this->_24HourData['qty'][] = $qty;
                $this->_24HourData['visitors'][] = $visitors;
                $this->_24HourData['conversion'][] = $conversion;
            }

        }
        return $this->_24HourData;
    }

    protected function _hasDirtyData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');
        $aggregatesDayTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');

        $query = "SELECT max(is_dirty) FROM (SELECT max(is_dirty) is_dirty FROM $aggregatesTable UNION SELECT max(is_dirty) is_dirty FROM $aggregatesDayTable) t";

        return $adapter->fetchOne($query);
    }

    protected function _getTotalsForPeriod($period)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $start = false;
        $middle = false;
        switch ($period) {
            case 1:
                $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
                $start = $date->modify('-48hour')->format('Y-m-d H:00:00');
                $middle = $date->modify('+24hour')->format('Y-m-d H:00:00');
                break;
            case 7:
                $start = $date->modify('-14day')->format('Y-m-d');
                $middle = $date->modify('+7day')->format('Y-m-d');
                break;
            case 30:
                $start = $date->modify('-60day')->format('Y-m-d');
                $middle = $date->modify('+30day')->format('Y-m-d');
                break;
            case 365:
                $start = $date->modify('-730day')->format('Y-m-d');
                $middle = $date->modify('+365day')->format('Y-m-d');
                break;
        }

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();

        if ($start && $middle) {
            $select->from($aggregatesTable, array(
                'order_totals/now/' . $period     => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", orders_revenue, 0))'),
                'order_totals/last/' . $period => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", 0, orders_revenue))'),
                'order_qty/now/' . $period      => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", orders_number, 0))'),
                'order_qty/last/' . $period  => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", 0, orders_number))'),
            ));
        } else {
            $select->from($aggregatesTable, array(
                'order_totals/now/all'     => new Zend_Db_Expr('SUM(orders_revenue)'),
                'order_qty/now/all'      => new Zend_Db_Expr('SUM(orders_number)'),
            ));
        }

        if ($start) {
            $select->where('date >= ?', $start);
        }

        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        return $adapter->fetchRow($select);
    }

    protected function _getAveragesForPeriod($period)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $start = false;
        $middle = false;
        switch ($period) {
            case 1:
                $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
                $start = $date->modify('-48hour')->format('Y-m-d H:00:00');
                $middle = $date->modify('+24hour')->format('Y-m-d H:00:00');
                break;
            case 7:
                $start = $date->modify('-14day')->format('Y-m-d');
                $middle = $date->modify('+7day')->format('Y-m-d');
                break;
            case 30:
                $start = $date->modify('-60day')->format('Y-m-d');
                $middle = $date->modify('+30day')->format('Y-m-d');
                break;
            case 365:
                $start = $date->modify('-730day')->format('Y-m-d');
                $middle = $date->modify('+365day')->format('Y-m-d');
                break;
        }
        
        /** @var Varien_Db_Select $select */
        $select = $adapter->select();



        if ($start && $middle) {
            $select->from($aggregatesTable, array(
                'avg_order_totals/now/' . $period     => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", orders_revenue, null)) / SUM(if(date>"' . $middle . '", orders_number, null))'),
                'avg_order_totals/last/' . $period => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", null, orders_revenue)) / SUM(if(date>"' . $middle . '", null, orders_number))'),
                'avg_product_cost/now/' . $period      => new Zend_Db_Expr('AVG(if(date>"' . $middle . '", orders_average_product_price, null))'),
                'avg_product_cost/last/' . $period  => new Zend_Db_Expr('AVG(if(date>"' . $middle . '", null, orders_average_product_price))'),
                'avg_qty_items_in_order/now/' . $period      => new Zend_Db_Expr('AVG(if(date>"' . $middle . '", orders_average_product_number, null))'),
                'avg_qty_items_in_order/last/' . $period  => new Zend_Db_Expr('AVG(if(date>"' . $middle . '", null, orders_average_product_number))'),

                'avg_order_day/now/' . $period     => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", orders_revenue, 0)) / ' . $period),
                'avg_order_day/last/' . $period => new Zend_Db_Expr('SUM(if(date>"' . $middle . '", 0, orders_revenue)) / ' . $period),
            ));
        } else {
            $daysOnlineSelect = 'SELECT datediff( now( ) , min( date ) ) FROM ' . $aggregatesTable;
            if ($storeId = Mage::app()->getRequest()->getParam('store')) {
                $daysOnlineSelect .= ' WHERE store_id = ' . (int)$storeId;
            }
            $select->from($aggregatesTable, array(
                'avg_order_totals/now/all'     => new Zend_Db_Expr('SUM(orders_revenue)/SUM(orders_number)'),
                'avg_product_cost/now/all'     => new Zend_Db_Expr('AVG(orders_average_product_price)'),
                'avg_qty_items_in_order/now/all' => new Zend_Db_Expr('AVG(orders_average_product_number)'),
                'avg_order_day/now/all'     => new Zend_Db_Expr('SUM(orders_revenue) / (' . $daysOnlineSelect . ')'),
            ));
        }

        if ($start) {
            $select->where('date >= ?', $start);
        }

        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        return $adapter->fetchRow($select);
    }
    
    protected function _getQuartersForPeriod($period)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $now_period = new DateTime();
        $prev_period = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->setDate($date->format('Y'), $date->format('m'), '1');
 
        $array_of_period = array('Q1'=>1,'Q2'=>2,'Q3'=>3,'Q4'=>4);

        $date = $date->modify('-'.(3*$array_of_period[$period]).'month');

        $now_period->setDate($date->format('Y'), $date->format('m'), '1');
        $start_now = $now_period->format('Y-m-d');
        $prev_period->setDate($now_period->format('Y')-1, $now_period->format('m'), '1');
        $start_pre = $prev_period->format('Y-m-d');

        $now_period->modify('+3month')->modify('-1day');
        $end_now = $now_period->format('Y-m-d');  
        $prev_period->modify('+3month')->modify('-1day');
        $end_pre = $prev_period->format('Y-m-d');  
       
        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        
        $string_of_cond_now = 'date>="' . $start_now . '" AND date<="' . $end_now . '"';
        $string_of_cond_pre = 'date>="' . $start_pre . '" AND date<="' . $end_pre . '"';

        $select->from($aggregatesTable, array(
            'avg_order_totals/now/'         . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), null)) / SUM(if('.$string_of_cond_now.', ifnull(orders_number, 0), null))'),
            'avg_order_totals/last/'        . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), null)) / SUM(if('.$string_of_cond_pre.', ifnull(orders_number, 0), null))'),
            'avg_product_cost/now/'         . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_now.', ifnull(orders_average_product_price, 0), null))'),
            'avg_product_cost/last/'        . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_pre.', ifnull(orders_average_product_price, 0), null))'),
            'avg_qty_items_in_order/now/'   . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_now.', ifnull(orders_average_product_number, 0), null))'),
            'avg_qty_items_in_order/last/'  . $period => new Zend_Db_Expr('AVG(if('.$string_of_cond_pre.', ifnull(orders_average_product_number, 0), null))'),

            'avg_order_day/now/'            . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), 0)) / 90'),
            'avg_order_day/last/'           . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), 0)) / 90'),
            
            'order_totals/now/'             . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_revenue, 0), 0))'),
            'order_totals/last/'            . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_revenue, 0), 0))'),
            'order_qty/now/'                . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_now.', ifnull(orders_number, 0), 0))'),
            'order_qty/last/'               . $period => new Zend_Db_Expr('SUM(if('.$string_of_cond_pre.', ifnull(orders_number, 0), 0))'),
        ));

        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }
//
//        echo '<pre>';
//        var_dump($start_now);
//        var_dump($end_now);
//        var_dump($start_pre);
//        var_dump($end_pre);
//        echo $select;
//        echo '</pre>';
        
        return $adapter->fetchRow($select);
    }

    protected function getQuartersNames()
    {
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->setDate($date->format('Y'), $date->format('m'), '1');
 
        $result = array();
        for($i=1;$i<=4;$i++){
            $date_val = new DateTime();
            $date_val->setDate($date->format('Y'), $date->format('m'), '1');
            $date_val = $date_val->modify('-'.(3*$i).'month');
            $result['Q'.$i] = "";
            for($j=1;$j<=3;$j++){
                $result['Q'.$i] .= $date_val->format('M').'/';
                if($j == 1) $start_year = $date_val->format('Y');
                if($j == 3) $end_year = $date_val->format('Y');
                $date_val->modify('+1month');
            }
            if($start_year == $end_year) {
                $year = $start_year;
            } else {
                $year = $end_year.'-'.$start_year;
            }
            $result['Q'.$i] = substr($result['Q'.$i], 0, -1)." ".$year;
        }
        return $result;
    }

    protected function _getCurrencySymbol()
    {
        return $this->_getCurrency()->getSymbol();
    }

    protected function _formatCurrencyValue($value)
    {
        $precision = 0;
        $addK = false;
        $value = (float) $value;

        if ((float) $value > 1000) {
            $value   = ($value / 1000);
            $precision = 1; // if we're talking thousands, 1 decimal place is probably good
            $addK = true;
        }

        $result = $this->_getCurrency()->toCurrency($value, array(
            'precision' => $precision,
            'position' => Zend_Currency::LEFT
        ));
        if ($addK) {
            $result .= 'k';
        }

        return $result;
    }

    protected function _getCurrency()
    {
        if (is_null($this->_currency)) {
            $this->_currency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode());
        }
        return $this->_currency;
    }
}
