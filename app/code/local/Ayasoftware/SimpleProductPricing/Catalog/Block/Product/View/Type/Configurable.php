<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ayasoftware_SimpleProductPricing_Catalog_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {

    public function getJsonConfig() {
        if (!Mage::getStoreConfig('spp/setting/enableModule')) {
            return parent::getJsonConfig();
        }
        $saveCache = false;
        $cache = Mage::app()->getCache();
        $cacheKey = $this->getProduct()->getId();
        if (intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')) > 0) {
            if ($cache->load("jsonconfig_" . $cacheKey)) {
                $cache_jsonconfig = unserialize($cache->load("jsonconfig_" . $cacheKey));
                return $cache_jsonconfig;
            } else {
                $saveCache = true;
            }
        }
        $showOutOfStock = false;
        $config = Zend_Json::decode(parent::getJsonConfig());
        if (Mage::getStoreConfig('spp/details/show')) {
            $showOutOfStock = true;
        }
        $productsCollection = $this->getAllowProducts();
        $rexHelper = Mage::helper('nwh_retailexpress');
        //Create the extra price and tier price data/html we need.
        foreach ($productsCollection as $product) {
            $productId = $product->getId();
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            $currentItem = Mage::getModel('catalog/product')->load($productId);
            if ($rexHelper->enableDirectSync() === true) {
                $rexHelper->sync($currentItem);
            }
            if ($stockItem->getQty() <= 0 || !($stockItem->getIsInStock())) {
                $stockInfo[$productId] = array(
                    "stockLabel" => $this->__('Out of stock'),
                    "stockQty" => intval($stockItem->getQty()),
                    "is_in_stock" => false,
                    'sku' => $product->getSku(),
                    'stockalert' => Mage::helper('amxnotif')->getStockAlert($product, $this->helper('customer')->isLoggedIn()),
                );
            } else {
                $stockInfo[$productId] = array(
                    "stockLabel" => $this->__('In Stock'),
                    "stockQty" => intval($stockItem->getQty()),
                    "is_in_stock" => true,
                    'sku' => $product->getSku(),
                    'stockalert' => '',
                );
            }
            $finalPrice = $currentItem->getFinalPrice();

            if ($currentItem->getCustomerGroupId()) {
                $finalPrice = $currentItem->getGroupPrice();
            }

            if ($currentItem->getTierPrice()) {
                $tprices = array();
                foreach ($tierprices = $currentItem->getTierPrice() as $tierprice) {
                    $tprices[] = $tierprice['price'];
                }
                $tierpricing = min($tprices);
            } else {
                $tierpricing = '';
            }

            $_price = $currentItem->getPrice();
            $rrp = $currentItem->getMsrp();
            if (!empty($rrp)) {
                $_price = $rrp;
            }
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($_price)),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($finalPrice)),
                "tierpricing" => $this->_registerJsPrice($this->_convertPrice($tierpricing)),
            );
            if (Mage::getStoreConfig('spp/details/productname')) {
                $ProductNames[$productId] = array(
                    "ProductName" => $currentItem->getName()
                );
            }
            if (Mage::getStoreConfig('spp/details/shortdescription')) {
                $shortDescriptions[$productId] = array(
                    "shortDescription" => $this->helper('catalog/output')->productAttribute($currentItem, nl2br($currentItem->getShortDescription()), 'short_description')
                );
            }
            if (Mage::getStoreConfig('spp/details/description')) {
                $Descriptions[$productId] = array(
                    "Description" => $this->helper('catalog/output')->productAttribute($currentItem, nl2br($currentItem->getDescription()), 'description')
                );
            }

            if (Mage::getStoreConfig('spp/details/productAttributes')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                        ->setProduct($this->getProduct())
                        ->toHtml();
                $config['product_attributes_markup'] = Mage::getStoreConfig('spp/markup/product_attributes_markup');
            } else {
                $config['productAttributes'] = false;
            }
        }
        if (Mage::getStoreConfig('spp/details/customstockdisplay')) {
            $config['customStockDisplay'] = true;
            $config['product_customstockdisplay_markup'] = Mage::getStoreConfig('spp/markup/product_customstockdisplay_markup');
        } else {
            $config['customStockDisplay'] = false;
        }
        $config['showOutOfStock'] = $showOutOfStock;
        $config['stockInfo'] = $stockInfo;
        $config['childProducts'] = $childProducts;
        $config['showPriceRangesInOptions'] = true;
        $config['rangeToLabel'] = $this->__('-');
        if (Mage::getStoreConfig('spp/setting/hideprices')) {
            $config['hideprices'] = true;
        } else {
            $config['hideprices'] = false;
        }
        if (Mage::getStoreConfig('spp/details/disable_out_of_stock_option')) {
            $config['disable_out_of_stock_option'] = true;
        } else {
            $config['disable_out_of_stock_option'] = false;
        }
        if (Mage::getStoreConfig('spp/details/productname')) {
            $config['productName'] = $this->getProduct()->getName();
            $config['ProductNames'] = $ProductNames;
            $config['product_name_markup'] = Mage::getStoreConfig('spp/markup/product_name_markup');
            $config['updateProductName'] = true;
        } else {
            $config['updateProductName'] = false;
        }
        if (Mage::getStoreConfig('spp/details/shortdescription')) {
            $config['shortDescription'] = $this->getProduct()->getShortDescription();
            $config['shortDescriptions'] = $shortDescriptions;
            $config['product_shortdescription_markup'] = Mage::getStoreConfig('spp/markup/product_shortdescription_markup');
            $config['updateShortDescription'] = true;
        } else {
            $config['updateShortDescription'] = false;
        }
        if (Mage::getStoreConfig('spp/details/description')) {
            $config['description'] = $this->getProduct()->getDescription();
            $config['Descriptions'] = $Descriptions;
            $config['product_description_markup'] = Mage::getStoreConfig('spp/markup/product_description_markup');
            $config['updateDescription'] = true;
        } else {
            $config['updateDescription'] = false;
        }

        if (Mage::getStoreConfig('spp/details/showfromprice')) {
            $config['showfromprice'] = true;
        } else {
            $config['showfromprice'] = false;
        }
        if (Mage::getStoreConfig('spp/media/updateproductimage')) {
            $config['updateproductimage'] = true;
            $config['product_image_markup'] = Mage::getStoreConfig('spp/markup/product_image_markup');
        } else {
            $config['updateproductimage'] = false;
        }
        $config['priceFromLabel'] = $this->__('From:');
        if (Mage::getStoreConfig('web/secure/use_in_frontend')) {
            $config['ajaxBaseUrl'] = Mage::getUrl('spp/ajax/', array('_secure' => true));
        } else {
            $config['ajaxBaseUrl'] = Mage::getUrl('spp/ajax/');
        }
        $config['zoomtype'] = Mage::getStoreConfig('spp/media/zoomtype');
        $jsonConfig = Zend_Json::encode($config);
        if (intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')) > 0 && $saveCache) {
            $cache->save(serialize($jsonConfig), "jsonconfig_" . $cacheKey, array("jsonconfig_cache"), intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')));
        }
        return $jsonConfig;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function canShowOutOfStockProducts() {

        $category_name = Mage::getSingleton('catalog/layer')
                ->getCurrentCategory()
                ->getName();
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                    ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    public function getAllowProducts() {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                    ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                // if (Mage::getStoreConfig('spp/details/show')) {
                //     $products[] = $product;
                // } else {
                if ((int) $product->getStatus() !== Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    $products[] = $product;
                }
                // }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

}
