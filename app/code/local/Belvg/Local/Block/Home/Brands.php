<?php

class Belvg_Local_Block_Home_Brands extends Mage_Core_Block_Template
{
    const CACHE_TAG = 'brands_collection';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId() . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    public function getCollection()
    {
        $splashGroup = $this->getBrandsGroup();
        $splashPages = $splashGroup
            ->getSplashPages()
            ->addOrderBySortOrder();

        return $splashPages;
    }

    public function getBrandsGroup()
    {
        $groups = Mage::getResourceModel('attributeSplash/group_collection')
            ->addFieldToFilter('attribute_id', Belvg_Local_Helper_Data::ATTRIBUTE_BRAND_ID);
        return $groups->getFirstItem();
    }

}