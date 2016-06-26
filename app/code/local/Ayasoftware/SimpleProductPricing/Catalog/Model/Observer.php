<?php

/**
 * Modify price using events.
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Catalog_Model_Observer extends Mage_Core_Model_Abstract {

    /**
     * Update all cart items - if price change
     * @param Varien_Event_Observer $observer
     */
    public function updateSimpleProductPricing(Varien_Event_Observer $observer) {
        foreach (Mage::getModel("checkout/cart")->getItems() as $item /* @var $item Mage_Sales_Model_Quote_Item */) {
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
                if (!$item->getProduct()->isConfigurable()) {
                    return;
                }
                $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $simple = Mage::getModel('catalog/product')->load($item->getProduct()->getIdBySku($productOptions['simple_sku']));
                $simplePrice = $simple->getFinalPrice();
                if ($simple->getCustomerGroupId()) {
                    $simplePrice = $simple->getGroupPrice();
                }
                if ($simple->getTierPrice($item->getQty())) {
                    $simplePrice = min($simple->getTierPrice($item->getQty()), $simplePrice);
                }
                // if simple product has a special price, then use the
                // minimum of the previous price and special price
                if ($simple->special_price) {
                    $simplePrice = min($simple->getFinalPrice(), $simplePrice);
                }
                if (Mage::helper('spp')->applyRulesToProduct($simple)) {
                    $rulePrice = min(Mage::helper('spp')->applyRulesToProduct($simple), $simplePrice);
                    if (Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $rulePrice)) {
                        $simplePrice = Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $rulePrice);
                    }
                } else {
                    if (Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $simplePrice)) {
                        $simplePrice = Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $simplePrice);
                    }
                }
                $item->setCustomPrice($simplePrice);
                $item->setOriginalCustomPrice($simplePrice);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }

    /**
     * Update the  price of the product in cart based on Ordered Options
     * @param Varien_Event_Observer $obs
     */
    public function useSimpleProductPricing(Varien_Event_Observer $obs) {
        $item = $obs->getQuoteItem();
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
            if (!$item->getProduct()->isConfigurable()) {
                return;
            }
            $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $simple = Mage::getModel('catalog/product')->load($item->getProduct()->getIdBySku($productOptions['simple_sku']));
            $simplePrice = $simple->getFinalPrice();
            if ($simple->getCustomerGroupId()) {
                $simplePrice = $simple->getGroupPrice();
            }
            if ($simple->getTierPrice($item->getQty())) {
                $simplePrice = min($simple->getTierPrice($item->getQty()), $simplePrice);
            }

            // if simple product has a special price, then use the
            // minimum of the previous price and special price
            if ($simple->special_price) {
                $simplePrice = min($simple->getFinalPrice(), $simplePrice);
            }
            if (Mage::helper('spp')->applyRulesToProduct($simple)) {
                $rulePrice = min(Mage::helper('spp')->applyRulesToProduct($simple), $simplePrice);
                if (Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $rulePrice)) {
                    $simplePrice = Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $rulePrice);
                }
            } else {
                if (Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $simplePrice)) {
                    $simplePrice = Mage::helper('spp')->applyOptionsPrice($item->getProduct(), $simplePrice);
                }
            }
            $item->setCustomPrice($simplePrice);
            $item->setOriginalCustomPrice($simplePrice);
            $item->getProduct()->setIsSuperMode(true);
        }
    }

}
