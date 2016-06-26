<?php

class Belvg_Local_Block_Home_Accessories extends Mage_Catalog_Block_Product_Abstract
{
    const CACHE_TAG = 'accessories_collection';
    protected $_category = NULL;

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
            ->addAttributeToSelect(array('name', 'url_key', 'small_image', 'short_description'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        /*$collection
            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
            ->addAttributeToFilter('category_id', array(array(
                'finset' => Belvg_Local_Helper_Data::CATEGORY_ACCESSORIES
            )));*/
        $collection->addCategoryFilter($this->getCategory());

        return $collection;
    }

    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::getModel('catalog/category')->load(Belvg_Local_Helper_Data::CATEGORY_ACCESSORIES);
        }

        return $this->_category;
    }

    public function getCategoryUrl()
    {
        return $this->getCategory()->getUrl($this->getCategory());
    }

}