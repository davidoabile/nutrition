<?php

class Belvg_Local_Block_Category_View_Shopby extends Mage_Catalog_Block_Category_View
{
    const CACHE_TAG = 'catalog_category_view_shopby';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId()  . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    public function getCategoryCollections()
    {
        $categories = Mage::getModel('catalog/category')->getCollection();
        $categories
            ->addAttributeToSelect('*')
            ->addFieldToFilter('parent_id', Belvg_Local_Helper_Data::CATEGORY_CATEGORY)
            ->addFieldToFilter('is_active', array('eq' => '1'))
            ->addAttributeToSort('position', 'asc')
            ->addUrlRewriteToResult();;

        return $categories;
    }

}