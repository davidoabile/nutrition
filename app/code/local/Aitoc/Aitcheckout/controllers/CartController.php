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
class Aitoc_Aitcheckout_CartController extends Aitoc_Aitcheckout_Controller_Action {

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    protected function _ajaxRedirectResponse() {
        $this->getResponse()
                ->setHeader('HTTP/1.1', '403 Session Expired')
                ->setHeader('Login-Required', 'true')
                ->sendResponse();
        return $this;
    }

    protected function _expireAjax() {
        if (!$this->_getQuote()->hasItems()
        //|| $this->_getOnepage()->getQuote()->getHasError()
        //|| $this->_getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $currentStep = $data['step'];
            if (!$this->_getQuote()->getItemsCount()) {
                $this->getResponse()
                        ->setBody(
                                Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                );
                return;
            }
            $couponCode = (string) $this->getRequest()->getPost('coupon_code', '');

            if (strtolower($couponCode) === strtolower(Mage::getStoreConfig('nwh_retailexpress/newsletter/code'))) {
                $result = array('error' => -1, 'message' => Mage::helper('aitcheckout')->__('Coupon code not supported'));
            } else {
                if ($data['remove_coupon'] == 1) {
                    $couponCode = '';
                }
                $oldCouponCode = $this->_getQuote()->getCouponCode();
                if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                    $this->getResponse()
                            ->setBody(
                                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                    );
                    return;
                }

                $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                        ->collectTotals()
                        ->save();

                if ($couponCode) {
                    if ($couponCode == $this->_getQuote()->getCouponCode()) {
                        $result = array('error' => 0, 'message' => Mage::helper('aitcheckout')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
                    } else {
                        $result = array('error' => -1, 'message' => Mage::helper('checkout')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
                    }
                } else {
                    $result = array('error' => 1, 'message' => Mage::helper('aitcheckout')->__('Coupon code was canceled.'));
                }
            }

            // $errormsg = '';
            if ($result['error'] == -1) {
                $result['message'] = '<div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important; background-color: rgb(240, 240, 240); color:red;">' . $result['message'] . '</div>';
            } else if ($result['error'] == 0) {
                $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important; background-color: rgb(240, 240, 240); color:green;">' . $result['message'] . '</div>';
            } else if ($result['error'] == 1) {

                $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important; background-color: rgb(240, 240, 240); color:red;">' . $result['message'] . '</div>';
            }

            Mage::getSingleton('customer/session')->setCouponmessage($result['message']);

            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
            );
        }
    }

    public function giftcardPostAction() {
        if ($this->_expireAjax()) {
            return;
        }
        $data = $this->getRequest()->getPost();
        $currentStep = $data['step'];
        if (!$this->_getQuote()->getItemsCount()) {
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
            return;
        }
        $couponCode = (string) $this->getRequest()->getPost('giftcard_code', '');
        //$couponCode = (string) $this->getRequest()->getParam('giftcoupon');
        if ($this->getRequest()->getPost('remove_giftcard', '') == 1) {
            try {
                Mage::helper('aw_giftcard/totals')->removeCardFromQuote(trim($couponCode));
                Mage::getSingleton('checkout/session')->addSuccess(
                        $this->__('Gift Card "%s" has been removed.', Mage::helper('core')->escapeHtml($couponCode))
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot remove gift card.'));
            }
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode2();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
            return;
        }

        $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->_getQuote()->setCouponCode2(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
        if ($couponCode) {
            try {
                $giftcardModel = $this->_initCard();
                Mage::helper('aw_giftcard/totals')->addCardToQuote($giftcardModel);
                Mage::getSingleton('checkout/session')->addSuccess(
                        $this->__('Gift Card "%s" has been added.', Mage::helper('core')->escapeHtml($giftcardModel->getCode()))
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__($e->getMessage()));
            }
            if ($couponCode == $this->_getQuote()->getCouponCode2()) {
                $result = array('error' => 0, 'message' => Mage::helper('aitcheckout')->__('Gift Card number "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
            } else {
                $result = array('error' => -1, 'message' => Mage::helper('checkout')->__('Gift Card number "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
            }
        } else {
            $result = array('error' => 1, 'message' => Mage::helper('aitcheckout')->__('Gift Card number was canceled.'));
        }



        // $errormsg = '';
        if ($result['error'] == -1) {
            $result['message'] = '<div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important; color:red;">' . $result['message'] . '</div>';
        } else if ($result['error'] == 0) {
            $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important;color:green;">' . $result['message'] . '</div>';
        } else if ($result['error'] == 1) {

            $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important;color:red;">' . $result['message'] . '</div>';
        }
        Mage::getSingleton('customer/session')->setGiftmessage($result['message']);

        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        $this->getResponse()
                ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
        );
    }

    protected function _initCard() {
        $giftcardCode = (string) $this->getRequest()->getPost('giftcard_code', null);
        Mage::log($giftcardCode, Zend_Log::DEBUG, 'bi_debug3.log');
        if (null === $giftcardCode) {
            throw new Exception($this->__('Please enter gift card code.'));
        }

        $giftcardModel = Mage::getModel('aw_giftcard/giftcard')->loadByCode(trim($giftcardCode));
        if (null === $giftcardModel->getId()) {
            throw new Exception(
            $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftcardCode))
            );
        }

        if ($giftcardModel->isValidForRedeem()) {
            Mage::register('current_giftcard', $giftcardModel, true);
        }
        return $giftcardModel;
    }

    /**
     * Update shoping cart data action
     */
    public function updateItemOptionsAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            try {
                $itemId = (int) $this->getRequest()->getParam('id');
                $productId = $this->_getQuote()->getItemById($itemId)->getProduct()->getId();
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $increment = $stockItem->getQtyIncrements() ? $stockItem->getQtyIncrements() : 1;
                $sign = $this->getRequest()->getParam('sign');
                $data = $this->getRequest()->getPost();
                $cartData = $data['cart'];
                $currentStep = $data['step'];
                if (is_array($cartData)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $cartData[$itemId]['qty'] = $filter->filter($cartData[$itemId]['qty'] + $sign * $increment);

                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                        $cart->getQuote()->setCustomerId(null);
                    }
                    $this->_getQuote()->unsetData('messages');
                    if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.4.2')) {
                        $cartData = $cart->suggestItemsQty($cartData);
                    }
                    $oldQty = $this->_getQuote()->getItemById($itemId)->getQty();

                    $cart->updateItems($cartData);
                    if (Mage::helper('aitcheckout')->isErrorQuoteItemQty()) {
                        //restoring old qty in cart
                        $cartData[$itemId]['qty'] = $oldQty;
                        $cart->updateItems($cartData);
                        //don't allow to save quote and it's items, they can't be changed now
                        if (method_exists($this->_getQuote(), 'preventSaving')) {
                            $this->_getQuote()->preventSaving();
                        }
                        $message = Mage::helper('aitcheckout')->getLastErrorMessage();
                        //for lower magento version error message can be duplicated, so we update it with our one
                        if (!$message || version_compare(Mage::getVersion(), '1.6.0.0', '<'))
                            $message = Mage::helper('aitcheckout')->__('Cannot update the item.');
                        Mage::throwException($message);
                    }
                    $cart->save();
                }
                if ($this->_expireAjax()) {
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                if ($this->_getCart()->getCheckoutSession()->getUseNotice(true)) {
                    $this->_getCart()->getCheckoutSession()->addNotice($e->getMessage());
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->_getCart()->getCheckoutSession()->addError($message);
                    }
                }
            } catch (Exception $e) {
                $this->_getCart()->getCheckoutSession()->addException($e, Mage::helper('aitcheckout')->__('Cannot update the item.'));
                Mage::logException($e);
            }
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
        }
    }

    public function updatePostAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            try {
                $currentStep = $this->getRequest()->getPost('step');
                $cartData = $this->getRequest()->getParam('cart');
                $oldData = $cartData;
                if (is_array($cartData)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    foreach ($cartData as $index => $data) {
                        if (isset($data['qty'])) {
                            $cartData[$index]['qty'] = $filter->filter($data['qty']);
                            $oldData[$index]['qty'] = $this->_getQuote()->getItemById($index)->getQty();
                        }
                    }
                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                        $this->_getQuote()->setCustomerId(null);
                    }
                    $this->_getQuote()->unsetData('messages');
                    if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.4.2')) {
                        $cartData = $cart->suggestItemsQty($cartData);
                    }

                    $cart->updateItems($cartData);
                    if (Mage::helper('aitcheckout')->isErrorQuoteItemQty()) {
                        //restoring old qty in cart
                        $cart->updateItems($oldData);
                        //don't allow to save quote and it's items, they can't be changed now
                        if (method_exists($this->_getQuote(), 'preventSaving')) {
                            $this->_getQuote()->preventSaving();
                        }
                        $message = Mage::helper('aitcheckout')->getLastErrorMessage();
                        //for lower magento version error message can be duplicated, so we update it with our one
                        if (!$message || version_compare(Mage::getVersion(), '1.6.0.0', '<'))
                            $message = Mage::helper('aitcheckout')->__('Cannot update the item.');
                        Mage::throwException($message);
                    }
                    $cart->save();
                }
                if ($this->_expireAjax()) {
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getCart()->getCheckoutSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getCart()->getCheckoutSession()->addException($e, Mage::helper('aitcheckout')->__('Cannot update shopping cart.'));
                Mage::logException($e);
            }
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $currentStep = $this->getRequest()->getPost('step');
            $id = (int) $this->getRequest()->getParam('id');
            if ($id) {
                $this->_getCart()->removeItem($id)
                        ->save();
            }
            if ($this->_expireAjax()) {
                return;
            }
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
        }
    }

    public function addGiftAction() {

        $productId = $this->getRequest()->getParam("id");
        $storeid = Mage::app()->getStore()->getStoreId();
        $_product = Mage::getModel('catalog/product')->load($productId);
        $_product->setSpecialPrice("0");
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $_product->save();
        Mage::app()->setCurrentStore($storeid);
        $_product = Mage::getModel('catalog/product')->load($productId);
        $cart = Mage::getModel('checkout/cart');

        //Check price before add Gift
        $quote = $cart->getQuote();
        $grandTotal = $quote->getGrandTotal();

        $configTotal = (float) Mage::getStoreConfig('gifts/settings/total_amount');
        $numberGift = Mage::helper("bonusoffers/gifts")->numberItemInCart();
        $cart->init();
        $qty = 1;
        if ($numberGift > 0) {
            $stillPrice = ($grandTotal - ($numberGift * $configTotal)) - $configTotal;
            if ($stillPrice >= 0) {
                $cart->addProduct($_product, array('qty' => $qty));
            } else {
                $floorTotal = $grandTotal - floor($grandTotal);
                $per = floor($grandTotal) % $configTotal;
                $per = $per + $floorTotal;
                $spend = $configTotal - $per;
                $spend = Mage::helper('core')->currency($spend, true, false);
                Mage::getSingleton('core/session')->addError("SPEND {$spend} MORE TO GET ANOTHER FREE GIFT");
            }
        } else {
            if ($grandTotal >= $configTotal) {
                $cart->addProduct($_product, array('qty' => $qty));
            } else {
                $floorTotal = $grandTotal - floor($grandTotal);
                $per = floor($grandTotal) % $configTotal;
                $per = $per + $floorTotal;
                $spend = $configTotal - $per;
                $spend = Mage::helper('core')->currency($spend, true, false);
                Mage::getSingleton('core/session')->addError("SPEND {$spend} MORE TO GET ANOTHER FREE GIFT");
            }
        }
        //End check
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        $this->_redirectUrl(Mage::helper('aitcheckout')->getCheckoutUrl(), array('_secure' => true));
    }

    public function addGiftAjaxAction() {
        $productId = $this->getRequest()->getParam("id");
        $_product = Mage::getModel('catalog/product')->load($productId);
        $_product->setSpecialPrice("0");
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $_product->save();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
        $cart = Mage::getModel('checkout/cart');

        //Check price before add Gift
        $quote = $cart->getQuote();
        $grandTotal = $quote->getGrandTotal();

        $configTotal = (float) Mage::getStoreConfig('gifts/settings/total_amount');
        $numberGift = Mage::helper("bonusoffers/gifts")->numberItemInCart();
        $cart->init();
        $qty = ($grandTotal - $grandTotal % $configTotal) / $configTotal;
        if ($numberGift > 0) {
            $stillPrice = ($grandTotal - ($numberGift * $configTotal)) - $configTotal;
            if ($stillPrice >= 0) {
                $cart->addProduct($_product, array('qty' => $qty));
            } else {
                $floorTotal = $grandTotal - floor($grandTotal);
                $per = floor($grandTotal) % $configTotal;
                $per = $per + $floorTotal;
                $spend = $configTotal - $per;
                $spend = Mage::helper('core')->currency($spend, true, false);
                Mage::getSingleton('core/session')->addError("SPEND {$spend} MORE TO GET ANOTHER FREE GIFT");
            }
        } else {
            if ($grandTotal >= $configTotal) {
                $cart->addProduct($_product, array('qty' => $qty));
            } else {
                $floorTotal = $grandTotal - floor($grandTotal);
                $per = floor($grandTotal) % $configTotal;
                $per = $per + $floorTotal;
                $spend = $configTotal - $per;
                $spend = Mage::helper('core')->currency($spend, true, false);
                Mage::getSingleton('core/session')->addError("SPEND {$spend} MORE TO GET ANOTHER FREE GIFT");
            }
        }
        //End check
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        //echo Mage::getUrl("checkout/cart/index");
        echo Mage::helper('aitcheckout')->getCheckoutUrl(), array('_secure' => true);
        return;
    }

    public function addProductAction() {
        $productId = $this->getRequest()->getPost("id");
        $qty = '1'; // Replace qty with your qty
        $_product = Mage::getModel('catalog/product')->load($productId);
        $cart = Mage::getModel('checkout/cart');
        $cart->init();
        $cart->addProduct($_product, array('qty' => $qty));
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        echo $productId;
    }

    public function removeProductAction() {

        $id = $this->getRequest()->getPost("id");
        $cartHelper = Mage::helper('checkout/cart');
        $items = $cartHelper->getCart()->getItems();
        foreach ($items as $item):
            if ($item->getProduct()->getId() == $id):
                $itemId = $item->getItemId();
                $cartHelper->getCart()->removeItem($itemId)->save();
                break;
            endif;
        endforeach;
    }

    public function addInsuranceAction() {
        Mage::getSingleton("core/session")->setInsuranceAdd(1);
        //$this->_redirectUrl(Mage::getUrl("checkout/cart/index"));
    }

    public function removeInsuranceAction() {
        Mage::getSingleton("core/session")->setInsuranceAdd(0);
        //$this->_redirectUrl(Mage::getUrl("checkout/cart/index"));
    }

    public function customcouponPostAction() {
        if ($this->_expireAjax()) {
            return;
        }

        $data = $this->getRequest()->getPost();
        $currentStep = $data['step'];
        if (!$this->_getQuote()->getItemsCount()) {
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
            return;
        }
        //$couponCode = (string) $this->getRequest()->getPost('coupon_code2', '');
        $couponCode = (string) $this->getRequest()->getParam('giftcoupon');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode2();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
            return;
        }

        $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->_getQuote()->setCouponCode2(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

        if ($couponCode) {
            if ($couponCode == $this->_getQuote()->getCouponCode2()) {
                $result = array('error' => 0, 'message' => Mage::helper('aitcheckout')->__('Gift Card number "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
            } else {
                $result = array('error' => -1, 'message' => Mage::helper('checkout')->__('Gift Card number "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
            }
        } else {
            $result = array('error' => 1, 'message' => Mage::helper('aitcheckout')->__('Gift Card number was canceled.'));
        }



        // $errormsg = '';
        if ($result['error'] == -1) {
            $result['message'] = '<div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important; color:red;">' . $result['message'] . '</div>';
        } else if ($result['error'] == 0) {
            $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important;color:green;">' . $result['message'] . '</div>';
        } else if ($result['error'] == 1) {

            $result['message'] = '<div <div style="font-size: 13px; padding-bottom: 8px;border: medium none ! important;color:red;">' . $result['message'] . '</div>';
        }


        Mage::getSingleton('customer/session')->setGiftmessage($result['message']);

        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        $this->getResponse()
                ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
        );
    }

}
