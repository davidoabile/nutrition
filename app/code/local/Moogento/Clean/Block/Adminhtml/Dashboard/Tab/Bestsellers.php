<?php

class Moogento_Clean_Block_Adminhtml_Dashboard_Tab_Bestsellers extends Mage_Adminhtml_Block_Dashboard_Graph
{

    public function __construct()
    {
        $this->setHtmlId('best_sellers');
        parent::__construct();
        $this->setTemplate('moogento/clean/dashboard/graph/bestsellers.phtml');
    }

    protected function _prepareData()
    {
        $colorsArray = array(
            // 1 => array("color" => "#006400", "highlight" =>"#228B22"),
            0 => array("color" => "#FF9136", "highlight" =>"#DD853C"),
            1 => array("color" => "#46BFBD", "highlight" =>"#DD853C"),
            2 => array("color" => "#444", "highlight" =>"#FFFFE0"),
            3 => array("color" => "#D02090", "highlight" =>"#FF00FF"),
            4 => array("color" => "#9400D3", "highlight" =>"#BA55D3"),
            5 => array("color" => "#CDC5BF", "highlight" =>"#EEE5DE"),
            6 => array("color" => "#FFFACD", "highlight" =>"#EEE9BF"),
            7 => array("color" => "#00B2EE", "highlight" =>"#00BFFF"),
            8 => array("color" => "#00E5EE", "highlight" =>"#00F5FF"),
            9 => array("color" => "#00CD00", "highlight" =>"#00FF00"),
            10 => array("color" => "#FFD700", "highlight" =>"#EEC900"),
        );

        foreach ($this->_getPeriods() as $period) {
            $result[$period] = array();
            foreach ($this->_getDataForPeriod($period) as $index => $bestseller) {
                $result[$period][] = array(
                    "value"     => round($bestseller["sum"], 2),
                    "color"     => $colorsArray[ $index ]['color'],
                    "highlight" => $colorsArray[ $index ]['highlight'],
                    "label"     => $bestseller["sku"]
                );
            }
        }

        $this->setDiagrammData($result);
    }

    protected function _getPeriods()
    {
        return array(
            Moogento_Clean_Model_Dashboard_Period::DAY,
            Moogento_Clean_Model_Dashboard_Period::WEEK,
            Moogento_Clean_Model_Dashboard_Period::MONTH,
            Moogento_Clean_Model_Dashboard_Period::YEAR,
            Moogento_Clean_Model_Dashboard_Period::ALL,
        );
    }

    protected function _getDataForPeriod($period)
    {
        $start = $this->_getStartDate($period);

        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_bestsellers');

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, array(
            'sum' => new Zend_Db_Expr('SUM(amount)'),
            'sku'
        ));
        $select->where('amount is not null and amount > 0');
        if ($start) {
            $select->where('date >= ?', $start);
        }
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        $select->order('sum');
        $select->group('sku');
        $select->limit(null, 10);

        $othersQuery = "SELECT SUM(sum) sum, 'another' sku FROM (" . $select . ") t";
        $select->limit(10);

        return array_merge($adapter->fetchAll($select), $adapter->fetchAll($othersQuery));
    }

    protected function _getStartDate($period)
    {
        if ($period == Moogento_Clean_Model_Dashboard_Period::ALL) return false;

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        switch ($period) {
            case Moogento_Clean_Model_Dashboard_Period::DAY:
                $date->modify('-24hour');
                break;
            default:
                $date->modify('-' . $period . 'ay');
        }

        return $date->format('Y-m-d H:i:s');
    }

    protected function _hasDirtyData()
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_bestsellers');

        $query = "SELECT max(is_dirty) FROM $aggregatesTable";

        return $adapter->fetchOne($query);
    }
}

