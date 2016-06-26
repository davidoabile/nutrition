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
* File        Collection.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_Core_Model_Mysql4_Sales_Order_Grid_Collection extends Mage_Sales_Model_Mysql4_Order_Grid_Collection
{

    public function clear()
    {
        $this->_setIsLoaded(false);
        $this->_items = array();
        $this->_totalRecords = null;
        return $this;
    }
    
    public function getAllIds($limit=null, $offset=null)
    {
        $session = Mage::getSingleton('adminhtml/session');

        $filter = $session->getData('sales_order_gridfilter');
        if (!$filter) {
            $filter = 'EMPTY';
        }

        $cache = Mage::app()->getCache();

        $cacheKey = 'Order_Grid_Collection_AllIds_' . $filter;
        $data = $cache->load($cacheKey);
        if ($data) return explode(',',$data);

        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');

        $data = $this->getConnection()->fetchCol($idsSelect);
        $cache->save(implode(',', $data), $cacheKey, array('moogento_cache'), 600);
        return $data;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        if (method_exists($this, 'getIsCustomerMode') && $this->getIsCustomerMode()) {
            return parent::getSelectCountSql();
        } else {
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);

            $countSelect->columns('main_table.entity_id');
        }

        $select = $this->getConnection()->select();
        $select->from($countSelect);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->columns('count(*)');

        return $select;
    }

    public function addFieldToFilter($field, $condition = null) {
        if($field == 'increment_id') {
            $field = 'main_table.'.$field;
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
