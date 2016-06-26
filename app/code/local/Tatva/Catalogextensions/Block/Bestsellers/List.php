<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of List
 *
 * @author om
 */
class Tatva_Catalogextensions_Block_Bestsellers_List extends Mage_Catalog_Block_Product_List
{
    protected function old_getProductCollection()
    {
        parent::__construct();
        $storeId    = Mage::app()->getStore()->getId();
        $products = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
			->addOrderedQty()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            //->setOrder('ordered_qty', 'desc')
    		->setPageSize($this->get_prod_count())
            ->setOrder($this->get_order(), $this->get_order_dir())
            ->setCurPage($this->get_cur_page());
            
		$productFlatData = Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
		if($productFlatData == "1")
		{
			$products->getSelect()->joinLeft(
	                array('flat' => 'catalog_product_flat_'.$storeId),
	                "(e.entity_id = flat.entity_id ) ",
	                //array(
//	                   'flat.name AS name','flat.image AS small_image','flat.price AS price','flat.minimal_price as minimal_price','flat.special_price as special_price','flat.special_from_date AS special_from_date','flat.special_to_date AS special_to_date'
//	                )
					array(
	                   'flat.name AS name','flat.small_image AS small_image','flat.price AS price','flat.special_price as special_price','flat.special_from_date AS special_from_date','flat.special_to_date AS special_to_date'
					)
	            );
		}

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);

        $this->_productCollection = $products;

        return $this->_productCollection;
    }
	protected function remove_getProductCollection()
    {
        parent::__construct();
        $storeId = (int) Mage::app()->getStore()->getId();
	 
			// Date
			$date = new Zend_Date();
			$toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
			$fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
	 
			$collection = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
				->addStoreFilter()
				->addPriceData()
				->addTaxPercents()
				->addUrlRewrite()
				->setPageSize(Mage::getStoreConfig('catalogextensions/config1/max_product') * 2);
	 
			$collection->getSelect()
				->joinLeft(
					array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
					"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
					array('SUM(aggregation.qty_ordered) AS sold_quantity')
				)
				->group('e.entity_id')
				->order(array('sold_quantity DESC', 'e.created_at'));
	 
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
	 
			$this->_productCollection = $collection;

        return $this->_productCollection;
    }
    protected function _getProductCollection()
    {
        parent::__construct();
        $storeId    = Mage::app()->getStore()->getId();
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter(array(array('attribute' => 'top_seller', 'eq' => '1')))
            ->addAttributeToSelect('*')
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setPageSize(Mage::getStoreConfig('catalogextensions/config1/max_product') * 2);
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);

        $this->_productCollection = $products;

        return $this->_productCollection;
    }

    function get_prod_count()
	{
		//unset any saved limits
	    Mage::getSingleton('catalog/session')->unsLimitPage();
	    return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : Mage::getStoreConfig('catalogextensions/config1/max_product') * 2;
	}// get_prod_count

	function get_cur_page()
	{
		return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
	}// get_cur_page

    function get_order()
	{
		return (isset($_REQUEST['order'])) ? ($_REQUEST['order']) : 'ordered_qty';
	}// get_order

    function get_order_dir()
	{
		return (isset($_REQUEST['dir'])) ? ($_REQUEST['dir']) : 'desc';
	}// get_direction
}

?>
