<?php

/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Model_Observer {

    public function onPredispatchCheckoutOnepageIndex(Varien_Event_Observer $observer) {
        if (!$this->_checkRule() || Mage::helper('aitcheckout')->isDisabled())
            return;
        $helper = Mage::helper('aitcheckout');
        $checkoutUrl = Mage::getUrl($helper->getCheckoutUrl(), array('_secure' => true));
        $observer->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $observer->getEvent()->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
    }

    public function onPredispatchCheckoutCartIndex(Varien_Event_Observer $observer) {
        if (!$this->_checkRule() || Mage::helper('aitcheckout')->isDisabled())
            return;

        $helper = Mage::helper('aitcheckout');
        $cartUrl = Mage::getUrl($helper->getCartUrl(), array('_secure' => true));
        if ($helper->isShowCartInCheckout() || $helper->isNeedRedirectToSecure()) {
            $observer->getEvent()->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $observer->getEvent()->getControllerAction()->getResponse()->setRedirect($cartUrl);
        }
    }

    private function _checkRule() {
        /* {#AITOC_COMMENT_END#}
          $iStoreId = Mage::app()->getStore()->getId();
          $iSiteId  = Mage::app()->getWebsite()->getId();
          $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckout')->getLicense()->getPerformer();
          $ruler     = $performer->getRuler();
          if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
          {
          return false;
          }
          {#AITOC_COMMENT_START#} */

        return true;
    }

    public function updateQuote(Varien_Event_Observer $observer) {
        $quote = $observer->getQuote();
        if ($quote instanceof Mage_Sales_Model_Quote) {
            $countryId = $quote->getBillingAddress()->getCountryId();
            if (empty($countryId)) {
                $quote->getBillingAddress()->setCountryId(Mage::helper('aitcheckout')->getDefaultCountry());
            }
        }
    }

    public function onCustomerLogin(Varien_Event_Observer $observer) {
        /**
         * Reset checkout method to avoid "The password cannot be empty" error
         * If checkout method was set to "register" before customer logged in
         */
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId()) {
            return;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$quote->getId()) {
            return;
        }

        $quote->setCheckoutMethod(null)
                ->save();
    }

    public function reloadShippingMethods($observer) {
        if ($observer->getBlock() instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save();
        }
    }

    public function setDefaultShippingMethod($observer) {
        if ($observer->getData('action') instanceof Mage_Checkout_CartController ||
                $observer->getData('action') instanceof Aitoc_Aitcheckout_CheckoutController) {
            Mage::helper('aitcheckout')->setDefaultShippingMethod();
        }
    }

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
            $ruleSimpleAction = $ruleData['simple_action'];
            $ruleDiscountAmount = $ruleData['discount_amount'];


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

                        $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                                ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                                ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                                ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                                ->save();

                        if ($address->getAddressType() == $canAddItems) {
                            $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);
                            $address->setBaseTaxAmount($address->getBaseTaxAmount() - $discountTax);
                            $address->setTaxAmount($address->getTaxAmount() - $discountTax);

                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                //$address->setDiscountDescription($address->getDiscountDescription().', Gift Card -'.$couponCode2);
                                $address->setDiscountDescription($address->getDiscountDescription() . ', GIFT CARD');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } else {
                                $address->setDiscountAmount(-($discountAmount));
                                //$address->setDiscountDescription('Gift Card -'.$couponCode2);
                                $address->setDiscountDescription('GIFT CARD');
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
