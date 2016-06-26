<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Ddmenu_Block_Navigation_Lastproduct extends Mage_Catalog_Block_Navigation
{
    protected $_priceBlock = array();

    /**
     * Default price block
     *
     * @var string
     */
    protected $_block = 'catalog/product_price';

    protected $_priceBlockDefaultTemplate = 'catalog/product/price.phtml';

    protected $_tierPriceDefaultTemplate  = 'catalog/product/view/tierprices.phtml';

    protected $_priceBlockTypes = array();

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_item';

    /**
     * HTML Last category product
     * 
     * @return string (or false)
     */
    protected function _toHtml()
    {
        if (!is_array($this->categoryIds)) {
            $this->categoryIds = explode(',', $this->categoryIds);
        }

        if (count($this->categoryIds)) {
            return parent::_toHtml();
        }

        return FALSE;
    }

    /**
     * Detection last product
     * 
     * @return Mage_Catalog_Model_Product
     */
    protected function _getLastProduct()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', NULL, 'left')
            ->addAttributeToFilter('category_id', array('in' => $this->categoryIds))
            ->addAttributeToSelect(array('name','url_key','small_image'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        $collection->getSelect()->order('entity_id desc')->limit(1);

        return $collection->getFirstItem();
    }

    protected function _getLastProducts()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', NULL, 'left')
            ->addAttributeToFilter('category_id', array('in' => $this->categoryIds))
            ->addAttributeToSelect(array('name','url_key','small_image'))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        $collection->getSelect()->order('entity_id desc')->limit(4)->group('entity_id');

        return $collection;
    }

    /**
     * Get product category with max level
     *
     * @param Mage_Catalog_Model_Product
     * @return Mage_Catalog_Model_Category
     */
    protected function _getProductCategory($product)
    {
        return Mage::getModel('catalog/category')->load($product->getData('category_id'));
    }

    /**
     * Retrieve Product URL
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _getProductUrl($product)
    {
        $rewrite     = Mage::getModel('core/url_rewrite');
        $storeId     = $product->getStoreId();
        $idPath      = sprintf('product/%d', $product->getEntityId());
        $rewrite->setStoreId($storeId)->loadByIdPath($idPath);
        $routePath   = '';
        if ($rewrite->getId()) {
            $requestPath = $rewrite->getRequestPath();
            $product->setRequestPath($requestPath);
        } else {
            $product->setRequestPath(FALSE);
        }

        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = TRUE;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return Mage::getModel('core/url')->setStore($storeId)->getUrl($routePath, $routeParams);
    }

    protected function _getPriceBlock($productTypeId)
    {
        if (!isset($this->_priceBlock[$productTypeId])) {
            $block = $this->_block;
            if (isset($this->_priceBlockTypes[$productTypeId])) {
                if ($this->_priceBlockTypes[$productTypeId]['block'] != '') {
                    $block = $this->_priceBlockTypes[$productTypeId]['block'];
                }
            }

            $this->_priceBlock[$productTypeId] = $this->getLayout()->createBlock($block);
        }

        return $this->_priceBlock[$productTypeId];
    }

    protected function _getPriceBlockTemplate($productTypeId)
    {
        if (isset($this->_priceBlockTypes[$productTypeId])) {
            if ($this->_priceBlockTypes[$productTypeId]['template'] != '') {
                return $this->_priceBlockTypes[$productTypeId]['template'];
            }
        }

        return $this->_priceBlockDefaultTemplate;
    }


    /**
     * Prepares and returns block to render some product type
     *
     * @param string $productType
     * @return Mage_Core_Block_Template
     */
    public function _preparePriceRenderer($productType)
    {
        return $this->_getPriceBlock($productType)
            ->setTemplate($this->_getPriceBlockTemplate($productType))
            ->setUseLinkForAsLowAs($this->_useLinkForAsLowAs);
    }

    /**
     * Returns product price block html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtml($product, $displayMinimalPrice = FALSE, $idSuffix = '')
    {
        $type_id = $product->getTypeId();
        if (Mage::helper('catalog')->canApplyMsrp($product)) {
            $realPriceHtml = $this->_preparePriceRenderer($type_id)
                ->setProduct($product)
                ->setDisplayMinimalPrice($displayMinimalPrice)
                ->setIdSuffix($idSuffix)
                ->toHtml();
            $product->setAddToCartUrl($this->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }

        return $this->_preparePriceRenderer($type_id)
            ->setProduct($product)
            ->setDisplayMinimalPrice($displayMinimalPrice)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * Adding customized price template for product type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_priceBlockTypes[$type] = array(
                'block' => $block,
                'template' => $template
            );
        }
    }
}