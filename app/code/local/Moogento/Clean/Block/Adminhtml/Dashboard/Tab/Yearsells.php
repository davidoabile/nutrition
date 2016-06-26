<?php

class Moogento_Clean_Block_Adminhtml_Dashboard_Tab_Yearsells extends Mage_Adminhtml_Block_Dashboard_Graph
{

    public function __construct()
    {
        $this->setHtmlId('year_sells');
        parent::__construct();
        $this->setTemplate('moogento/clean/dashboard/graph/yearsells.phtml');
    }

    protected function _prepareData()
    {
        $yearSells = $this->_getYearData();
        $labels = array();
        $sums = array();
        $qtys = array();
        for($i=0; $i<12; $i++){
            $month = date("Y F", strtotime("-".$i."month"));
            $sum = 0;
            $qty = 0;
			if (is_array($yearSells))
			{
	            foreach($yearSells as $item){
	                if($month == $item["month"]){
	                    $sum = $item["sum"];
	                    $qty = $item["count"];
	                }
	            }
			}
            $labels[] = $month;
            $sums[] = round($sum, 2);
            $qtys[] = (int)$qty;
        }
        $this->setLabelsData(json_encode($labels));
        $this->setSumsData(json_encode($sums));
        $this->setQtysData(json_encode($qtys));
    }

    protected function _getYearData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'month' => new Zend_Db_Expr("CONCAT(YEAR(date), ' ', MONTHNAME(date))"),
            'sum' => new Zend_Db_Expr('SUM(orders_total)'),
            'count' => new Zend_Db_Expr('SUM(orders_number)'),
        ));

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));
        $date->modify('-365day');

        $select->where('date >= ?', $date->format('Y-m-d H:i:s'));
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $select->group('month');

        return $adapter->fetchAll($select);
    }

    protected function _hasDirtyData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $query = "SELECT max(is_dirty) is_dirty FROM $aggregatesTable";

        return $adapter->fetchOne($query);
    }
}

