<?php


class Moogento_CourierRules_Block_Adminhtml_Sales_Order_Connector_Log_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_filterVisibility = false;
    protected $_pagerVisibility = false;

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct();
//        $this->setId('sales_shipment_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('moogento_courierrules/connector_log_collection');
        $collection->addFieldToFilter('connector_id', $this->getConnectorId());
        $collection->setPageSize(1000);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header' => $this->__('Date'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'sortable' => false,
        ));

        $this->addColumn('type', array(
            'header' => $this->__('Connector'),
            'index'  => 'type',
            'type'   => 'text',
            'sortable' => false,
        ));
        $this->addColumn('request_method', array(
            'header' => $this->__('Request method'),
            'index'  => 'request_method',
            'type'   => 'text',
            'sortable' => false,
        ));
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index'  => 'status',
            'type'   => 'text',
            'sortable' => false,
        ));
        $this->addColumn('status_message', array(
            'header' => $this->__('Status Message'),
            'index'  => 'status_message',
            'type'   => 'text',
            'sortable' => false,
        ));
        $this->addColumn('consignment', array(
            'header' => $this->__('Consignment'),
            'index'  => 'consignment',
            'type'   => 'text',
            'sortable' => false,
        ));

        return parent::_prepareColumns();
    }


} 