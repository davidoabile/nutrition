<?php

class Belvg_Local_Block_Homecollection extends Mage_Catalog_Block_Product_Abstract
{
    const CACHE_TAG = 'home_collection';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId() . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    public function getHomeCollection()
    {
        /*$productIds = explode(",", Mage::getStoreConfig('featuredproducts/productids/productids'));
        $items = array();
        foreach ($productIds as $key => $id) {
            if ($id != '') {
                try {
                    $item = Mage::getModel('catalog/product')->load($id);
                } catch (Exception $e) {
                    continue;
                }
                if ($item->getId()) {
                    $items[] = $item;
                }
            }
        }

        return $items;*/

        $productIds = explode(",", Mage::getStoreConfig('featuredproducts/productids/productids'));
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->addAttributeToSelect(array('name','url_key','small_image'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        return $collection;
    }

}