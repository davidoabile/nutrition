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
 * File        Grid.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Helper_Grid extends Mage_Core_Helper_Abstract
{
    public function getCancelTypeBlock()
    {
        $block = Mage::app()->getLayout()
                     ->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_massaction_cancelType');

        return $block;
    }

    public function getUpdateAttributeBlock()
    {
        $block = Mage::app()->getLayout()
                     ->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_massaction_updateAttribute');

        return $block;
    }

    public function getMassActionNotifyBlock($action)
    {
        $block = Mage::app()->getLayout()
                     ->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_massaction_notify');
        $block->setActionCode($action);

        return $block;
    }

    public function getMassActionAssignTrackingBlock($action)
    {
        $block = Mage::app()->getLayout()
                     ->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_massaction_assigntracking');
        $block->setActionCode($action);

        return $block;
    }

    public function getMassActionStatusNotifyBlock($action)
    {
        $block = Mage::app()->getLayout()
                     ->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_massaction_notify');
        $block->setActionCode($action);
        $block->setUseStatuses(true);

        return $block;
    }

    protected function _getUrl($route, $params = array())
    {
        return Mage::getModel('adminhtml/url')->getUrl($route, $params);
    }

    protected function _isAllowed($action)
    {
        return true;// Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }

    public function getMassActionItems()
    {
        $items = array();

        if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_seperator1') == 1) {
            $items['sEzySeparator1'] = array(
                'label' => Mage::helper('moogento_shipeasy')->__('---------------'),
                'url'   => ''
            );
        }

        if ($this->_isAllowed('moo_shipeasy_ship')) {
            if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_ship') == 1) {
                $items['ship_order'] = array(
                    'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Ship'),
                    'url'        => $this->_getUrl('*/sales_order_process/massShip'),
                    'additional' => $this->getMassActionNotifyBlock('ship_order')
                );
            }
        }

        $items['assign_tracking'] = array(
            'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Assign tracking'),
            'url'        => $this->_getUrl('*/sales_order_process/massAssigntracking'),
            'additional' => $this->getMassActionAssignTrackingBlock('assign_tracking'),
            'func'       => "assignTracking"
        );

        if ($this->_isAllowed('moo_shipeasy_invoice')) {
            if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_invoice') == 1) {
                $items['invoice_order'] = array(
                    'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Invoice'),
                    'url'        => $this->_getUrl('*/sales_order_process/massInvoice'),
                    'additional' => $this->getMassActionNotifyBlock('invoice_order')
                );
            }
        }

        if ($this->_isAllowed('moo_shipeasy_ship_and_invoice')) {
            if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_ship_and_invoice') == 1) {
                $items['ship_invoice_order'] = array(
                    'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Ship & Invoice'),
                    'url'        => $this->_getUrl('*/sales_order_process/massProcess'),
                    'additional' => $this->getMassActionNotifyBlock('ship_invoice_order')
                );
            }
        }

        if ($this->_isAllowed('moo_shipeasy_change_status')) {
            if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_change_status') == 1) {
                $items['order_change_status'] = array(
                    'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Change Status'),
                    'url'        => $this->_getUrl('*/sales_order_process/updateStatus'),
                    'additional' => $this->getMassActionStatusNotifyBlock('order_status')
                );
            }
        }

        if ($this->_isAllowed('moo_shipeasy_change_update_custom_attribute')) {
            if (Mage::getStoreConfig('moogento_shipeasy/action_menu/show_update_custom_attribute') == 1) {
                $items['custom_order_attribute'] = array(
                    'label'      => 'sE ' . Mage::helper('moogento_shipeasy')->__('Update Custom Attribute '),
                    'url'        => $this->_getUrl('*/sales_order_process/updateAttribute1'),
                    'additional' => $this->getUpdateAttributeBlock()
                );
            }
        }

        if (Mage::helper('moogento_core')->isInstalled('Raveinfosys_Deleteorder')) {
            $items['delete_order'] = array(
                'label'   => Mage::helper('moogento_shipeasy')->__('Delete Order'),
                'url'     => $this->_getUrl('deleteorder/adminhtml_deleteorder/massDelete'),
                'confirm' => Mage::helper('sales')->__('Are you sure you want to delete order?')
            );
        }


        //Return empty array if just has ------ in menu.
        if (count($items) < 2) {
            unset($items);
            $items = array();
        }


        return $items;
    }

    public static function setShippingMethodFilter($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();

        if (isset($condition['value']) && (strlen(trim($condition['value'])) > 0)) {
            if ($condition['condition'] == 1) {
                $conditionOp = 'nlike';
            } else {
                $conditionOp = 'like';
            }
            if (strpos($condition['value'], 'group_') === 0) {
                $groupName = str_replace('group_', '', $condition['value']);

                $existingStatusGroups = Mage::getStoreConfig('moogento_shipeasy/grid/szy_shipping_method_method_group');
                $groupData            = false;
                if ($existingStatusGroups) {
                    @$existingStatusGroups = unserialize($existingStatusGroups);
                    if (is_array($existingStatusGroups) && count($existingStatusGroups)) {
                        foreach ($existingStatusGroups as $groupStatuses) {
                            if ($groupStatuses['name'] == $groupName) {
                                $groupData = $groupStatuses;
                            }
                        }
                    }

                }
                if ($groupData) {
                    $fields = array();
                    if (in_array('custom_value', $groupData['method'])) {
                        $fields[]
                            = 'szy_shipping_description ' . $conditionOp . '"%' . $groupData['custom_value'] . '%"';
                    }
                    if ($condition['condition'] == 1) {
                        $collection->addFieldToFilter('szy_shipping_description',
                            array($conditionOp => '%' . $groupData['custom_value'] . '%'));
                        $collection->getSelect()
                                   ->where('szy_shipping_method NOT RLIKE ?', implode('|', $groupData['method']));
                    } else {
                        $fields[] = 'szy_shipping_method regexp "' . implode('|', $groupData['method']) . '"';
                        $collection->getSelect()->where('(' . implode(' OR ', $fields) . ')');
                    }
                }
            } else {

                if ($condition['value'] != 'szy_shipping_custom_value') {
                    $conditionValue = $condition['value'];
                    $field          = 'szy_shipping_method';
                } else {
                    $conditionValue = $condition['custom_value'];
                    $field          = 'szy_shipping_description';
                }
                $collection->addFieldToFilter(
                    $field,
                    array(
                        $conditionOp => "%{$conditionValue}%"
                    )
                );
            }
        }
    }

    public static function setCustomAttributeFilter($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        if ($condition['value'] == 'custom') {
            $conditionValue = $condition['custom_value'];
        } else if ($condition['value'] == '{{date}}') {
            $conditionValue = $condition['date'];            
        } else {
            $conditionValue = $condition['value'];
        }
        $pos = strrpos($conditionValue, '|');
        if ($pos !== false) {
            $conditionValue = substr($conditionValue, 0, $pos);
        }

        if ($condition['condition'] == 1) {
            $operator = 'nlike';
            $collection->addFieldToFilter(
                $column->getIndex(),
                array(
                    array($operator => "%{$conditionValue}%"),
                    array('null' => true),
                )
            );
        } else {
            $operator = 'like';
            $collection->addFieldToFilter(
                $column->getIndex(),
                array(
                    array($operator => "%{$conditionValue}%"),
                )
            );
        }
    }

    public static function setCustomAttributeFirstFilter($collection, $column)
    {
        $type_condition = 0;
        $condition = $column->getFilter()->getCondition();
        if ($condition['value'] == 'custom') {
            $conditionValue = $condition['custom_value'];
            $type_condition = 1;
        } else if ($condition['value'] == '{{date}}') {
            $conditionValue = $condition['date'];            
        } else {
            $conditionValue = $condition['value'];
        }
        $pos = strrpos($conditionValue, '|');
        if ($pos !== false) {
            $conditionValue = substr($conditionValue, 0, $pos);
        }
        
        if($type_condition){
            $configValues = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute_preset');
            $configValuesLines = explode("\n", $configValues);
            $configValuesArray = array();
            foreach ($configValuesLines as $conVal){
                $flag      = $conVal;
                $flag      = str_replace('{{', '', $flag);
                $flag      = str_replace('}}', '', $flag);
                $flag      = str_replace('{', '', $flag);
                $flag      = str_replace('}', '', $flag);
                $flag      = trim($flag);
                if ($flag) {
                    $configValuesArray[] = "'" . substr($flag, 0, strpos($flag, "|")) . "'";
                }
            }
            array_filter($configValuesArray);
            if (count($configValuesArray)) {
                $resource       = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $table          = $resource->getTableName('sales/order_grid');

                $query          = 'SELECT entity_id FROM ' . $table . ' WHERE szy_custom_attribute IN('
                                  . implode(",", $configValuesArray) . ')';
                $results        = $readConnection->fetchAll($query);
                $resultsArray   = array();
                foreach ($results as $result) {
                    $resultsArray[] = $result['entity_id'];
                }
                $collection->addAttributeToFilter('entity_id', array('nin' => $resultsArray));
            }
        } 

        if ($condition['condition'] == 1) {
            $operator = 'nlike';
            $collection->addFieldToFilter(
                $column->getIndex(),
                array(
                    array($operator => "%{$conditionValue}%"),
                    array('null' => true),
                )
            );
        } else {
            $operator = 'like';
            $collection->addFieldToFilter(
                $column->getIndex(),
                array(
                    array($operator => "%{$conditionValue}%"),
                )
            );
        }        
    }

    public static function setImageFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addFieldToFilter('szy_product_names', array('like' => "%{$value}%"));
    }

    public static function setCommentFilter($collection, $column)
    {
        $select = $collection->getSelect();

        $collection_of_status_history = Mage::getModel("sales/order_status_history")->getCollection();
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $collection_of_status_history->addFieldToFilter("comment", array('like' => "%{$value}%"));
        $select_of_status_history = $collection_of_status_history
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('parent_id');
        $union                    = $select_of_status_history;

        if (Mage::helper('moogento_core')->isInstalled('MW_Onestepcheckout')) {
            $collection_mv_onestepcheckout = Mage::getModel('onestepcheckout/onestepcheckout')->getCollection();
            $collection_mv_onestepcheckout->addFieldToFilter("mw_customercomment_info", array('like' => "%{$value}%"));
            $select_mv_onestepcheckout = $collection_mv_onestepcheckout
                ->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('sales_order_id');
            $union .= " UNION " . $select_mv_onestepcheckout;
        }
        if (Mage::helper('moogento_core')->isInstalled('Idev_OneStepCheckout')) {
            $collection_idev_onestepcheckout_comment = Mage::getModel('sales/order')->getCollection();
            $collection_idev_onestepcheckout_comment->addFieldToFilter("onestepcheckout_customercomment",
                array('like' => "%{$value}%"));
            $select_idev_onestepcheckout_comment = $collection_idev_onestepcheckout_comment
                ->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('entity_id');
            $union .= " UNION " . $select_idev_onestepcheckout_comment;

            $collection_idev_onestepcheckout_feedback = Mage::getModel('sales/order')->getCollection();
            $collection_idev_onestepcheckout_feedback->addFieldToFilter("onestepcheckout_customerfeedback",
                array('like' => "%{$value}%"));
            $select_idev_onestepcheckout_feedback = $collection_idev_onestepcheckout_feedback
                ->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('entity_id');
            $union .= " UNION " . $select_idev_onestepcheckout_feedback;
        }

        $select->where("main_table.entity_id IN (" . $union . ")");

    }

    public static function setCountryGroupFilter($collection, $column)
    {
        $zone = Mage::getModel('moogento_courierrules/zone')->load($column->getFilter()->getValue());
        $countries = (array) $zone->getCountries();
        $codes = array_filter($zone->getZipCodes());

        $condition = array();
        if (!empty($countries)) {
            $condition[] = 'main_table.szy_country IN ("' . implode('","', $zone->getCountries()) . '")';
        }
        if (!empty($codes)) {
            $codesCondition = array();
            foreach ($codes as $code) {
                $code = str_replace('*', '%', $code);
                $codesCondition[] = 'main_table.szy_postcode like "' . $code . '"';
            }
            if (count($codesCondition)) {
                $condition[] = '(' . implode(' OR ', $codesCondition) . ')';
            }
        }
        if (count($condition)) {
            $collection->getSelect()->where('(' . implode(' AND ', $condition) . ')');
        } else {
            $collection->getSelect()->where('1=0');
        }
    }

    public static function setPaidFilter($collection, $column)
    {
        $filterValue = $column->getFilter()->getValue();
        if (isset($filterValue['from'])) {
            $collection->getSelect()->where('ifnull(total_paid, grand_total) >= ?', $filterValue['from']);
        }
        if (isset($filterValue['to'])) {
            $collection->getSelect()->where('ifnull(total_paid, grand_total) <= ?', $filterValue['to']);
        }
    }

    public static function setEbayUserIdFilter($collection, $column)
    {
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $conditionValue = $column->getFilter()->getCondition();
            if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_ebay_customer_id_show_email')) {

                $collection
                    ->addFieldToFilter(
                        array('szy_ebay_customer_id', 'szy_customer_email'),
                        array(
                            $conditionValue,
                            $conditionValue
                        )
                    );
            } else {
                $collection
                    ->addFieldToFilter(
                        array('szy_ebay_customer_id'),
                        array(
                            $conditionValue
                        )
                    );
            }
        }
    }

    public static function getCountryGroupSql()
    {
        $sql = '';
        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            $zones = Mage::getModel('moogento_courierrules/zone')->getCollection();
            $zonesSql = array();

            foreach ($zones as $zone) {
                $countries = (array) $zone->getCountries();
                $codes = array_filter($zone->getZipCodes());
                if (empty($countries) && empty($codes)) {
                    continue;
                }
                $condition = array();
                if (!empty($countries)) {
                    $condition[] = 'main_table.szy_country IN ("' . implode('","', $zone->getCountries()) . '")';
                }
                if (!empty($codes)) {
                    $codesCondition = array();
                    foreach ($codes as $code) {
                        $code = str_replace('*', '%', $code);
                        $codesCondition[] = 'main_table.szy_postcode like "' . $code . '"';
                    }
                    if (count($codesCondition)) {
                        $condition[] = '(' . implode(' OR ', $codesCondition) . ')';
                    }
                }
                $zonesSql[] = 'IF(' . implode(' AND ', $condition) .', "' . $zone->getId() . '", NULL)';
            }
            if (count($zonesSql)) {
                $sql = 'CONCAT_WS(",", ' . implode(',', $zonesSql) . ')';
            }
        }

        return $sql;
    }

    public static function setSkuNumberFilter($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        $select = $collection->getSelect();
        $select->where('szy_sku_number = ' . ($filter == 'single' ? '0' : '1'));
    }

    public function getCourierRulesZones()
    {
        $using_zones  = explode(",", Mage::getStoreConfig('moogento_shipeasy/country_groups/shipping_zone'));
        $result_zones = array();

        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            $zones = Mage::getModel('moogento_courierrules/zone')->getCollection();
            foreach ($zones as $zone) {
                if (in_array($zone->getId(), $using_zones)) {
                    $result_zones[] = $zone;
                }
            }
        }

        return $result_zones;
    }

    public static function setBackordersFilter($collection, $column)
    {
        $condition      = $column->getFilter()->getCondition();
        $conditionValue = current($condition);
        $select         = $collection->getSelect();
        $select->where(new Zend_Db_Expr('(' . self::getBackorderSql() . ') = ' . $conditionValue["value"]));
    }

    public static function getBackorderSql()
    {
        $resourceModel = Mage::getResourceSingleton('moogento_shipeasy/sales_order');
        $readAdapter   = $resourceModel->getReadConnection();

        $select = $readAdapter->select();
        $select->from(
            array('order_item' => $resourceModel->getTable('sales/order_item')),
            array()
        );

        $select->join(
            array('stock_item' => $resourceModel->getTable('cataloginventory/stock_item')),
            'order_item.product_id = stock_item.product_id',
            array()
        );

        $configManageStockValue
            = (int) Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);

        $select->columns(array(
            'is_covered' => new Zend_Db_Expr('
            CASE
                AVG(
                    IF(
                        IF (stock_item.use_config_manage_stock, ' . $configManageStockValue . ', stock_item.manage_stock) = 1,
                        IF (stock_item.is_in_stock = 1 AND (stock_item.qty + order_item.qty_ordered) > 0, 2, 0),
                        2
                    )
                ) 
            WHEN 0 THEN 0
            WHEN 2 THEN 2
            ELSE 1
            END
        ')
        ));

        $select->where('order_item.order_id = main_table.entity_id');
        $select->where('order_item.product_type != "configurable"');
        $select->group('order_item.order_id');

        return $select;

    }

    public static function setTrackingFilter($collection, $column)
    {
        $value = strtolower($column->getFilter()->getValue());

        $select = $collection->getSelect();
        $select->joinLeft(
            array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
            'main_table.entity_id = shipment.order_id',
            array()
        );
        $select->joinLeft(
            array('tracking' => Mage::getSingleton('core/resource')->getTableName('sales/shipment_track')),
            'shipment.entity_id = tracking.parent_id',
            array()
        );

        $carriers = Mage::helper('moogento_core/carriers')->getCarriersConfig();
        $write = Mage::getSingleton('core/resource')->getConnection('default_write');

        $queries = array(
            'tracking.track_number like ' . $write->quote('%' . $value . '%'),
            'main_table.preshipment_tracking like ' . $write->quote('%' . $value . '%'),
        );
        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            $queries[] = 'main_table.courierrules_tracking like ' . $write->quote('%' . $value . '%');
        }
        foreach ($carriers as $code => $carrier) {
            if (strpos(strtolower($carrier['title']), $value) !== false) {
                if (trim($carrier['code'])) {
                    $queries[] = 'tracking.track_number like ' . $write->quote($carrier['code'] . '%');
                    $queries[] = 'main_table.preshipment_tracking like ' . $write->quote($carrier['code'] . '%');
                    if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
                        $queries[] = 'main_table.courierrules_tracking like ' . $write->quote($carrier['code'] . '%');
                    }
                } else if ($carrier['length']) {
                    $queries[] = 'LENGTH(tracking.track_number) = ' . (int) $carrier['length'];
                    $queries[] = 'main_table.preshipment_tracking like ' . $write->quote('%' . $code);
                    if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
                        $queries[] = 'main_table.courierrules_tracking like ' . $write->quote('%' . $code);
                    }
                }
            }
        }
        $select->where(implode(' OR ', $queries));

        $select->group('main_table.entity_id');
    }

    public function getMktLink($item)
    {
        $item_id = $item->getEbayItemId();
        if (!$item_id && $item->getParentItemId()) {
            $item_id = $item->getParentItem()->getEbayItemId();
        }
        if ($item_id) {
            $itemParts = explode('|', $item_id);

            switch ($itemParts[0]) {
                case 'ebay':
                    return Mage::helper('M2ePro/Component_Ebay')
                               ->getItemUrl($itemParts[1], $itemParts[2], $itemParts[3]);
                case 'amazon':
                    return Mage::helper('M2ePro/Component_Amazon')->getItemUrl($itemParts[1], $itemParts[2]);
            }
        }

        return '';
    }

    public static function setTimezoneFilter($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        $select     = $collection->getSelect();
        $time_start = Mage::getStoreConfig('moogento_shipeasy/grid/timezone_time_start');
        $time_end   = Mage::getStoreConfig('moogento_shipeasy/grid/timezone_time_end');

        switch ($value) {
            case 0:
                break;
            case 1:
                $select->where("date_format(UTC_TIMESTAMP() + INTERVAL main_table.timezone_offset SECOND, '%H:%i') > ?",
                    $time_end)
                       ->orWhere("date_format(UTC_TIMESTAMP() + INTERVAL main_table.timezone_offset SECOND, '%H:%i') < ?",
                           $time_start);
                break;
            case 2:
                $select->where("date_format(UTC_TIMESTAMP() + INTERVAL main_table.timezone_offset SECOND, '%H:%i') <= ?",
                    $time_end)
                       ->where("date_format(UTC_TIMESTAMP() + INTERVAL main_table.timezone_offset SECOND, '%H:%i') >= ?",
                           $time_start);
                break;
        }
        $select->group('main_table.entity_id');
    }
}
