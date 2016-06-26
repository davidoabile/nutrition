<?php
/**
 * @category    Ayasoftware
 * @package     Ayasoftware_SimpleProductPricing
 * @copyright   2015 Ayasoftware (http://www.ayasoftware.com)
 * @author      EL HASSAN MATAR <support@ayasoftware.com>
 */
class Ayasoftware_SimpleProductPricing_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Get Cheapest Child Price
     * @param type $product
     * @return $prices or false (if not a configurable product)
     */
    public function getCheapestChildPrice($product) {

        if ($product->getTypeId() != 'configurable') {
            return false;
        }
        $saveCache = false;
        $cache = Mage::app()->getCache();
        $cacheKey = $product->getId();
        
        if (intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')) > 0) {
            if ($cache->load("product_id_prices_" . $cacheKey)) {
                $product_id_prices_cache = unserialize($cache->load("product_id_prices_" . $cacheKey));
                return $product_id_prices_cache;
            } else {
                $saveCache = true;
            }
        }
        
        $productIds = array();
        $childProducts = $product->getTypeInstance(true)->getUsedProductCollection($product);
        $childProducts->addAttributeToSelect(array('msrp', 'price', 'special_price', 'status', 'special_from_date', 'special_to_date'));
        foreach ($childProducts as $childProduct) {
            if (!$childProduct->isSalable()) {
                if (!Mage::getStoreConfig('spp/setting/show')) {
                    continue;
                }
            }
            $finalPrice = $childProduct->getFinalPrice();
            if ($childProduct->getTierPrice()) {
                $tprices = array();
                foreach ($tierprices = $childProduct->getTierPrice() as $tierprice) {
                    $tprices[] = $tierprice['price'];
                }
            }
            if (!empty($tprices)) {
                $finalPrice = min($tprices);
            }
            Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $childProduct, 'qty' => 1));
            if ($childProduct->isSalable()) {
                $productIds[$childProduct->getId()] = array("finalPrice" => $childProduct->getFinalPrice(), "price" => $childProduct->getPrice());
            }
        }
        if (empty($productIds)) {
            return false;
        }
        $productCheapestId = array_search(min($productIds), $productIds);
        $productExpensiveId = array_search(max($productIds), $productIds);
        $prices = array();
        $prices["minChild"] = $productCheapestId;
        $prices["Min"] = array("finalPrice" => $productIds[$productCheapestId]['finalPrice'], 'price' => $productIds[$productCheapestId]['price']);
        $prices["Max"] = array("finalPrice" => $productIds[$productExpensiveId]['finalPrice'], 'price' => $productIds[$productExpensiveId]['price']);

        if (intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')) > 0 && $saveCache) {
            $cache->save(serialize($prices), "product_id_prices_" . $cacheKey, array("product_id_prices_cache"), intval(Mage::getStoreConfig('spp/setting/spp_cache_lifetime')));
        }
        return $prices;
    }

    public function applyRulesToProduct($product) {
        $rule = Mage::getModel("catalogrule/rule");
        return $rule->calcProductPriceRule($product, $product->getPrice());
    }

    public function canApplyTierPrice($product, $qty) {
        $tierPrice = $product->getTierPrice($qty);
        if (empty($tierPrice)) {
            return false;
        }
        $price = $product->getPrice();
        if ($tierPrice != $price) {
            return true;
        } else {
            return false;
        }
    }

    public function applyOptionsPrice($product, $finalPrice) {
        if ($optionIds = $product->getCustomOption('option_ids')) {
            $basePrice = $finalPrice;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                }
            }
        }
        return $finalPrice;
    }

}
