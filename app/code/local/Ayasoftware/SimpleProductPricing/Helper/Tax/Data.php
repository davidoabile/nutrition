<?php
/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @copyright   2015 Ayasoftware (http://www.ayasoftware.com)
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Helper_Tax_Data extends Mage_Tax_Helper_Data {

    /**
     * Display special price if one of the associated products is on sale
     * @param type $product
     * @param type $price
     * @param type $includingTax
     * @param type $shippingAddress
     * @param type $billingAddress
     * @param type $ctc
     * @param type $store
     * @param type $priceIncludesTax
     * @param type $roundPrice
     * @return type $price
     */
    public function getPrice($product, $price, $includingTax = null, $shippingAddress = null, $billingAddress = null, $ctc = null, $store = null, $priceIncludesTax = null, $roundPrice = true) {

        $price = parent::getPrice($product, $price, $includingTax, $shippingAddress, $billingAddress, $ctc, $store, $priceIncludesTax, $roundPrice);
        if ( $prices = Mage::helper('spp')->getCheapestChildPrice($product)) {
            $currentItem = Mage::getModel('catalog/product')->load($prices['minChild']);
            $product->setPrice($currentItem->getMsrp());
            //$product->setPrice($prices['Min']['price']);
             $product->setFinalPrice($currentItem->getFinalPrice());
        }
        return $price;
    }

}

