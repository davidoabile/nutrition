<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Sales observer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Sales/Model/Observer.php';

class Excellence_Collection_Model_Observer extends Mage_Sales_Model_Observer {

    public function setDiscountCouponCode2(Varien_Event_Observer $observer) {

        $couponCode2 = $observer->getEvent()->getQuote()->getCouponCode2();
        if(empty($couponCode2)) {
            return;
        }
        $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode2, 'code');
        $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
        $ruleData = $oRule->getData();

        $flag = 1;
        if (isset($ruleData['product_ids'])) {
            $product_ids = $ruleData['product_ids'];
            foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $_item) {
                if ($product_ids == $_item->getProductId()) {
                    $flag = 1;
                }
            }
        }


        if ($flag) {
            $ruleSimpleAction = isset($ruleData['simple_action']) ? $ruleData['simple_action'] : null;
            $ruleDiscountAmount = isset($ruleData['discount_amount']) ? $ruleData['discount_amount'] : 0;


            $quote = $observer->getEvent()->getQuote();
            $quoteid = $quote->getId();

            $discountAmount = $ruleDiscountAmount;
            if ($quoteid) {
                if ($discountAmount > 0) {
                    $calc = Mage::getSingleton('tax/calculation');
                    $rates = $calc->getRatesForAllProductTaxClasses($calc->getRateRequest());
                    $rate = (float) end($rates) / 100;
                    $discountTax = ($ruleDiscountAmount * $rate);
                    $total = $quote->getBaseSubtotal();
                    $grandTotal = $quote->getBaseGrandTotal();

                    $quote->setSubtotal(0);
                    $quote->setBaseSubtotal(0);

                    $quote->setSubtotalWithDiscount(0);
                    $quote->setBaseSubtotalWithDiscount(0);

                    $quote->setGrandTotal(0);
                    $quote->setBaseGrandTotal(0);


                    $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                    foreach ($quote->getAllAddresses() as $address) {

                        $address->setSubtotal(0);
                        $address->setBaseSubtotal(0);

                        $address->setGrandTotal(0);
                        $address->setBaseGrandTotal(0);

                        $address->collectTotals();

                        $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                        $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

                        $quote->setSubtotalWithDiscount(
                                (float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                        );
                        $quote->setBaseSubtotalWithDiscount(
                                (float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                        );

                        $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                        $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

                        $quote->save();
                        if ($quote->getBaseGrandTotal() > 0) {
                            $quote->setGrandTotal($quote->getBaseGrandTotal() - $discountAmount)
                                    ->setBaseGrandTotal($quote->getBaseGrandTotal() - $discountAmount)
                                    ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                                    ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                                    ->save();
                        }

                        if ($address->getAddressType() == $canAddItems && $quote->getBaseGrandTotal() > 0) {
                            $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);
                           // $address->setBaseTaxAmount($address->getBaseTaxAmount() - $discountTax);
                           // $address->setTaxAmount($address->getTaxAmount() - $discountTax);
                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                //$address->setDiscountDescription($address->getDiscountDescription().', Gift Card -'.$couponCode2);
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } elseif ($quote->getBaseGrandTotal() > 0) {
                                $address->setDiscountAmount(-($discountAmount));
                                //$address->setDiscountDescription('Gift Card -'.$couponCode2);
                                $address->setBaseDiscountAmount(-($discountAmount));
                            }
                            $address->save();
                        }
                    }

                    foreach ($quote->getAllItems() as $item) {
                        $rat = $item->getPriceInclTax() / $total;
                        $ratdisc = $discountAmount * $rat;
                        $item->setDiscountAmount(($item->getDiscountAmount() + $ratdisc) * $item->getQty());
                        $item->setBaseDiscountAmount(($item->getBaseDiscountAmount() + $ratdisc) * $item->getQty())->save();
                    }
                }
            }
        } else {
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode2('')->save();
        }
    }

    public function setQuoteCanApplyMsrp(Varien_Event_Observer $observer) {

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if (Mage::helper('catalog')->isMsrpEnabled()) {
            foreach ($quote->getAllAddresses() as $adddress) {
                if ($adddress->getCanApplyMsrp()) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }
        $this->setDiscountCouponCode2($observer);
        $quote->setCanApplyMsrp($canApplyMsrp);
    }

}
