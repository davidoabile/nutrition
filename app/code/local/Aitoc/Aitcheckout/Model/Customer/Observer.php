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
class Aitoc_Aitcheckout_Model_Customer_Observer
{

    public function checkCheckoutLoginCaptcha(Varien_Event_Observer $observer)
    {
       if(!Mage::helper('aitcheckout/captcha')->checkIfCaptchaEnabled()) {
           return false;//not 1.7
       }
       switch($observer->getEvent()->getName()) {
           case 'controller_action_predispatch_aitcheckout_customer_account_loginAjax':
               $formId = 'user_login';
               Mage::getSingleton('captcha/observer')->checkUserLogin($observer);
           break;
           case 'controller_action_predispatch_aitcheckout_customer_account_forgotPasswordAjax':
               $formId = 'user_forgotpassword';
               Mage::getSingleton('captcha/observer')->checkForgotPassword($observer);
           break;
       }
       $message = Mage::getSingleton('customer/session')->getMessages()->getLastAddedMessage();
       if($message instanceof Mage_Core_Model_Message_Error) {
           $controller = $observer->getControllerAction();
           Mage::getSingleton('customer/session')->getMessages()->deleteMessageByIdentifier( $message->getIdentifier() );
           $response = $controller->getResponse();
           if($response->isRedirect()) {
               $response->clearHeader('Location')->setHttpResponseCode(200);
           }
           $result = array('error' => $message->getText(), 'form_id'=> $formId, 'captcha_image'=>Mage::helper('aitcheckout/captcha')->generateNewCaptcha($formId) );
           $response->setBody(Mage::helper('core')->jsonEncode($result));
       }
    }

    /**
     * paypal compatibility for "Require Customer To Be Logged In To Checkout" option
     */    
    public function onPredispatchCustomerAccountLoginPost(Varien_Event_Observer $observer)
    {
        try{
            $http_reffer = $observer->getControllerAction()->getRequest()->getServer('HTTP_REFERER');
            if($http_reffer && ((strpos($http_reffer , "checkout/cart")!==false)||(strpos($http_reffer , "aitcheckout/checkout")!==false)))
            {
                Mage::getSingleton('customer/session')->setBeforeAuthUrl($http_reffer);
            }
        }catch(Exception $e)
        {
            Mage::logException($e);
        }
    
    }
}