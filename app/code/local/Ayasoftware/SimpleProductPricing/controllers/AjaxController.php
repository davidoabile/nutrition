<?php

/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
require_once 'Mage/Catalog/controllers/ProductController.php';

class Ayasoftware_SimpleProductPricing_AjaxController extends Mage_Catalog_ProductController {

    public function coAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            $this->loadLayout(false);
            $this->renderLayout();
        }
    }

    public function productattributesAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            $this->loadLayout(false);
            $this->renderLayout();
        }
    }

    public function imageAction() {
        $product = $this->_initProduct();
        $image = $product->getImage();
        //override child image with the parent's image
        if (empty($image) || $image === 'no_selection') {
           $product = $this->_initProduct((int) $this->getRequest()->getParam('pid'));
        }
        if (!empty($product)) {
            $this->loadLayout(false);
            $this->renderLayout();
        }
    }

    public function galleryAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            #$this->_initProductLayout($product);
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    protected function _initProduct($overrideProductId = 0) {
        $productId = (int) $this->getRequest()->getParam('id');
        $parentId = (int) $this->getRequest()->getParam('pid');

        if ($productId > 0 && $overrideProductId === 0) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if (!$product->getId()) {
                return false;
            }
            Mage::register('current_product', $product);
            Mage::register('product', $product);

            return $product;
        }

        $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($parentId);
        if (!$product->getId()) {
            return false;
        }
        if ($overrideProductId > 0) {
            Mage::unregister('current_product');
            Mage::unregister('product');
        }
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        return $product;
    }

}
