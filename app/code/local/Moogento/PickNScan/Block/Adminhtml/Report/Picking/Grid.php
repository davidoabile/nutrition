<?php

class Moogento_PickNScan_Block_Adminhtml_Report_Picking_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName()
    {
        return 'moogento_pickscan/picking_aggregated_collection';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header' => Mage::helper('sales')->__('Period'),
            'index' => 'period',
            'width' => 100,
            'sortable' => false,
            'period_type' => $this->getPeriodType(),
            'renderer' => 'moogento_pickscan/adminhtml_widget_grid_column_renderer_date',
            'totals_label' => Mage::helper('moogento_pickscan')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        if ($this->getFilterData()->getSplitPerUser()) {
            $users = array();
            foreach (Mage::getModel('admin/user')->getCollection() as $user) {
                $users[ $user->getUserId() ] = $user->getFirstname() . ' ' . $user->getLastname();
            }
            $this->addColumn('user', array(
                'header'          => Mage::helper('sales')->__('User'),
                'index'           => 'user_id',
                'width'           => 100,
                'sortable'        => false,
                'type'            => 'options',
                'options'         => $users,
                'totals_label'    => '',
                'html_decorators' => array('nobr'),
            ));
        }

        $this->addColumn('orders_count', array(
            'header' => Mage::helper('moogento_pickscan')->__('Orders picked'),
            'index' => 'orders_count',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('orders_count_avg', array(
            'header' => Mage::helper('moogento_pickscan')->__('Orders picked average'),
            'index' => 'orders_count_avg',
            'type' => 'number',
            'total' => 'avg',
            'sortable' => false
        ));

        $this->addColumn('items_count', array(
            'header' => Mage::helper('moogento_pickscan')->__('Items picked'),
            'index' => 'items_count',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('items_count_avg', array(
            'header' => Mage::helper('moogento_pickscan')->__('Items picked average'),
            'index' => 'items_count_avg',
            'type' => 'number',
            'total' => 'avg',
            'sortable' => false
        ));

        $this->addColumn('orders_per_hour', array(
            'header' => Mage::helper('moogento_pickscan')->__('Orders per hour'),
            'index' => 'orders_per_hour',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('items_per_hour', array(
            'header' => Mage::helper('moogento_pickscan')->__('Items per hour'),
            'index' => 'items_per_hour',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('pick_time', array(
            'header' => Mage::helper('moogento_pickscan')->__('Pick time (net)'),
            'index' => 'pick_time',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false,
            'frame_callback' => array($this, 'doFormatTime'),
        ));
        $this->addColumn('pick_time_day', array(
            'header' => Mage::helper('moogento_pickscan')->__('Pick time per day'),
            'index' => 'pick_time_day',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false,
            'frame_callback' => array($this, 'doFormatTime'),
        ));

        $this->addColumn('substituted_count', array(
            'header' => Mage::helper('moogento_pickscan')->__('Items substituted'),
            'index' => 'substituted_count',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('ignored_count', array(
            'header' => Mage::helper('moogento_pickscan')->__('Items ignored'),
            'index' => 'ignored_count',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        return parent::_prepareColumns();
    }

    protected function _addOrderStatusFilter($collection, $filterData)
    {
        return $this;
    }

    protected function _addCustomFilter($collection, $filterData)
    {
        $collection->setSplitPerUser($filterData->getSplitPerUser());
        if ($filterData->getUserId()) {
            $collection->getSelect()->where('user_id in (' . implode(',', $filterData->getUserId()) . ')');
        }

        return $this;
    }

    public function doFormatTime($value, $row, $column, $isExport)
    {
        return date('H:i:s', $value);
    }
}