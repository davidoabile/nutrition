<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_CartController extends Mage_Core_Controller_Front_Action {

    //protected $redirectTo = 'checkout/cart';
    protected $redirectTo = '/aitcheckout/checkout';
    
    public function freegiftAction() {
        $free = explode(",", Mage::helper('ampromo/bonus')->getFreeSku());
        $free_item = 0;
        foreach ($free as $fr) {
            if (!empty($fr))
                $free_item = 1;
        }
        if ($free_item == 0) {
            $this->_redirect($this->redirectTo);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function bonusAction() {
        $bonus = explode(",", Mage::helper('ampromo/bonus')->getBonusSku());
        $bonus_item = 0;
        foreach ($bonus as $bn) {
            if (!empty($bn))
                $bonus_item = 1;
        }
         if ($bonus_item > 0) {
            $products = Mage::helper('ampromo')->getNewItems();
            $bonus_item = 0;
            foreach ($products as $k => $product) {
                if (in_array($product->getSku(), $bonus)){
                    $bonus_item = 1;
                    break;
                }
            }
        }
        if ($bonus_item == 0) {
            $this->_redirect($this->redirectTo);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function freegiftcheckoutAction() {
        $free = explode(",", Mage::helper('ampromo/bonus')->getFreeSku());
        $free_item = 0;
        foreach ($free as $fr) {
            if (!empty($fr)) {
                $free_item = 1;
                break;
            }
        }
       
        if ($free_item === 0) {
            //$this->_redirect("/ampromo/cart/bonuscheckout");
            $this->getResponse()->setRedirect("/ampromo/cart/bonuscheckout");
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function offersAction() {
       
        $this->loadLayout();
        $this->renderLayout();
    }
    

    public function bonuscheckoutAction() {
        $bonus = explode(",", Mage::helper('ampromo/bonus')->getBonusSku());
        $bonus_item = 0;
        foreach ($bonus as $bn) {
            if (!empty($bn)) {
                $bonus_item = 1;
                break;
            }
        }
        
        if ($bonus_item > 0) {
            $products = Mage::helper('ampromo')->getNewItems();
            $bonus_item = 0;
            foreach ($products as $k => $product) {
                if (in_array($product->getSku(), $bonus)){
                    $bonus_item = 1;
                    break;
                }
            }
        }
        if ($bonus_item == 0) {
            /* $this->_redirect("checkout/onepage"); */
           // $this->_redirect($this->redirectTo);
             $this->getResponse()->setRedirect($this->redirectTo);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function updateAction() {
        $productId = $this->getRequest()->getParam('product_id');

        $product = Mage::getModel('catalog/product')->load($productId);
        
        if ($product->getId()) {
            $limits = Mage::getSingleton('ampromo/registry')->getLimits();

            $sku = $product->getSku();
            
            $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
            $addOneRule = false;
            if (!$addAllRule) {
                foreach ($limits['_groups'] as $ruleId => $rule) {
                    if (in_array($sku, $rule['sku'])) {
                        $addOneRule = $ruleId;
                    }
                }
            }
            if ($addAllRule || $addOneRule) {
                $super = $this->getRequest()->getParam('super_attributes');
                $options = $this->getRequest()->getParam('options');
                $bundleOptions = $this->getRequest()->getParam('bundle_option');

                /* To compatibility amgiftcard module */
                $amgiftcardValues = array();
                if ($product->getTypeId() == 'amgiftcard') {
                    $amgiftcardFields = array_keys(Mage::helper('amgiftcard')->getAmGiftCardFields());
                    foreach ($amgiftcardFields as $amgiftcardField) {
                        if ($this->getRequest()->getParam($amgiftcardField)) {
                            $amgiftcardValues[$amgiftcardField] = $this->getRequest()->getParam($amgiftcardField);
                        }
                    }
                }

                Mage::helper('ampromo')->addProduct($product, $super, $options, $bundleOptions, $addOneRule, $amgiftcardValues);
            }
        }

        $referer = $this->getRequest()->getPost('referer');

        $referer = Mage::helper('core')->urlDecode($referer);

        $urlModel = Mage::getModel('core/url');

        if (method_exists($urlModel, 'getRebuiltUrl')) { // Fix for old versions
            $referer = $urlModel->getRebuiltUrl($referer);
        }

        $this->getResponse()->setRedirect('/ampromo/cart/freegiftcheckout');
    }

}
