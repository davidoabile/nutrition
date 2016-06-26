<?php


class Moogento_Core_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var  Mage_Adminhtml_Block_Sales_Order_Grid */
    protected $_initialGrid = null;

    protected $_customColumns = array();

    protected $_collectionEventCalled = false;

    protected $_hiddenColumns = array();

    protected $_massactionBlockName = 'moogento_core/adminhtml_widget_grid_massaction';

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort(Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy') ? 'szy_created_at' : 'created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->_prepareCustomColumnData();
        Mage::dispatchEvent('moogento_core_order_grid_init', array('grid' => $this));

        if (Mage::helper('moogento_core')->isInstalled('Amasty_Flags')) {
            $flagCollection = Mage::getModel('amflags/flag')->getCollection();

            $columnCollection = Mage::getModel('amflags/column')->getCollection();
            $columnCollection->getSelect()->order('pos ASC');

            foreach ($columnCollection as $column) {
                if (($column->getApplyFlag()) && ($flagCollection->getSize() > 0)) {
                    $flagFilterOptions = array();
                    $columnFlags = explode(',', $column->getApplyFlag());

                    foreach ($flagCollection as $flag) {
                        if (in_array($flag->getEntityId(), $columnFlags)) {
                            $flagFilterOptions[$flag->getEntityId()] = $flag->getAlias();
                        }
                    }

                    $column = array(
                        'header'       => Mage::helper('amflags')->__($column->getAlias()),
                        'index'        => 'priority'.$column->getEntityId(),
                        'filter_index' => 'f'.$column->getEntityId().'.entity_id',
                        'width'        => '80px',
                        'align'        => 'center',
                        'sortable'     => true,
                        'renderer'     => 'amflags/adminhtml_renderer_flag',
                        'type'         => 'options',
                        'options'      => $flagFilterOptions,
                    );
                    $this->addCustomColumn($column['index'], $column);
                }
            }
        }

        if (Mage::helper('moogento_core')->isInstalled('Amasty_Perm')) {
            $column = array(
                'header'   => Mage::helper('amperm')->__('Dealer'),
                'type'     => 'options',
                'align'    => 'center',
                'index'    => 'uid',
                'options'  => Mage::helper('amperm')->getSalesPersonList(),
                'sortable' => false,
                'filter_condition_callback' => array('Amasty_Perm_Block_Adminhtml_Relation', 'dealerFilter'),
            );
            $this->addCustomColumn($column['index'], $column);
        }
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        Mage::dispatchEvent('moogento_core_order_grid_prepare_layout', array('grid' => $this));
        return $this;
    }
    
    protected function _prepareCollection()
    {
        if (!$this->_collectionEventCalled) {
            $collection = $this->_getInitialGrid()->getCollection();
            $collection->clear();
            $collection->getSelect()->reset(Zend_Db_Select::WHERE);
            $collection->getSelect()->reset(Zend_Db_Select::ORDER);
            $this->setCollection($collection);

            $this->_collectionEventCalled = true;
            Mage::dispatchEvent('moogento_core_order_grid_collection_prepare',
                array('grid' => $this, 'collection' => $collection));
            parent::_prepareCollection();
        }
        return $this;
    }

    protected function _getInitialGrid()
    {
        if (is_null($this->_initialGrid)) {
            $this->_initialGrid = $this->getLayout()->createBlock('adminhtml/sales_order_grid', 'sales_order.grid');
            $this->_initialGrid->setTemplate('');
            $this->_initialGrid->toHtml();
        }

        return $this->_initialGrid;
    }

    public function isDeleteorderRewrite()
    {
        return Mage::helper('moogento_core')->isInstalled('Raveinfosys_Deleteorder')
            && mageFindClassFile('Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid') &&
                $this->_getInitialGrid() instanceof Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');

        foreach ($this->_getInitialGrid()->getMassactionBlock()->getItems() as $itemId => $item) {
            $this->getMassactionBlock()->addItem($itemId, $item->getData());
            if ($item->getAdditionalActionBlock()) {
                $newItem = $this->getMassactionBlock()->getItem($itemId);
                $newItem->setAdditionalActionBlock($item->getAdditionalActionBlock());
            }
        }

        Mage::dispatchEvent('moogento_core_order_grid_actions', array('grid' => $this));

        return $this;
    }

    public function getFullColumnsList()
    {
        $columnsData = array();
        foreach ($this->_getInitialGrid()->getColumns() as $columnId => $column) {
            $data = $this->_prepareColumn($columnId, $column->getData());
            if (isset($data['removed']) && $data['removed']) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            $columnsData[$columnId] = $data;
        }
        foreach ($this->_customColumns as $columnId => $data) {
            $data = $this->_prepareColumn($columnId, $data);
            if (isset($data['removed']) && $data['removed']) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            $columnsData[$columnId] = $data;
        }

        if (Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')) {
            uasort($columnsData, array($this, '_sortColumns'));
        }

        return $columnsData;
    }

    protected function _prepareColumns()
    {
        $this->_columns = array();
        $columnsData = array();
        foreach ($this->_getInitialGrid()->getColumns() as $columnId => $column) {
            $data = $this->_prepareColumn($columnId, $column->getData());
            if (isset($data['removed']) && $data['removed']) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            if (isset($data['visible']) && $data['visible'] === false) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            if ($this->_isExport && isset($data['exportable']) && !$data['exportable']) continue;
            $columnsData[$columnId] = $data;
        }
        foreach ($this->_customColumns as $columnId => $data) {
            $data = $this->_prepareColumn($columnId, $data);
            if (isset($data['removed']) && $data['removed']) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            if (isset($data['visible']) && $data['visible'] === false) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            if ($this->_isExport && isset($data['exportable']) && !$data['exportable']) {
                $this->_hiddenColumns[] = $columnId;
                continue;
            }
            $columnsData[$columnId] = $data;
        }

        if (Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')) {
            uasort($columnsData, array($this, '_sortColumns'));
            foreach ($columnsData as $columnId => $data) {
                $this->addColumn($columnId, $data);
            }
        } else {
            foreach ($columnsData as $columnId => $data) {
                if (isset($data['after'])) {
                    $this->addColumnAfter($columnId, $data, $data['after']);
                } else {
                    $this->addColumn($columnId, $data);
                }
            }
        }

        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _sortColumns($a, $b) {
        $aSort = isset($a['order']) ? $a['order'] : 10000;
        $bSort = isset($b['order']) ? $b['order'] : 10000;
        if ($aSort == $bSort) {
            return 0;
        }
        return ($aSort < $bSort) ? -1 : 1;
    }

    protected function _prepareColumn($columnId, $data)
    {
        $dataObj = new Varien_Object();
        $dataObj->setData($data);
        Mage::dispatchEvent('moogento_core_order_grid_columns_prepare', array('column_id' => $columnId, 'data_object' => $dataObj, 'grid' => $this));
        return $dataObj->getData();
    }

    protected function _prepareCustomColumnData()
    {
        Mage::dispatchEvent('moogento_core_order_grid_columns', array('grid' => $this));
    }

    public function addCustomColumn($columnId, $data)
    {
        $this->_customColumns[$columnId] = $data;
        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getColumns()
    {
        $columns = parent::getColumns();
        if (Mage::helper('moogento_core')->isInstalled('Hackathon_GridControl')) {
            foreach ($columns as $columnId => $column) {
                if (in_array($columnId, $this->_hiddenColumns)) {
                    unset($columns[$columnId]);
                }
            }
        }

        return $columns;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if ($html) {
            $html .= '<style>a.disabled{opacity: 0.5}</style>';
        }
        $additional = new Varien_Object();
        Mage::dispatchEvent('moogento_core_order_grid_html_additional', array('additional' => $additional));
        if ($additional->getHtml()) {
            $html .= $additional->getHtml();
        }

        return $html;
    }
} 