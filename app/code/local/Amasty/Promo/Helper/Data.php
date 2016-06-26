<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_productsCache = null;
    protected $_productsOffersCache = null;

    public function checkAvailableQty($product, $qtyRequested) {
        /**
         * @var Mage_Checkout_Model_Cart $cart
         */
        $cart = Mage::getModel('checkout/cart');
        if (Mage::helper('nwh_retailexpress')->enableDirectSync() === true) {
            Mage::helper('nwh_retailexpress')->sync($product, false);
        }
        $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->assignProduct($product);

        if (!$stockItem->getManageStock())
            return $qtyRequested;

        $qtyAdded = 0;
        foreach ($cart->getItems() as $item) {
            if ($item->getProductId() == $product->getId()) {
                $qtyAdded += $item->getQty();
            }
        }

        $qty = $stockItem->getStockQty() - $qtyAdded;

        return min($qty, $qtyRequested);
    }

    public function addProduct($product, $super = false, $options = false, $bundleOptions = false, $ruleId = false, $amgiftcardValues = array(), $qty = 1) {
        /**
         * @var Mage_Checkout_Model_Cart $cart
         */
        $cart = Mage::getModel('checkout/cart');

        $qty = $this->checkAvailableQty($product, $qty);
        if ((int) $qty <= 0) {
            $this->showMessage(
                    $this->__(
                            "We apologize, <b>" . $product->getName() . '</b> is currently out of stock', $product->getName()
                    ), false, true
            );
            return;
        }

        $requestInfo = array(
            'qty' => $qty,
            'options' => array()
        );

        if ($super)
            $requestInfo['super_attribute'] = $super;

        if ($options)
            $requestInfo['options'] = $options;

        if ($bundleOptions)
            $requestInfo['bundle_option'] = $bundleOptions;

        /* To compatibility amgiftcard module */
        if ($amgiftcardValues) {
            $requestInfo = array_merge($amgiftcardValues, $requestInfo);
        }

        $requestInfo['options']['ampromo_rule_id'] = $ruleId;
        try {
            $cart->addProduct($product->getId(), $requestInfo);

            $cart->getQuote()->save();

            Mage::getSingleton('ampromo/registry')->restore($product->getData('sku'));

            if (!Mage::app()->getRequest()->isXmlHttpRequest()) {
                $this->showMessage(
                        $this->__(
                                "Free gift <b>%s</b> was added to your shopping cart", $product->getName()
                        ), false, true
                );
            }
        } catch (Exception $e) {
            $this->showMessage($e->getMessage(), true, true);
        }
    }

    public function getNewItems() {
        if ($this->_productsCache === null) {
            $items = Mage::getSingleton('ampromo/registry')->getLimits();
            $groups = $items['_groups'];
            unset($items['_groups']);

            if (!$items && !$groups)
                return array();

            $allowedSku = array_keys($items);
            foreach ($groups as $rule) {
                $allowedSku = array_merge($allowedSku, $rule['sku']);
            }

            $addAttributes = array();
            if ($this->isModuleEnabled('Amasty_GiftCard')) {
                $addAttributes = Mage::helper('amgiftcard')->getAmGiftCardOptionsInCart();
            }

            $products = Mage::getResourceModel('catalog/product_collection')
                    ->addFieldToFilter('sku', array('in' => $allowedSku))
                    ->addAttributeToSelect(array_merge(array('name', 'small_image', 'status', 'visibility'), $addAttributes))
            ;

            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), array('simple', 'configurable', 'virtual', 'bundle', 'amgiftcard'))) {
                    $this->showMessage($this->__("We apologize, but products of type <b>%s</b> are not supported", $product->getTypeId()));
                    $products->removeItemByKey($key);
                }
                if (Mage::helper('nwh_retailexpress')->enableDirectSync() === true) {
                    Mage::helper('nwh_retailexpress')->sync($product);
                }

                if (($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) || !$product->isSalable() || !$this->checkAvailableQty($product, 1)
                ) {
                    //$this->showMessage($this->__("We apologize, but your free gift is not available at the moment"));
                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }
            }

            $this->_productsCache = $products;
        }

        return $this->_productsCache;
    }

    public function getOffers() {
        if ($this->_productsOffersCache === null) {
            $ruleid = Mage::getStoreConfig('ampromo/gift/ruleid');
            $rule = Mage::getModel('salesrule/rule')->load($ruleid);
            $skus = explode(',', $rule->getData('promo_sku'));
            foreach ($skus as $k => $sku) {
                $skus[$k] = trim($sku);
            }
            $products = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('sku', array('in' => $skus))
                    ->addAttributeToSelect(array_merge(array('name', 'small_image', 'status', 'visibility')));

            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), array('simple', 'configurable', 'virtual', 'bundle', 'amgiftcard'))) {
                    $this->showMessage($this->__("We apologize, but products of type <b>%s</b> are not supported", $product->getTypeId()));
                    $products->removeItemByKey($key);
                }
                if (Mage::helper('nwh_retailexpress')->enableDirectSync() === true) {
                    Mage::helper('nwh_retailexpress')->sync($product);
                }

                if (($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) || !$product->isSalable() || !$this->checkAvailableQty($product, 1)
                ) {
                    $products->removeItemByKey($key);
                }
            }

            $this->_productsOffersCache = $products;
        }

        return $this->_productsOffersCache;
    }

    public function showMessage($message, $isError = true, $showEachTime = false) {

        Mage::getSingleton('checkout/session')->addNotice($message);
        $arr[] = $message;
        Mage::getSingleton('checkout/session')->setAmpromoMessages($arr);
    }

    public function processPattern($pattern) {
        $result = preg_replace_callback(
                '#{url\s+(?P<url>[\w/]+?)}#', array($this, 'replaceUrl'), $pattern
        );

        return $result;
    }

    public function replaceUrl($matches) {
        return $this->_getUrl($matches['url']);
    }

    public function updateNotificationCookie() {
        $newItems = $this->getNewItems();

        Mage::getModel('core/cookie')->set(
                'am_promo_notification', empty($newItems) ? 0 : 1, null, null, null, null, false
        );
    }

}
