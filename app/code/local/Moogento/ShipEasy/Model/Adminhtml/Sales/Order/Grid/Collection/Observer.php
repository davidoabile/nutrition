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
* File        Observer.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_Sales_Order_Grid_Collection_Observer
{
    protected $_called = false;

    public function moogento_core_order_grid_collection_prepare($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($this->_called) return $collection;
        $this->_called = true;


        $select = $collection->getSelect();

        if (Mage::getStoreConfig('moogento_shipeasy/grid/szy_country_region_show')) {
            $sql = Mage::helper('moogento_shipeasy/grid')->getCountryGroupSql();

            if ($sql) {
                $select->columns(
                    array('szy_country_region' => new Zend_Db_Expr($sql))
                );
            }
        }

        if (Mage::getStoreConfig('moogento_shipeasy/grid/backorder_show')) {
            $sql = Mage::helper('moogento_shipeasy/grid')->getBackorderSql();
            $select->columns(
                array('backorder' => new Zend_Db_Expr("({$sql})"))
            );
        }
            
        if(Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_type_field') == "ship"){
            $existingStatusGroups = Mage::getStoreConfig('moogento_shipeasy/grid/szy_shipping_method_method_group');
            if ($existingStatusGroups) {
                @$existingStatusGroups = unserialize($existingStatusGroups);
                if (is_array($existingStatusGroups) && count($existingStatusGroups)) {
                    foreach ($existingStatusGroups as $groupStatuses) {
                        if($groupStatuses['name'] == Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_ship_field')){
                            foreach($groupStatuses['method'] as $method){
                                $select->order(new Zend_Db_Expr('IF (szy_shipping_method LIKE "%'.$method.'%", 1, 0) DESC'));
                            }
                        }
                    }
                }
            }            
        }
        if(Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_type_field') == "courierrules"){
            $existingStatusGroups = Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group');
            if ($existingStatusGroups) {
                @$existingStatusGroups = unserialize($existingStatusGroups);
				if (is_array($existingStatusGroups) && count($existingStatusGroups)) {
	                foreach (@$existingStatusGroups[Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_courier_rule_field')]["courierrules"] as $key => $elem){
	                    $val = ($elem != "custom_value") ? $elem : @$existingStatusGroups[Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_courier_rule_field')]["custom_value"];
	                    $select->order(new Zend_Db_Expr('IF (main_table.courierrules_description LIKE "%'.$val.'%", 1, 0) DESC'));
	                }
				}
            }
        }
        
        if(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field') && Mage::getStoreConfig('moogento_shipeasy/grid/'.Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field').'_show')){
            $grid = $observer->getEvent()->getGrid();
            $grid->setDefaultSort(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field'));
            $grid->setDefaultDir(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_type') ? 'ASC' : 'DESC');
        }

        return $collection;
    }
}
