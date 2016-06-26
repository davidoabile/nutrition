<?php

class Moogento_CourierRules_Model_Rule extends Mage_Core_Model_Abstract
{
    const ANY_METHOD = '__any__';

    const CUSTOM_METHOD = '__custom__';
    const CONNECTOR_METHOD = '__connector__';

    protected $_connector;

    protected $_carrier;

    protected $_service;

    protected function _construct()
    {
        $this->_init('moogento_courierrules/rule');
    }

    protected function _loadConnectorInfo()
    {
        $method = $this->getCourierrulesMethod();
        $result = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($method);
        if(isset($result['connector'])) {
            $this->_connector = $result['connector'];
            $this->_carrier = $result['carrier'];
            $this->_service = $result['service'];
        }
        else {
            $this->_connector = false;
            $this->_carrier = false;
            $this->_service = false;
        }
    }

    public function getConnector()
    {
        if(is_null($this->_connector)) {
            $this->_loadConnectorInfo();
        }
        return $this->_connector;
    }

    public function getCarrier()
    {
        if(is_null($this->_carrier)) {
            $this->_loadConnectorInfo();
        }
        return $this->_carrier;
    }

    public function getService()
    {
        if(is_null($this->_service)) {
            $this->_loadConnectorInfo();
        }
        return $this->_service;
    }

    /**
     * @param $order Mage_Sales_Model_Order
     * @return bool
     */
    public function validate($order)
    {
        $this->setData('partial_match', false);
        if ($this->getScope() && $this->getScope() != 'default') {
            list($scope, $code) = explode('_', $this->getScope());
            $orderStore = $order->getStore();
            switch ($scope) {
                case 'website':
                    if ($orderStore->getWebsite()->getCode() != $code) {
                        return false;
                    }
                    break;
                case 'store':
                    if ($orderStore->getCode() != $code) {
                        return false;
                    }
                    break;
            }
        }

        $order_items = $order->getAllItems();
        
        $count=0;
        $discount=0;
        foreach($order_items as $val){
            if (is_null($val->getParentId())){
                $count += $val->getQtyOrdered();
                if ( ((float)$val->getDiscountAmount()>0) || ((float)$val->getRowTotal() == 0) ) $discount += $val->getQtyOrdered();
            }
        }

        if ($this->getShippingMethod() && $this->getShippingMethod() != self::ANY_METHOD) {
            if ($this->getShippingMethod() == self::CUSTOM_METHOD && trim($this->getCustomShippingMethod())) {
                if (Mage::getStoreConfigFlag('courierrules/settings/exact_match')) {
                    if (trim($this->getCustomShippingMethod()) != trim($order->getShippingDescription())) {
                        return false;
                    }
                } else {
                    if (strpos(trim($order->getShippingDescription()), trim($this->getCustomShippingMethod())) === false) {
                        return false;
                    }
                }
            } elseif ($this->getShippingMethod() != self::CUSTOM_METHOD && $order->getShippingMethod() != $this->getShippingMethod()) {
                return false;
            }
        }

        if ($this->getShippingZone()) {
            $zone = Mage::helper('moogento_courierrules')->getZoneById($this->getShippingZone());

            if ($zone && !$zone->validate($order)) {
                return false;
            }
        }

        $orderWeight = $order->getWeight();
        $orderShipping = $order->getShippingAmount();
        
        if ($this->getMinWeight() && (float)$this->getMinWeight() > $orderWeight) {
            return false;
        }

        if ($this->getMaxWeight() && $orderWeight > (float)$this->getMaxWeight()) {
            return false;
        }

        if ($this->getMinAmount() && $order->getGrandTotal() < (float)$this->getMinAmount()) {
            return false;
        }

        if ($this->getMaxAmount() && $order->getGrandTotal() > (float)$this->getMaxAmount()) {
            return false;
        }

        if (Mage::getStoreConfigFlag('courierrules/settings/shipping_cost_filter')){
            if ($this->getShippingCostFilterMin() && (int)$this->getShippingCostFilterMin() <= $orderShipping) {
                return false;
            }

            if ($this->getShippingCostFilterMax() && $orderShipping >= (int)$this->getShippingCostFilterMax()) {
                return false;
            }
        }
        
        if (Mage::getStoreConfigFlag('courierrules/settings/cost_filter')){
            $orderCost = 0;
            foreach ($order->getAllItems() as $itemId => $item){
                if(!$item->getIsVirtual() && !$item->getProduct()->isComposite()){
                    $_product = $item->getProduct();
                    $productCost = $_product->getCost();
                    $itemCost = !empty($productCost) ? $_product->getCost() : 0;
                    $orderCost += $itemCost * ($item->getQtyOrdered() - $item->getQtyCanceled());
                }
            }
            
            if ($this->getCostFilterMin() && (int)$this->getCostFilterMin() > $orderCost) {
                return false;
            }

            if ($this->getCostFilterMax() && $orderCost > (int)$this->getCostFilterMax()) {
                return false;
            }
        }
        
        if (Mage::getStoreConfigFlag('courierrules/settings/quantity_all_items')){
            if ($this->getQuantityAllItems() && ((int)$this->getQuantityAllItems() != $count)) {
                return false;
            }
        }
        
        if (Mage::getStoreConfigFlag('courierrules/settings/quantity_free_discount_items')){
            if ($this->getQuantityFreeDiscountItems() && ((int)$this->getQuantityFreeDiscountItems() != $discount)) {
                return false;
            }
        }
        
        $result_bool = true;
        $set_data = false;

        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
            $set_data = $this->_checkValidateProductAttribute($order, "product_attribute");
            if(!$set_data) return false;
        }
        
        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute2')) {
            $set_data = $this->_checkValidateProductAttribute($order, "product_attribute2");
            if(!$set_data) return false;
        }
        
        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute3')) {
            $set_data = $this->_checkValidateProductAttribute($order, "product_attribute3");
            if(!$set_data) return false;
        }
        
        if($set_data && $set_data != count($order->getAllItems())){
            $this->setData('partial_match', true);
        }

        $connector = $this->getConnector();
        if($connector) {
            return $connector->validateOrder($order, $this);
        }

        if ($this->getCourierrulesMethod() == self::CONNECTOR_METHOD) {
            return Mage::helper('moogento_courierrules/connector')->loadSuggestions($order);
        }
        return true;
    }

    protected function _checkValidateProductAttribute($order, $product_attribute)
    {
        $attributeCode = Mage::getStoreConfig('courierrules/settings/'.$product_attribute);
        $isRange = Mage::getStoreConfig('courierrules/settings/use_product_attribute_range'.str_replace("product_attribute", "", $product_attribute));
        $isSum = Mage::getStoreConfig('courierrules/settings/use_product_attribute_sum'.str_replace("product_attribute", "", $product_attribute));

        $matched = 0;
        $productAttributeSum = 0;

        $items = $order->getAllItems();
        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if(!$isRange){
                if ($this->getData($product_attribute)) {
                    if (Mage::helper('moogento_courierrules')->isProductAttributeMultiple($product_attribute)) {
                        $ruleData       = $this->getData($product_attribute);
                        $productData    = $product->getData($attributeCode);
                        $matchedOptions = 0;
                        foreach ($ruleData as $fromRule) {
                            foreach ($productData as $fromProduct) {
                                if ($fromRule == $fromProduct) {
                                    $matchedOptions++;
                                }
                            }
                        }
                        if (count($ruleData) == $matchedOptions) {
                            $matched++;
                        }
                    } else {
                        if ($this->getData($product_attribute) == $product->getData($attributeCode)) {
                            $matched++;
                        }
                    }
                } else {
                    $matched++;
                }
            } else {
                $min = (float)$this->getData("min_".$product_attribute);
                $max = (float)$this->getData("max_".$product_attribute);
                if (($min < (float)$product->getData($attributeCode) || !$min) &&
                    ((float)$product->getData($attributeCode) <= $max || !$max)) {
                        $matched++;
                }
            }
            if (!Mage::helper('moogento_courierrules')->isProductAttributeMultiple($product_attribute)) {
                if(Mage::helper('moogento_courierrules')->isProductAttributeNumeric($product_attribute)) {
                    $productAttributeSum += $product->getData($attributeCode);
                }
            }
        }

        if($isSum){
            if($isRange){
                $min = (float)$this->getData("min_".$product_attribute);
                $max = (float)$this->getData("max_".$product_attribute);
                if (($min < (float)$productAttributeSum || !$min) &&
                    ((float)$productAttributeSum <= $max || !$max)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (!$this->getData($product_attribute) || $this->getData($product_attribute) == $productAttributeSum) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return $matched;
        }
    }
    
    public function useTracking()
    {
        $tracking = Mage::getModel('moogento_courierrules/tracking')->load($this->getTrackingId());
        if ($tracking->getId()) {
            $code = $tracking->useCode();
            return $code;
        }

        return null;
    }
} 