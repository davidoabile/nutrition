<?php

class Moogento_EasyCoupon_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * get product url without loading product itself;
     * @param $productId
     */
    public function getProductUrlById($productId){
        $storeId = Mage::app()->getStore()->getId();
        $product_collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('entity_id', $productId)
            ->addStoreFilter($storeId)
            ->addUrlRewrite()
            ->setCurPage(1);
        $productUrl = $product_collection->getFirstItem()->getProductUrl();
        return $productUrl;
    }

    /**
     * get product type without loading product itself
     * @param $productId
     * @return mixed
     */
    public function getIsSimple($productId){

        $storeId = Mage::app()->getStore()->getId();
        $product_collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('entity_id', $productId)
            ->addAttributeToSelect(array('type_id'))
            ->addStoreFilter($storeId)
            ->setCurPage(1);
        $productType = $product_collection->getFirstItem()->getTypeId();
        if('simple' == $productType){
            return true;
        }
        return false;
    }

    public function isInCart($productId){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote->hasProductId($productId)) {
            return true;
        }
        return false;
    }
}