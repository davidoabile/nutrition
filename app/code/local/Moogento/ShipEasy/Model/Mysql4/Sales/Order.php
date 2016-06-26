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
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Model_Mysql4_Sales_Order extends Mage_Sales_Model_Mysql4_Order
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
            $this->_getWriteAdapter()->quoteInto('entity_id = ?', $orderId)
        );
        $cache = Mage::app()->getCache();
        $cache->clean('matchingAnyTag', array('moogento_cache'));
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

        $cache = Mage::app()->getCache();
        $cache->clean('matchingAnyTag', array('moogento_cache'));
    }

    protected function _quoteCountryId($id)
    {
        return '\'' . trim($id) . '\'';
    }

    protected function _getStatusFilter()
    {
        $statuses = Mage::getStoreConfig('moogento_shipeasy/weight/statuses');
        if (!$statuses) {
            return false;
        }
        $statuses = explode(',', $statuses);

        return $statuses;
    }

    public function getValueColumnSe($orderId, $column)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getTable('sales/order_grid')
        );
        $select->where(
            'entity_id = ' . $orderId
        );
        $row_data = $this->_getReadAdapter()->fetchRow($select);

        return $row_data[ $column ];
    }

    public function getWeightPerRegionGroup()
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            array('main_table' => $this->getTable('sales/order_grid')),
            array()
        );
        $select->columns(
            array('country_group' => new Zend_Db_Expr($this->_getCountryGroupSql()))
        );
        $select->columns(
            array('weight' => new Zend_Db_Expr('SUM(main_table.szy_weight)'))
        );
        $select->columns(
            array('order_count' => new Zend_Db_Expr('COUNT(main_table.entity_id)'))
        );

        if ($filterStatuses = $this->_getStatusFilter()) {
            $select->where(
                $this->_getReadAdapter()->quoteInto(
                    'main_table.status IN (?)', $filterStatuses
                )
            );
        }

        $select->group('country_group');

        return $this->_getReadAdapter()->fetchAssoc($select);
    }

    public function getWeightTotal()
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            array('main_table' => $this->getTable('sales/order_grid')),
            array()
        );
        $select->columns(
            array('weight' => new Zend_Db_Expr('SUM(main_table.szy_weight)'))
        );
        $select->columns(
            array('orders' => new Zend_Db_Expr('COUNT(main_table.entity_id)'))
        );

        if ($filterStatuses = $this->_getStatusFilter()) {
            $select->where(
                $this->_getReadAdapter()->quoteInto(
                    'main_table.status IN (?)', $filterStatuses
                )
            );
        }

        return $this->_getReadAdapter()->fetchRow($select);
    }

    protected function _getCountryGroupSql()
    {
        $zones = Mage::helper('moogento_shipeasy/grid')->getCourierRulesZones();

        $sql = '';

        $count = 0;
        foreach ($zones as $zone) {
            if (!count($zone->getCountries())) {
                continue;
            }
            $count++;

            $countries = array_map(array($this, '_quoteCountryId'), $zone->getCountries());
            $countries = implode(',', $countries);

            $sql .= 'IF (`szy_country` IN (' . $countries . '), "' . $zone->getName() . '", ';
        }

        $sql .= '" "';

        for ($i = 0; $i < $count; $i++) {
            $sql .= ')';
        }

        return $sql;
    }
}
