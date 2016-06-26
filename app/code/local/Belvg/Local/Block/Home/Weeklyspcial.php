<?php

class Belvg_Local_Block_Home_Weeklyspcial extends Mage_Catalog_Block_Product_Abstract
{
    const CACHE_TAG = 'weeklyspcial_collection';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId()  . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    public function getCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToFilter('Weekly_specials', '1')
            ->addAttributeToSelect(array('name', 'url_key', 'small_image', 'short_description'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        return $collection;
    }

}