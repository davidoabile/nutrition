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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Ayasoftware_SimpleProductPricing_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        /*reset insurance cost when being at shopping cart page*/
        Mage::getSingleton("core/session")->setInsuranceAdd(0);
        $showgift=$this->getRequest()->getParam('showgift');
        if(!empty($showgift)) Mage::register('showgift',1);
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
            
            if (!$this->_getQuote()->validateMinimumAmount()) {
                $amount = Mage::getStoreConfigFlag('sales/minimum_order/active', Mage::app()->getStore()->getStoreId()) ? 
                        Mage::helper('orderamount')->getMinimumPrice() :
                        Mage::getStoreConfig('sales/minimum_order/amount');
                
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency($amount);

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
    }    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        /*Fix : empty cart if clicking "edit" link in shopping cart page*/
        return parent::configureAction();
        if(!Mage::getStoreConfig('spp/setting/enableModule')){
			return parent::configureAction();
		}
    	// Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('id');
        $quoteItem = null;
        $cart = $this->_getCart();
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->_getSession()->addError($this->__('Quote item is not found.'));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
         $this->_getCart()->removeItem($id)
                  ->save();
          $oProduct = Mage::getModel('catalog/product')->load($quoteItem->getProduct()->getId());
          $this->getResponse()->setRedirect($oProduct->getProductUrl());
        } catch (Exception $e) {
            $this->_goBack();
            return;
        }
    }
    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            $this->use_simple_product_price_for_configurable_product();
            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }
    function use_simple_product_price_for_configurable_product(){
        foreach (Mage::getModel("checkout/cart")->getItems() as $item) {
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
}
