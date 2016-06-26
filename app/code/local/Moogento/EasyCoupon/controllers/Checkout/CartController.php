<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class  Moogento_EasyCoupon_Checkout_CartController extends Mage_Checkout_CartController
{
    # Overloaded indexAction
	public function indexAction()
	{
		$cart = $this->_getCart();
		
		if ($cart->getQuote()->getItemsCount()) {
			
			$cart->init();
			
			$session = Mage::getSingleton('customer/session');
			$coreSession = Mage::getSingleton('core/session');
			$couponCodes = $session->getCouponCodes();
			$isCouponAdded = $session->getIsCouponAdded();
			$isRemove = $session->getRemoveCouponCodes();
			
			if($couponCodes)
			{
				if(($isCouponAdded !=1) || ($isRemove==1))
				{
					$this->couponAuto($couponCodes,$isRemove);
				}
			}
			$cart->save();

			if (!$this->_getQuote()->validateMinimumAmount()) {
				$minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
					->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

				$warning = Mage::getStoreConfig('sales/minimum_order/description')
					? Mage::getStoreConfig('sales/minimum_order/description')
					: Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

				$cart->getCheckoutSession()->addNotice($warning);
			}
		}

		// Compose array of messages to add
		$messages = array();
		foreach ($cart->getQuote()->getMessages() as $message) {
			if ($message) {
				// Escape HTML entities in quote message to prevent XSS
				$message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
				$messages[] = $message;
			}
		}
		$cart->getCheckoutSession()->addUniqueMessages($messages);

		/**
		 * if customer enteres shopping cart we should mark quote
		 * as modified bc he can has checkout page in another window.
		 */
		$this->_getSession()->setCartWasUpdated(true);
		
		
		
		Varien_Profiler::start(__METHOD__ . 'cart_display');
		$this
			->loadLayout()
			->_initLayoutMessages('checkout/session')
			->_initLayoutMessages('catalog/session')
			->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
		$this->renderLayout();
		Varien_Profiler::stop(__METHOD__ . 'cart_display');
	}
	
	public function couponAuto($couponCode, $isRemove)
    {
        /**
         * No reason continue with empty shopping cart
         */
		 
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
           return 0;            
        }
        $session = Mage::getSingleton('customer/session');
		
        if ($isRemove == 1) {
            $couponCode = '';
            $session = $session->setIsCouponAdded(0);
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
			$session->setIsCouponAdded(0);
            return false;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
			if(strlen($couponCode)> 0){
				$this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
				Mage::logException($e);
			}
        }
		
		$session->setIsCouponAdded(1);
		return true;
    }
}