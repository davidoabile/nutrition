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
class Aitoc_Aitcheckout_Model_Order_Observer
{
 
    public function onOnepageSaveOrderAfter(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        $quote = $observer->getQuote();
        $helper = Mage::helper('aitcheckout');
        Mage::getSingleton('checkout/session')->setConfirmSameAsBilling(0);
        if ($request->getPost('newsletter') && $quote->getBillingAddress()->getEmail())
        {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = $quote->getBillingAddress()->getEmail();

            $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
            if ($ownerId !== null && $ownerId != $customerSession->getId())
            {
                $session->addError(Mage::helper('aitcheckout')->__('There was a problem with the newsletter subscription: This email address is already assigned to another user.'));
            } else {
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE)
                {
                    $session->addSuccess(Mage::helper('aitcheckout')->__('Confirmation request for newsletter subscription has been sent.'));
                }
                else {
                    $session->addSuccess(Mage::helper('aitcheckout')->__('Thank you for your newsletter subscription.'));
                }
            }
        }
    }

    function onSalesOrderPlaceBefore(Varien_Event_Observer $observer)
    {
        $customer       = $observer->getOrder()->getCustomer();
        $password       = $customer->getPassword();
        $quote          = $observer->getQuote();
        $checkoutMethod = $quote->getData('checkout_method');
        
        
        if (($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
            || $checkoutMethod == 'register')
        {
            // Magento does not create a customer account during checkout via PayPal prior to v 1.6.1
            // thus in that case we should disable the following check to prevent unnecessary error
            if (!(version_compare(Mage::getVersion(), '1.6.1', 'lt')
                && $quote->getPayment()->getMethod() == Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS
                && !$customer->getEmail())) // For case when someone have fixed customer account creation in Magento prior to v.1.6.1 
            {
                if (!$password === NULL || $password == '') {
                    Mage::throwException( Mage::helper('aitcheckout')->__('The password cannot be empty.') );
                }
            }
        }
    }

    /**
     * Force payment method save for the case when only one payment method is available
     */
    public function beforeSagepaySaveOrder($observer)
    {   
        $payment = Mage::app()->getRequest()->getParam('payment');
        Mage::getSingleton('checkout/type_onepage')->savePayment($payment);
    }

    /**
     * paypal compatibility
     * analog of Mage_Paypal_Model_Observer 
     */
    public function setResponseAfterSaveOrder(Varien_Event_Observer $observer)
    {
        if(Mage::helper('aitcheckout')->isPaypalAdvancedAvailable())
        {
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::registry('hss_order');
    
            if ($order && $order->getId()) {
                $payment = $order->getPayment();
                if ($payment && in_array($payment->getMethod(), Mage::helper('paypal/hss')->getHssMethods())) {
                    /* @var $controller Mage_Core_Controller_Varien_Action */
                    $controller = $observer->getEvent()->getData('controller_action');
                    $result = Mage::helper('core')->jsonDecode(
                        $controller->getResponse()->getBody('default'),
                        Zend_Json::TYPE_ARRAY
                    );
    
                    if (empty($result['error'])) {
                        $result['update_presection'] = array(
                            'name' => 'review',
                            'html' => $this->_getPaypalReviewBlockHtml()
                        );
                        $result['redirect'] = false;
                        $result['success'] = false;
                        $controller->getResponse()->clearHeader('Location');
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * paypal compatibility
     * create review block before final order submit
     */
    public function renderReviewBlockAfterAitSaveOrder(Varien_Event_Observer $observer)
    {
        if(Mage::helper('aitcheckout')->isPaypalAdvancedAvailable() && !Mage::registry('aitcheckout_paypal_review_block'))
        {
            Mage::register('aitcheckout_paypal_review_block_rendering', true);
            $controller = $observer->getEvent()->getData('controller_action');
            $controller->loadLayout('aitcheckout_checkout_review');
            $html = $controller->getLayout()->getBlock('aitcheckout.checkout')->getChildHtml();
            Mage::unregister('aitcheckout_paypal_review_block_rendering');
            Mage::register('aitcheckout_paypal_review_block', $html);
        }
        return $this;
    }

    /**
     * Get block from registry and replace the url pattern with the actual iframe url.
     * 
     * @see Aitoc_Aitcheckout_Block_Rewrite_PaypalIframe::getFrameActionUrl()
     * @return string
     */
    protected function _getPaypalReviewBlockHtml()
    {
        $html = Mage::registry('aitcheckout_paypal_review_block');
        $block = Mage::registry('aitcheckout_paypal_iframe_block');
        if(is_object($block)) {//if module is enabled, but Checkout is disabled        
            $html = str_replace(Aitoc_Aitcheckout_Block_Rewrite_PaypalIframe::IFRAME_URL_REPLACEMENT, $block->getFrameActionUrl(), $html);
        }
        
        return $html;
    }
}