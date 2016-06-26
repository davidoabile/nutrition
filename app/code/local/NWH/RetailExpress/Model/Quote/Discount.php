<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_RetailExpress_Model_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount {

    /**
     * Collect address discount amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    /*
   public function collect(Mage_Sales_Model_Quote_Address $address) {
        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $this->_calculator->reset($address);
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $couponCode = $quote->getCouponCode();
        $couponArray = explode(',', $couponCode);
        foreach ($couponArray as $couponCode) {
            if(empty($couponCode)) {
                continue;
            }
            $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $couponCode);
            $this->_calculator->initTotals($items, $address);

            $eventArgs = array(
                'website_id' => $store->getWebsiteId(),
                'customer_group_id' => $quote->getCustomerGroupId(),
                'coupon_code' => $couponCode,
            );

            $address->setDiscountDescription(array());
            $items = $this->_calculator->sortItemsByPriority($items);
            foreach ($items as $item) {
                if ($item->getNoDiscount()) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                } else {
                    if ($item->getParentItemId()) {
                        continue;
                    }

                    $eventArgs['item'] = $item;
                    Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            $this->_calculator->process($child);
                            $eventArgs['item'] = $child;
                            Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                            $this->_aggregateItemDiscount($child);
                        }
                    } else {
                        $this->_calculator->process($item);
                        $this->_aggregateItemDiscount($item);
                    }
                }
            }

            /**
             * process weee amount
             */
         /*   if (Mage::helper('weee')->isEnabled() && Mage::helper('weee')->isDiscounted($store)) {
                $this->_calculator->processWeeeAmount($address, $items);
            } */

            /**
             * Process shipping amount discount
             */
          /*  $address->setShippingDiscountAmount(0);
            $address->setBaseShippingDiscountAmount(0);
            if ($address->getShippingAmount()) {
                $this->_calculator->processShippingAmount($address);
                $this->_addAmount(-$address->getShippingDiscountAmount());
                $this->_addBaseAmount(-$address->getBaseShippingDiscountAmount());
            }

            $this->_calculator->prepareDescription($address);
        }

        return $this;
    }
    */

}
