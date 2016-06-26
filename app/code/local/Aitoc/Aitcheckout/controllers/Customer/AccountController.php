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
require 'Mage/Customer/controllers/AccountController.php';
class Aitoc_Aitcheckout_Customer_AccountController extends Mage_Customer_AccountController
{
    public function isAjax()
    {
        //magento 1.4.1- compatibility
        return $this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax');
    }

    public function loginAjaxAction()
    {
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $result   = new Varien_Object();
        $session = $this->_getSession();

        if ($this->_getSession()->isLoggedIn() || !$this->isAjax()) {
            $result->redirect = $this->_getCheckoutRedirectUrl();
            $result->response = 'success';
            //$result->redirect = Mage::getUrl('*/*/');
            return $response->setBody($result->toJSON());
        }

        $login = $request->getPost('login');
        if (empty($login['username']) || empty($login['password'])) {
            $result->error = Mage::helper('aitcheckout')->__('Login and password are required.');
            return $response->setBody($result->toJSON());
        }

        try {
            $session->login($login['username'], $login['password']);
            /*if ($session->getCustomer()->getIsJustConfirmed()) {
                $this->_welcomeCustomer($session->getCustomer(), true);
            }*/
        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                    $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                    break;
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $message = $e->getMessage();
                    break;
                default:
                    $message = $e->getMessage();
            }
            $result->error = $message;
            Mage::helper('aitcheckout/captcha')->renewCaptcha($result);
            $session->setUsername($login['username']);
        } catch (Exception $e) {
        }

        /*if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = Mage::helper('core')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }*/

        $result->redirect = $this->_getCheckoutRedirectUrl();
        if ($session->isLoggedIn()) {
            $result->response = 'success';
        }
        return $response->setBody($result->toJSON());
    }

    protected function _getCheckoutRedirectUrl() {
        return Mage::app()->getStore()->getUrl(Mage::helper('aitcheckout')->getCheckoutUrl(),
            array(
                '_secure' => true
                )
            );
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordAjaxAction()
    {
        $email = $this->getRequest()->getPost('email');
        $result   = new Varien_Object();
        $response = $this->getResponse();

        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $result->error = Mage::helper('aitcheckout')->__('Invalid email address.');
                Mage::helper('aitcheckout/captcha')->renewCaptcha($result);
                return $response->setBody($result->toJSON());
            }
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    $result->message = Mage::helper('aitcheckout')->__('A new password has been sent.');
                    $result->response = 'success';
                }
                catch (Exception $e){
                    $result->error = $e->getMessage();
                }
            } else {
                $result->error = Mage::helper('aitcheckout')->__('This email address was not found in our records.');
                $this->_getSession()->setForgottenEmail($email);
            }
        } else {
            $result->error = Mage::helper('customer')->__('Please enter your email.');
        }
        //always renew captcha when after forgot Password Request
        Mage::helper('aitcheckout/captcha')->renewCaptcha($result);
        return $response->setBody($result->toJSON());
    }
    

}