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
class Aitoc_Aitcheckout_CheckoutController extends Aitoc_Aitcheckout_Controller_Action {

    protected $_sectionSaveFunctions = array(
        'billing' => 'saveBilling',
        'shipping' => 'saveShipping',
        'shipping_method' => 'saveShippingMethod',
        'payment' => 'savePayment',
        'deliverydate' => 'saveShippingMethod',
        'customreview' => 'saveCustomReview',
    );

    protected function _ajaxRedirectResponse() {
        $this->getResponse()
                ->setHeader('HTTP/1.1', '403 Session Expired')
                ->setHeader('Login-Required', 'true')
                ->sendResponse();
        return $this;
    }

    protected function _expireAjax() {
        if (!$this->_getOnepage()->getQuote()->hasItems() || $this->_getOnepage()->getCheckout()->getCartWasUpdated(true)) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    protected function _saveCustomerRequiredData() {
        if (!$this->_getOnepage()->getCustomerSession()->getCustomerId()) {
            return;
        }
        $data = $requiredData = $this->getRequest()->getPost();
        $customer = $this->_getOnepage()
                        ->getCustomerSession()->getCustomer();
        if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.5')) {
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('checkout_register')
                    ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
            $customerForm->setEntity($customer);
            $customerRequest = $customerForm->prepareRequest($data);
            $customerFields = $customerForm->extractData($customerRequest);
        } else {
            $customerFields = array(
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'email' => 'email',
                'dob' => 'dob',
                'taxvat' => 'taxvat',
                'gender' => 'gender',
                'suffix' => 'suffix',
                'prefix' => 'prefix',
            );
        }
        $requiredData = $this->getRequest()->getPost('billing');
        foreach ($requiredData as $fieldId => $value) {
            if (isset($customerFields[$fieldId]) && !$customer->getData($fieldId)) {
                $customer->setData($fieldId, $value);
            }
        }
        $customer->save();
        $this->_getOnepage()->getQuote()->setCustomer($customer);
    }

    protected function _isCaptchaCorrect() {
        if (!Mage::helper('aitcheckout/captcha')->checkIfCaptchaEnabled()) {
            return true; //not 1.7
        }
        $formId = $this->_getCheckoutCaptchaMethod();
        $helper = Mage::helper('aitcheckout/captcha');
        if ($helper->isCaptchaPassed($formId)) {
            return true;
        }

        $observer = new Varien_Object();
        $observer->setControllerAction($this);

        Mage::getSingleton('captcha/observer')->checkGuestCheckout($observer);
        Mage::getSingleton('captcha/observer')->checkRegisterCheckout($observer);

        $response = $this->getResponse();
        if ($response->getBody()) {
            $result = Mage::helper('core')->jsonDecode($response->getBody());
            if ($result['error'] == 1) {
                $result = new Varien_Object($result);
                $helper->renewCaptcha($result, $formId);

                $response->setBody(Mage::helper('core')->jsonEncode($this->_extractStepOutput('billing', $result->__toArray())));
                return false;
            }
        }
        $helper->setCaptchaAsConfirmed($formId);
        return true;
    }

    protected function _getCheckoutCaptchaMethod() {
        $checkoutMethod = $this->_getOnepage()->getQuote()->getCheckoutMethod();
        if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
            $formId = 'register_during_checkout';
        } else {
            $formId = 'guest_checkout';
        }
        return $formId;
    }

    public function indexAction() {
        /* {#AITOC_COMMENT_END#}
          $iStoreId = Mage::app()->getStore()->getId();
          $iSiteId  = Mage::app()->getWebsite()->getId();
          $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckout')->getLicense()->getPerformer();
          $ruler     = $performer->getRuler();
          if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
          {
          $this->_redirect('checkout/onepage');
          return;
          }
          {#AITOC_COMMENT_START#} */

          // Update default shipping
        $defaultmatrix='matrixrate_matrixrate_1';
        $groups = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
        if($groups){
            foreach ($groups as $code => $_rates){
                if($code=='matrixrate'){
                    foreach ($_rates as $_rate){

                        if($_rate->getCode() =='matrixrate_matrixrate_free'){
                            $defaultmatrix=$_rate->getCode();
                            break;
                        }
                        if($_rate->getCode()) $defaultmatrix=$_rate->getCode();
                    }
                }
            }
        }
        $shippingMethod = Mage::getStoreConfig('checkout/aitcheckout/default_shipping_method', Mage::app()->getStore()->getId());
        $shippingAddress = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress();
        if (!$shippingAddress->getShippingMethod() && $shippingMethod) {
            if($shippingMethod=='matrixrate_matrixrate') $shippingMethod = $defaultmatrix;
          $shippingAddress->setCountryId('AU')->setShippingMethod($shippingMethod)->save();

          Mage::getSingleton('checkout/cart')->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
          Mage::getSingleton('checkout/cart')->getQuote()->save();
          Mage::getSingleton('checkout/cart')->save();
        }

        // End update default shipping
        // echo $shippingMethod;die();

        if (Mage::helper('aitcheckout')->isDisabled()) {
            $this->_redirect('checkout/onepage');
            return;
        }

        if (Mage::helper('aitcheckout')->isNeedRedirectToSecure()) {
            $this->_redirect(Mage::helper('aitcheckout')->getCheckoutUrl(), array('_secure' => true));
            return;
        }

        $quote = $this->_getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            if (Mage::helper('aitcheckout')->isShowCheckoutOutsideCart()) {
                $this->_redirect('checkout/cart');
                return;
            }
        }

        if ($quote->hasItems() && !$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            $this->_getOnepage()->getCheckout()->addError($error);
            if (Mage::helper('aitcheckout')->isShowCheckoutOutsideCart()) {
                $this->_redirect('checkout/cart');
                return;
            }
        }

        foreach ($quote->getMessages() as $message) {
            if ($message) {
                $this->_getOnepage()->getCheckout()->addMessage($message);
            }
        }
        // Make default ticked Insurance
        Mage::getSingleton("core/session")->setInsuranceAdd(1);
        
        $this->_getOnepage()->getCheckout()->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->_getOnepage()->initCheckout();
        $this->loadLayout()
                ->_initLayoutMessages('checkout/session')
                ->_initLayoutMessages('catalog/session')
                ->getLayout()->getBlock('head')->setTitle(Mage::helper('aitcheckout')->getCheckoutTitle())
        ;

        if (Mage::helper('aitcheckout')->isShowCheckoutTitle()) {
            $this->getLayout()
                    ->getBlock('head')
                    ->setTitle(
                            Mage::helper('aitcheckout')->__(Mage::helper('aitcheckout')->getCheckoutTitle())
            );
        } else {
            $this->getLayout()
                    ->getBlock('head')
                    ->setTitle(
                            Mage::helper('checkout')->__('Checkout')
            );
        }
        $this->renderLayout();

        if (Mage::helper('aitcheckout/aitconfcheckout')->isEnabled()) {
            $this->_getOnepage()->saveSkippedSata('progress');
        }
    }

    public function updateStepsAction() {
        $aditionalResult = array();
        $error = array();
        $result = array();
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['radio_insurance'])) {
                Mage::getSingleton("core/session")->setInsuranceAdd(1);
            } else {
                Mage::getSingleton("core/session")->setInsuranceAdd(0);
            }
            $currentStep = $data['step'];
            $data = $this->getRequest()->getPost($currentStep, array());

            $customerAddressId = null;
            switch ($currentStep) {
                case 'billinglocation':
                case 'shippinglocation':
                    $stepName = substr($currentStep, 0, strpos($currentStep, 'location'));
                    if ($data = $this->getRequest()->getPost($stepName)) {
                        if (isset($data['use_for_shipping']) && !$data['use_for_shipping'] && $stepName == 'billing') {
                            break;
                        } elseif ($customerAddressId = $this->getRequest()->getPost($stepName . '_address_id', false)) {
                            $this->_setAddress($customerAddressId, $stepName);
                        } else {
                            $this->_setLocation($data, $currentStep);
                        }
                    }
                    break;

                case 'billing':
                    Mage::helper('aitcheckout')->getCheckoutMethod($this->_getOnepage());
                    if (false === $this->_isCaptchaCorrect()) {
                        return;
                    }
                    $this->_saveCustomerRequiredData();
                case 'shipping':
                    Mage::getSingleton('checkout/session')->setConfirmSameAsBilling(1);
                    $customerAddressId = $this->getRequest()->getPost($currentStep . '_address_id', false);
                    break;
                case 'shipping_method':
                    $this->_updateDeliveryDate();
                    break;
                case 'payment':
                    break;
                /* case 'giftcard':
                  $this->refreshStepsAction();
                  break; */
                case 'deliverydate':
                    $data = $this->getRequest()->getPost('shipping_method', array());
                    break;
                case 'aitgiftwrap':
                case 'giftmessage':
                    Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote' => $this->_getOnepage()->getQuote()));
                    break;
            }
            try {
                $saveFunction = isset($this->_sectionSaveFunctions[$currentStep]) ? $this->_sectionSaveFunctions[$currentStep] : null;
                $resolve = false;
                $result = $saveFunction ? $this->_getOnepage()->$saveFunction($data, $customerAddressId) : array();
            } catch (Mage_Core_Exception $e) {
                $error = array('error' => -1, 'message' => $e->getMessage());
            }

            Mage::helper('aitcheckout/onlyif_data')->saveBilling($currentStep, $customerAddressId);

            if (Mage::helper('aitcheckout/captcha')->isJustConfirmed()) {
                $resolve = array(
                    'hide_captcha' => 'captcha-input-box-' . $this->_getCheckoutCaptchaMethod()
                );
            }

            $this->_getOnepage()->getQuote()->collectTotals()->save();

            $result = array_merge($result, $aditionalResult, $error);

            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result, $resolve))
            );
        }
    }

    protected function _updateDeliveryDate() {
        if (Mage::helper('aitcheckout')->isModuleEnabled('AdjustWare_Deliverydate') && Mage::getStoreConfigFlag('checkout/adjdeliverydate/enabled')) {
            $quoteAddress = $this->_getOnepage()->getQuote()->getBillingAddress();
            Mage::getModel('adjdeliverydate/quote')->saveDelivery($quoteAddress);
        }
    }

    protected function _setAddress($customerAddressId, $step) {
        $data = $this->getRequest()->getPost($step);
        if ($step == 'billing') {
            $this->_getOnepage()->saveBilling($data, $customerAddressId);
        } else {
            $this->_getOnepage()->saveShipping($data, $customerAddressId);
        }
    }

    protected function _setLocation($data, $currentStep) {
        $country = (string) $data['country_id'];
        $postcode = (string) $data['postcode'];
        $city = (string) $data['city'];
        $regionId = (string) $data['region_id'];
        $region = (string) $data['region'];

        $this->_getOnepage()->getQuote()->getShippingAddress()
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region)
                ->setCollectShippingRates(true);

        if ($currentStep == 'billinglocation') {
            $this->_getOnepage()->getQuote()->getBillingAddress()
                    ->setCountryId($country)
                    ->setCity($city)
                    ->setPostcode($postcode)
                    ->setRegionId($regionId)
                    ->setRegion($region);
        }

        $this->_getOnepage()->getQuote()->collectTotals();
        $this->_getOnepage()->getQuote()->save();
    }

    public function saveOrderAction() {
        $result = array();
        $payment = $this->getRequest()->getParam('payment');
        $params = $this->getRequest()->getParams();
        $this->_getOnepage()->savePayment($payment);

        try {
            $result = $this->validateOrder();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Core_Exception $e) {
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
        }

        $redirectUrl = $this->_getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

        Mage::dispatchEvent('aitcheckout_save_order', array('request' => $this->getRequest(),
            'quote' => $this->_getOnepage()->getQuote()));

        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        $params['_secure'] = true;
        $this->_forward('saveOrder', 'onepage', 'checkout', $params);
    }

    /**
     * Validate quote state to be able submited from one page checkout page
     *
     * @deprecated after 1.4 - service model doing quote validation
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function validateOrder() {
        $helper = Mage::helper('checkout');
        if ($this->_getOnepage()->getQuote()->getIsMultiShipping()) {
            Mage::throwException(Mage::helper('aitcheckout')->__('Invalid checkout type.'));
        }

        if (!$this->_getOnepage()->getQuote()->isVirtual()) {
            $address = $this->_getOnepage()->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(Mage::helper('aitcheckout')->__('Please check shipping address information.'));
            }
            if (Mage::helper('aitcheckout/aitcheckoutfields')->isEnabled() && !Mage::getStoreConfig('aitconfcheckout/shipping_method/active')) {
                $rate = 0.0001;
                $method = 'n_a';
            } else {
                $method = $address->getShippingMethod();
                $rate = $address->getShippingRateByCode($method);
            }

            if (!$this->_getOnepage()->getQuote()->isVirtual() && (!$method || !$rate)) {
                Mage::throwException(Mage::helper('core')->__('Please specify shipping method.'));
            }
        }

        $address = $this->_getOnepage()->getQuote()->getBillingAddress();
        $addressValidation = $address->validate();

        if ($addressValidation !== true) {
            Mage::throwException(Mage::helper('aitcheckout')->__('Please check billing address information.'));
        }

        if (!($this->_getOnepage()->getQuote()->getPayment()->getMethod())) {
            Mage::throwException(Mage::helper('aitcheckout')->__('Please select valid payment method.'));
        }
    }

    public function refreshStepsAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $currentStep = $this->getRequest()->getPost('step');
            $this->getResponse()
                    ->setBody(
                            Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
            );
        }
    }

}
