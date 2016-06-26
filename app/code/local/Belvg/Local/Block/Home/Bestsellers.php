<?php

class Belvg_Local_Block_Home_Bestsellers extends Mage_Core_Block_Template
{
    const CACHE_TAG = 'bestsellers_collection';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId() . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    public function getCollectionHtml()
    {
        return $this->getLayout()->createBlock('catalogextensions/bestsellers_home_list')->setTemplate('catalogextensions/home_bestsellers.phtml')->toHtml();
    }

}