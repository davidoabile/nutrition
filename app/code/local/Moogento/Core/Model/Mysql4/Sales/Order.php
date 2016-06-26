<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Order.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_Core_Model_Mysql4_Sales_Order extends Mage_Sales_Model_Mysql4_Order
{

    public function getOrderColumnValue($order, $column)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getTable('sales/order'),
            'customer_email'
        );
        $select->where(
            'entity_id = ' . $order->getId()
        );

        return $this->_getReadAdapter()->fetchOne($select);
    }

    public function massUpdateGridRow($orderId, $data)
    {
        $this->_getWriteAdapter()->update(
            $this->getTable('sales/order_grid'),
            $data,
            $this->_getWriteAdapter()->quoteInto('entity_id = ?',$orderId)
        );
    }

    public function updateGridRow($order, $column, $data)
    {
        $orderId = $order;
        if ($order instanceof Mage_Sales_Model_Order) {
            $orderId = $order->getId();
        }

        $this->_getWriteAdapter()->update(
            $this->getTable('sales/order_grid'),
            array($column => $data),
            $this->_getWriteAdapter()->quoteInto('entity_id = ?', $orderId)
        );
    }

    protected function _quoteCountryId($id)
    {
        return '\'' . trim($id) . '\'';
    }

    protected function _getStatusFilter()
    {
        $statuses = Mage::getStoreConfig('moogento_core/weight/statuses');
        if (!$statuses) {
            return false;
        }
        $statuses = explode(',', $statuses);
        return $statuses;
    }

    public function getValueColumnSe($orderId, $column){
        //$resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getTable('sales/order_grid')
        );
        $select->where(
            'entity_id = ' . $orderId
        );
        $row_data = $this->_getReadAdapter()->fetchRow($select);
        return $row_data[$column];
    }
}
