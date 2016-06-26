<?php


class Moogento_Core_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'moogento_core';

    public function __construct()
    {
        $this->_controller = 'adminhtml_sales_order';
        $this->_headerText = Mage::helper('sales')->__('Orders');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
        Mage::dispatchEvent('moogento_core_sales_order_prepare', array('block' => $this));
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
        }
        if (Mage::helper('moogento_core')->isInstalled('Tradewinds_Channelunity')) {
            $this->_addButton('export', array(
                    'label'   => Mage::helper('salesrule')->__('Export CU Orders'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('*/cu/export') . '\')',
                    'class'   => 'go',
                )
            );
        }
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }

    public function getHeaderHtml()
    {
        Mage::dispatchEvent('moogento_core_sales_order_prepare_header', array('block' => $this));
        return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>' . $this->getHeaderAdditionalHtml();
    }

    public function getHeaderWidth()
    {
        return 'width:70%;';
    }
} 