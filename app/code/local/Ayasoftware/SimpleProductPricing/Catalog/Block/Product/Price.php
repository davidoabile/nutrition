<?php

class Ayasoftware_SimpleProductPricing_Catalog_Block_Product_Price extends Mage_Catalog_Block_Product_Price {

    public function getDisplayMinimalPrice() {
        if (!Mage::getStoreConfig('spp/setting/enableModule')) {
            return parent::getDisplayMinimalPrice();
        }
        $product = $this->getProduct();
        if (is_object($product) && $product->isConfigurable()) {
            return false;
        }
    }
    public function _toHtml() {
        if (!Mage::getStoreConfig('spp/setting/enableModule')) {
            return parent::_toHtml();
        }
        if (!Mage::getStoreConfig('spp/details/showfromprice')) {
            return parent::_toHtml();
        }
        $htmlToInsertAfter = '<div class="price-box">';
        if ($this->getTemplate() == 'catalog/product/price.phtml') {
            $product = $this->getProduct();
            $prices = Mage::helper('spp')->getCheapestChildPrice($product);
            if (is_object($product) && $product->isConfigurable()) {
                $extraHtml = '<span class="label" id="configurable-price-from-'
                        . $product->getId()
                        . $this->getIdSuffix()
                        . '"><span class="configurable-price-from-label">';
                if ($prices['Min']['finalPrice'] != $prices['Max']['finalPrice']) {
                    $extraHtml .= $this->__('From:');
                }
                $extraHtml .= '</span></span>';
                $priceHtml = parent::_toHtml();
                #manually insert extra html needed by the extension into the normal price html
                return substr_replace($priceHtml, $extraHtml, strpos($priceHtml, $htmlToInsertAfter) + strlen($htmlToInsertAfter), 0);
            }
        }
        return parent::_toHtml();
    }

}