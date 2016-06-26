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
class Aitoc_Aitcheckout_Block_Checkout_Giftmessage extends Mage_GiftMessage_Block_Message_Inline
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitcheckout/giftmessage/inline.phtml');  
//        $this->setId('giftmessage_form_0')
//             ->setDontDisplayContainer(false)
//             ->setEntity(Mage::getSingleton('checkout/session')->getQuote());  
    }
    
    protected function _beforeToHtml()
    {
        $this->setId('giftmessage_form_0')
             ->setDontDisplayContainer(false)
             ->setEntity(Mage::getSingleton('checkout/session')->getQuote())  
             ->setType('onepage_checkout');   
    }

    public function isShow()
    {
        return (Mage::getStoreConfigFlag(Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS) ||
                Mage::getStoreConfigFlag(Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER));
    } 
    
    public function isMessagesAvailable()
    {
        if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.4.2'))
        {
            return parent::isMessagesAvailable();
        }
        else { 
            return Mage::helper('giftmessage/message')->isMessagesAvailable('quote', $this->getEntity());
        }
    }
       
    
}