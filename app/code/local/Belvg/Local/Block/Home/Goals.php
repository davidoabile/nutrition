<?php

class Belvg_Local_Block_Home_Goals extends Mage_Core_Block_Template
{
    const CACHE_TAG = 'goals_collection';

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
        $groups      = Mage::getResourceModel('attributeSplash/group_collection')
            ->addFieldToFilter('attribute_id', Belvg_Local_Helper_Data::ATTRIBUTE_GOALS_ID);
        $splashGroup = $groups->getFirstItem();
        $splashPages = $splashGroup
            ->getSplashPages()
            ->addOrderBySortOrder();

        return $splashPages;
    }

}