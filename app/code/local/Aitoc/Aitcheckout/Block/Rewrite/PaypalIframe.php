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
class Aitoc_Aitcheckout_Block_Rewrite_PaypalIframe extends Mage_Paypal_Block_Iframe
{
    const IFRAME_URL_REPLACEMENT = '|||IFRAME_URL|||';
    
    protected $_compatibleHssMethods = array();

    protected function _aitcheckoutIsEnabled()
    {
        return !Mage::helper('aitcheckout')->isDisabled();
    }

    /**
     * Set our iframe template for compatible hss methods 
     */
    protected function _construct()
    {
        parent::_construct();
        if(defined('Mage_Paypal_Model_Config::METHOD_HOSTEDPRO'))  $this->_compatibleHssMethods[] = Mage_Paypal_Model_Config::METHOD_HOSTEDPRO;
        if(defined('Mage_Paypal_Model_Config::METHOD_PAYFLOWADVANCED'))  $this->_compatibleHssMethods[] = Mage_Paypal_Model_Config::METHOD_PAYFLOWADVANCED;
        if(defined('Mage_Paypal_Model_Config::METHOD_PAYFLOWLINK'))  $this->_compatibleHssMethods[] = Mage_Paypal_Model_Config::METHOD_PAYFLOWLINK;
        if ($this->_aitcheckoutIsEnabled() && in_array($this->_paymentMethodCode, $this->_compatibleHssMethods)) {
            $this->setTemplate('aitcheckout/paypal/iframe.phtml');
        }
    }

    /**
     * Check whether block is rendering after save payment
     *
     * @return bool
     */
    protected function _isAfterPaymentSave()
    {
        if ($this->_aitcheckoutIsEnabled()) {
            $quote = $this->_getCheckout()->getQuote();

            if ( ($quote->getPayment()->getMethod() == $this->_paymentMethodCode) &&
                $quote->getIsActive() &&
                $this->getTemplate() &&
                (Mage::app()->getRequest()->getActionName() != 'saveOrder') &&
                !Mage::registry('aitcheckout_paypal_review_block_rendering') ) 
            {
                return true;
            }  

            return false;
        }
        return parent::_isAfterPaymentSave();
    }

    protected function _beforeToHtml()
    {
        if (Mage::registry('aitcheckout_paypal_review_block_rendering') && 
            $this->_aitcheckoutIsEnabled() &&
            $this->_getCheckout()->getQuote()->getPayment()->getMethod() == $this->_paymentMethodCode
        )
        {
            $this->_shouldRender = true;
        }
        return parent::_beforeToHtml();
    }
    
    /**
     * Replaces the iframe url with some pattern which will be replaced with the actual url later.
     * This trick is necessary as we need to render iframe block before order
     * placement but we can get the url only when order is already created.          
     * 
     * @see Aitoc_Aitcheckout_Model_Order_Observer::_getPaypalReviewBlockHtml()
     * @return string
     */
    public function getFrameActionUrl()
    {
        // should work only if the extension is enabled and if we are in process of
        // order review block rendering for paypal advanced payment methods
        if (Mage::registry('aitcheckout_paypal_review_block_rendering') && 
            $this->_aitcheckoutIsEnabled())
        {
            return self::IFRAME_URL_REPLACEMENT;
        } else {
            return parent::getFrameActionUrl();
        }
    }
}