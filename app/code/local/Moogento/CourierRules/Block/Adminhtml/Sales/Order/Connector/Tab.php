<?php


class Moogento_CourierRules_Block_Adminhtml_Sales_Order_Connector_Tab
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_template = 'moogento/courierrules/order/connector/tab.phtml';
    protected $_collection = null;

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Shipping connections data');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Shipping connections data');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return strpos($this->getOrder()->getCourierrules(), 'connect:') !== false;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return strpos($this->getOrder()->getCourierrules(), 'connect:') === false;
    }

    protected function _getConnectorsCollection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('moogento_courierrules/connector')->getCollection();
            $this->_collection->getSelect()->join(
                array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
                'main_table.shipment_id = shipment.entity_id',
                array('shipment.increment_id')
            );
            $this->_collection->getSelect()->where('shipment.order_id = ?', $this->getOrder()->getId());
        }

        return $this->_collection;
    }
}