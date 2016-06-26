<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Model_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item {

    protected $_ruleId = null;

    public function getRuleId() {
        if (is_null($this->_ruleId)) {
            $buyRequest = $this->getBuyRequest();

            $this->_ruleId = isset($buyRequest['options']['ampromo_rule_id']) ? $buyRequest['options']['ampromo_rule_id'] : null;
        }

        return $this->_ruleId;
    }

    public function getIsFree() {
        if (Mage::app()->getStore()->isAdmin())
            return false;

        return $this->getRuleId() !== null;
    }

    public function setPrice($v) {

        if ($this->getIsFree()) {
            $v = 0;
             $this->setOriginalCustomPrice(0);
        }
        return parent::setPrice($v);
    }

    public function representProduct($product) {
        if (parent::representProduct($product)) {
            $option = $product->getCustomOption('info_buyRequest');
            $productBuyRequest = new Varien_Object($option ? unserialize($option->getValue()) : null);

            $currentBuyRequest = $this->getBuyRequest();

            $productIsFree = isset($productBuyRequest['options']['ampromo_rule_id']) ? $productBuyRequest['options']['ampromo_rule_id'] : null;
            $currentIsFree = isset($currentBuyRequest['options']['ampromo_rule_id']) ? $currentBuyRequest['options']['ampromo_rule_id'] : null;

            return $productIsFree === $currentIsFree;
        } else
            return false;
    }

    /**
     * Added for Magento <= 1.4 compatibility
     * @return Varien_Object
     */
    public function getBuyRequest() {
        $option = $this->getOptionByCode('info_buyRequest');
        $buyRequest = new Varien_Object($option ? unserialize($option->getValue()) : null);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
        $buyRequest->setOriginalQty($buyRequest->getQty())
                ->setQty($this->getQty() * 1);

        return $buyRequest;
    }

}
