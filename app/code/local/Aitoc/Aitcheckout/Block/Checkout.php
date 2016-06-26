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
class Aitoc_Aitcheckout_Block_Checkout extends Mage_Checkout_Block_Onepage_Abstract {
    public function __construct() {
        Mage::helper('aitcheckout/captcha')->resetConfirmedCaptchas();
        return parent::__construct();
    }

    protected function _preloadShippingMethods() {
        $quote = $this->getQuote();
        $customer = $this->getCustomer();

        if (!$customer->getId() || (!$customer->getDefaultShippingAddress() && !$customer->getDefaultBillingAddress())) {
            $address = $quote->getShippingAddress();
            if (!$address->getCountryId()) {
                $address
                        ->setCollectShippingRates(true)
                        ->setCountryId(Mage::helper('aitcheckout')->getDefaultCountry());
                if ($customer->getTaxClassId()) {
                    $address
                            ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION))
                            ->setPostcode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE));
                }
                $quote->save();
            }
        }
    }

    protected function _beforeToHtml() {
        parent::_beforeToHtml();

        $this->_preloadShippingMethods();

        foreach (Mage::helper('aitcheckout/aitconfcheckout')->getDisabledSectionHash() as $disabledStep) {
            $this->unsetChild($disabledStep);
        }
    }

    protected function _afterToHtml($html) {
        if ($this->helper('aitcheckout')->isShowCheckoutInCart() && $this->helper('aitcheckout')->isCompactDesign()) {
            $content = $this->getLayout()->getBlock('content');
            $content->setText(preg_replace('|\<div class="totals"\>(.*)\</div\>|Uis', '', $content->getText()));
        }
        return parent::_afterToHtml($html);
    }

    protected function _setCompactTemplate() {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/compact.css');
            if (Mage::getStoreConfig('checkout/aitcheckout/responsive')) {
                $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/responsive.css');
            }
        }

        $this->setTemplate($this->getCompactTemplate());
    }

    protected function _setCompact2Template() {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/compact.css');
          //  $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/compact.css');
            if (Mage::getStoreConfig('checkout/aitcheckout/responsive')) {
                $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/responsive.css');
            }
        }

        $this->setTemplate($this->getCompact2Template());
    }

    public function setContext($context) {
        $cartBlock = $this->getLayout()->getBlock('checkout.cart');

        if (!$this->helper('aitcheckout')->isShowCheckoutInCart() || $this->helper('aitcheckout')->isDisabled()) {
            $parentBlock = $cartBlock->getParentBlock();
            if ($parentBlock) {
                $parentBlock->unsetChild('aitcheckout.checkout');
            }
        } else {
            $this->getCheckout()->setCartWasUpdated(false);
            $cartBlock->unsetChild('shipping');

            if ($this->helper('aitcheckout')->isCompactDesign()) {
                $cartBlock->unsetChild('crosssell');
                $cartBlock->unsetChild('coupon');
                $cartBlock->unsetChild('totals');
                $cartBlock->unsetChild('methods');
                $cartBlock->getChild('top_methods')->unsetChild('checkout.cart.methods.onepage');
            }

            if (!$this->helper('aitcheckout')->isShowProceedToCheckoutButton()) {
                $cartBlock->getChild('top_methods')->unsetChild('checkout.cart.methods.onepage');
                if (!Mage::helper('aitcheckout')->isCompactDesign()) {
                    $cartBlock->getChild('methods')->unsetChild('checkout.cart.methods.onepage');
                }
            }
            if ($this->getQuote()->hasItems() && !$this->getQuote()->validateMinimumAmount()) {
                if ($this->helper('aitcheckout')->isShowCheckoutInCart()) {
                    $error = Mage::getStoreConfig('sales/minimum_order/error_message');
                    $this->getCheckout()->addError($error);
                }
            }
        }
    }

    public function getDesignId(){
        return $this->helper('aitcheckout')->getDesignTypeId();
    }
    public function chooseTemplate() {
        $controller = Mage::app()->getRequest()->getControllerName();

        if ($controller == 'cart' && $this->helper('aitcheckout')->isShowCheckoutInCart()) {
            if ($this->helper('aitcheckout')->isErrorQuoteItemQty()) {
                $this->setTemplate($this->getEmptyTemplate());
                return;
            }
        }

        if ($this->getQuote()->getItemsCount()) {
            switch ($this->helper('aitcheckout')->getDesignTypeId()) {
                case Aitoc_Aitcheckout_Helper_Data::DESIGN_DEFAULT:
                    $this->setTemplate($this->getCheckoutTemplate());
                    break;
                case Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT:
                    $this->_setCompactTemplate();
                    break;
                case Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT_V2:
                    $this->_setCompact2Template();
            }
            if ($this->helper('aitcheckout/terms')->getTocMode() == Aitoc_Aitcheckout_Helper_Data::CONDITIONS_POPUP) {
                $headBlock = $this->getLayout()->getBlock('head');
                if ($headBlock) {
                    $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/popup.css');
                }
            }
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {

                $headBlock = $this->getLayout()->getBlock('head');
                if ($headBlock) {
                    $headBlock->addItem('skin_css', 'css/aitoc/aitcheckout/popup.css');
                    $headBlock->addItem('skin_js', 'js/aitoc/aitcheckout/popup.js');
                }
            }
        } else {
            $this->setTemplate($this->getEmptyTemplate());
        }
    }

    public function getContinueShoppingUrl() {
        $url = $this->getData('continue_shopping_url');
        if (is_null($url)) {
            $url = Mage::getSingleton('checkout/session')->getContinueShoppingUrl(true);
            if (!$url) {
                $url = Mage::getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }

    /**
     * Return list of available checkout methods
     *
     * @param string $nameInLayout Container block alias in layout
     * @return array
     */
    public function getMethods($nameInLayout) {
        if ($this->getChild($nameInLayout) instanceof Mage_Core_Block_Abstract) {
            return $this->getChild($nameInLayout)->getSortedChildren();
        }
        return array();
    }

    /**
     * Return HTML of checkout method (link, button etc.)
     *
     * @param string $name Block name in layout
     * @return string
     */
    public function getMethodHtml($name) {
        $block = $this->getLayout()->getBlock($name);
        if (!$block) {
            Mage::throwException(Mage::helper('aitcheckout')->__('Invalid method: %s', $name));
        }
        return $block->toHtml();
    }

    /**
     * Validate if order amount is allowed to purchase
     *
     * @return boolean
     */
    public function isDisabled() {
        return Mage::helper('aitcheckout')->isPlaceOrderDisabled();
    }

    /**
     * @return boolean
     */
    public function isRequireLoggedInCheckout() {
        $guestCheckoutEnabled = Mage::getStoreConfig('checkout/options/guest_checkout');
        $customerMustBeLoggedIn = Mage::getStoreConfig('checkout/options/customer_must_be_logged');

        return (!$guestCheckoutEnabled && $customerMustBeLoggedIn) || $this->_isPersistentCartEnabled();
    }

    private function _isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Return HTML of login block that usually appear on checkout when user is guest
     *
     * @return string
     */
    public function getLoginBlockHtml() {
        $loginBlock = $this->getChild('reqlogin');
        if (!$loginBlock) {
            return false;
        }
        $param = Mage::app()->getRequest()->getParams();
        if ($param['usertype'] == 'guest' || $param['usertype'] == 'register') {
            return false;
        }
        if (!$this->_isCustomerLoggedIn()) {

            $html = $loginBlock->toHtml();

            $search = $loginBlock->getUrl('customer/account/forgotpassword');
            $replace = '#" id="forgotB" onclick="return false;';

            $html = str_replace($loginBlock->getMessagesBlock()->getGroupedHtml(), '', $html);
            $html = str_replace($search, $replace, $html);

            return $html;
        }

        return false;
    }

    /**
     * @return boolean
     */
    private function _isCheckoutLoginPersistent() {
        return Mage::getConfig()->getModuleConfig('Mage_Persistent')->is('active', 'true');
    }

    private function _isPersistentCartEnabled() {
        return $this->_isCheckoutLoginPersistent() &&
                Mage::helper('persistent')->isEnabled() &&
                Mage::helper('persistent')->isShoppingCartPersist() &&
                Mage::getSingleton('persistent/session')->loadByCookieKey()->getCustomerId();
    }

    /**
     * Return login block tempates. There are no persistent template in old versions of magento.
     *
     * @return string
     */
    public function getLoginTemplatePath() {
        return $this->_isCheckoutLoginPersistent() ?
                'persistent/checkout/onepage/login.phtml' :
                'checkout/onepage/login.phtml';
    }
    public function getCountryHtmlSelect($type)
    {

        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }

}
