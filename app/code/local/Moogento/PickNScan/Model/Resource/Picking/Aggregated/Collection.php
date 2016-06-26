<?php

class Moogento_PickNScan_Model_Resource_Picking_Aggregated_Collection extends Mage_Reports_Model_Resource_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_splitPerUser = false;

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('moogento_pickscan/picking_aggregated');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _applyCustomFilter()
    {
        return $this;
    }

    public function setSplitPerUser($flag)
    {
        $this->_splitPerUser = $flag;
    }

    protected function _getSelectedColumns()
    {
        $adapter = $this->getConnection();
        switch ($this->_period) {
            case 'month':
                $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
                break;
            case 'year':
                $this->_periodFormat = $adapter->getDateExtractSql('period', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
                break;
            case 'week':
                $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%u');
                break;
            default:
                $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        return array(
            'period'                => $this->_periodFormat,
            'user_id'               => 'user_id',
            'orders_count'          => 'SUM(orders_count)',
            'orders_count_avg'      => 'ROUND(AVG(orders_count), 2)',
            'items_count'           => 'SUM(items_count)',
            'items_count_avg'       => 'ROUND(AVG(items_count), 2)',
            'substituted_count'     => 'SUM(substituted_count)',
            'ignored_count'         => 'SUM(ignored_count)',
            'orders_per_hour'       => 'ROUND(SUM(orders_count/8), 2)',
            'items_per_hour'        => 'ROUND(SUM(items_count/8), 2)',
            'pick_time'             => 'SUM(pick_time)',
            'pick_time_day'         => 'AVG(pick_time_day)',
        );
    }

    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            if ($this->_splitPerUser) {
                $this->getSelect()->group('user_id,' . $this->_periodFormat);
            } else {
                $this->getSelect()->group($this->_periodFormat);
            }
            $this->getSelect()->order($this->_periodFormat . ' DESC');
        }
        return $this;
    }
}