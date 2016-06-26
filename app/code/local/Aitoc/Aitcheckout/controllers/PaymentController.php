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
/**
* @copyright  Copyright (c) 2011 AITOC, Inc.
*/

$ebizmartsControllerFile = Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Ebizmarts' . DS . 'SagePaySuite' . DS . 'controllers' . DS . 'PaymentController.php';

if (Mage::helper('aitcheckout/sagepay')->checkIfEbizmartsSagePaySuiteActive() && is_file($ebizmartsControllerFile))
{
    require_once $ebizmartsControllerFile;
    
    class Aitoc_Aitcheckout_PaymentController extends Ebizmarts_SagePaySuite_PaymentController
    {
        public function onepageSaveOrderAction()
        {            
            //return;
            //if ($this->_expireAjax())
            {                
                //return;
            }            

            $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
            if ((FALSE === strstr(parse_url($this->_getRefererUrl(), PHP_URL_PATH), 'onestepcheckout')) && is_null($this->getRequest()->getPost('billing')))
            {
                /*# Validate checkout Terms and Conditions
                $result = array();
                if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                        $result['success'] = false;
                        $result['response_status'] = 'ERROR';
                        $result['response_status_detail'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }
                }*/
            }
            else
            {                
                $requestParams = $this->getRequest()->getParams();

                if (array_key_exists('billing', $requestParams))
                {
                    $billingAddressId = array_key_exists('billing_address_id', $requestParams) ? $requestParams['billing_address_id'] : false;
                    $shippingAddressId = array_key_exists('shipping_address_id', $requestParams) ? $requestParams['shipping_address_id'] : false;
                    $sameAsBilling = array_key_exists('use_for_shipping', $requestParams['billing']) ? $requestParams['billing']['use_for_shipping'] : false;
                    $billing_data = $requestParams['billing'];

                    if (!Mage::helper('customer')->isLoggedIn())
                    {
                        

                        $registration_mode = Mage::getStoreConfig('onestepcheckout/registration/registration_mode');
                        if($registration_mode == 'auto_generate_account')
                        {
                            // Modify billing data to contain password also
                            $password = Mage::helper('onestepcheckout/checkout')->generatePassword();
                            $billing_data['customer_password'] = $password;
                            $billing_data['confirm_password'] = $password;
                            $this->_getQuote()->getCustomer()->setData('password', $password);
                            $this->_getQuote()->setData('password_hash', Mage::getModel('customer/customer')->encryptPassword($password));

                            $this->_getQuote()->setData('customer_email', $billing_data['email']);
                            $this->_getQuote()->setData('customer_firstname', $billing_data['firstname']);
                            $this->_getQuote()->setData('customer_lastname', $billing_data['lastname']);
                        }

                        if ($registration_mode == 'require_registration' || $registration_mode == 'allow_guest')
                        {
                            if (!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']) && ($billing_data['customer_password'] == $billing_data['confirm_password']))
                            {
                                $password = $billing_data['customer_password'];
                                $this->_getQuote()->setCheckoutMethod('register');
                                $this->_getQuote()->getCustomer()->setData('password', $password);
                                $this->_getQuote()->setData('customer_email', $billing_data['email']);
                                $this->_getQuote()->setData('customer_firstname', $billing_data['firstname']);
                                $this->_getQuote()->setData('customer_lastname', $billing_data['lastname']);
                                $this->_getQuote()->setData('password_hash', Mage::getModel('customer/customer')->encryptPassword($password));
                            }
                        }

                        if (!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']))
                        {
                            
                            // Trick to allow saving of
                            Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod('register');
                        }

                    }//IsLoggedIn

                    if (false !== $billingAddressId && (int)$billingAddressId)
                    {
                        $customerAddress = Mage::getModel('customer/address')->load((int)$billingAddressId);
                        if ($customerAddress->getId())
                        {
                            $this->_getQuote()->getBillingAddress()->importCustomerAddress($customerAddress);
                            if($sameAsBilling)
                            {
                                $this->_getQuote()->getShippingAddress()->importCustomerAddress($customerAddress);
                            }
                        }
                    }
                    else
                    {
                        $billingAddress = new Ebizmarts_SagePaySuite_Model_Address($requestParams['billing']);
                        $this->_getQuote()->getBillingAddress()->addData($billingAddress->toArray());
                    }

                    if (false !== $shippingAddressId && false === $sameAsBilling && (int)$shippingAddressId)
                    {
                        $customerAddress = Mage::getModel('customer/address')->load((int)$shippingAddressId);
                        if ($customerAddress->getId()) {
                        $this->_getQuote()->getShippingAddress()->importCustomerAddress($customerAddress);
                        }
                    }
                    else
                    {
                        /*if (false === $sameAsBilling)
                        {
                            Mage::helper('onestepcheckout/checkout')->saveShipping($requestParams['shipping'], null);
                        }
                        elseif (false === $billingAddressId)
                        {
                            Mage::helper('onestepcheckout/checkout')->saveShipping($requestParams['billing'], $billingAddressId);
                        }*/
                    }
                    $this->_getQuote()->save();

                    if (array_key_exists('onestepcheckout_comments', $requestParams) && !empty($requestParams['onestepcheckout_comments']))
                    {
                        $this->getSageSuiteSession()->setOscOrderComments($requestParams['onestepcheckout_comments']);
                    }

                    if (array_key_exists('subscribe_newsletter', $requestParams) && (int)$requestParams['subscribe_newsletter'] === 1)
                    {
                        $this->getSageSuiteSession()->setOscNewsletterEmail($this->_getQuote()->getBillingAddress()->getEmail());
                    }

                }
            }

            if ($data = $this->getRequest()->getPost('payment', false))
            {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            if ($dataM = $this->getRequest()->getPost('shipping_method', ''))
            {
                $this->getOnepage()->saveShippingMethod($dataM);
            }

            if ($paymentMethod == 'sagepayserver')
            {
                $this->_forward('saveOrder', 'serverPayment', 'sagepaysuite', $this->getRequest()->getParams());
                return;
            }
            elseif ($paymentMethod == 'sagepaydirectpro')
            {
                $this->_forward('saveOrder', 'directPayment', 'sagepaysuite', $this->getRequest()->getParams());
                return;
            }
            elseif ($paymentMethod == 'sagepayform')
            {
                $resultData             = array();
                $resultData['success']  = true;
                $resultData['error']    = false;
                $resultData['redirect'] = Mage::getUrl('sgps/formPayment/go', array('_secure' => true));
                return $this->getResponse()->setBody(Zend_Json::encode($resultData));
            }
            else
            {
                $this->_forward('saveOrder', 'onepage', 'checkout');

                return;
            }
        }
    }
}
else
{
    class Aitoc_Aitcheckout_PaymentController extends Aitoc_Aitcheckout_Controller_Action
    {
    }
}