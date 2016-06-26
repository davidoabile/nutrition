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
class Aitoc_Aitcheckout_Helper_Adjgiftregistry extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;
    
    protected $_addrId = null;

    /**
     * Check whether the GR module is active or not
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        if($this->_isEnabled === null)
        {
            $this->_isEnabled = $this->isModuleEnabled('AdjustWare_Giftreg')?true:false;
        }
        return $this->_isEnabled;
    }
    
    /**
     * Add additional option to shipping address list if module is active
     * 
     * @param Mage_Checkout_Block_Onepage_Abstract $step Onepage checkout step object
     * @param array $options Array of address options passed by referense
     * 
     * @return integer
     */
    public function getAddressesHtmlSelect(Mage_Checkout_Block_Onepage_Abstract $step, &$options)
    {
        $addressId = 0;
        if($this->isEnabled() && $step instanceof Aitoc_Aitcheckout_Block_Checkout_Shipping)
        {
            if ($id = $this->getGiftAddressId())
            {
                $options[] = array(
                    'value' => $id,
                    'label' => Mage::helper('adjgiftreg')->__('Ship to the owner of the gift registry'),
                ); 
                $addressId = $id;           
            }
        }
        
        return $addressId;
    }
    
    /**
     * Check whether current order have gift regestry address or not
     * 
     * @return integer 
     */
    public function getGiftAddressId()
    {
        if(is_null($this->_addrId))
        {
            $this->_addrId = 0;
            if($this->isEnabled())
            {
                $id = Mage::getSingleton('checkout/session')->getQuote()->getAdjgiftregEventId();
                if ($id)
                {
                    $event = Mage::getModel('adjgiftreg/event')->load($id);
                    if ($event->getId())
                    {
                        $this->_addrId = $event->getAddressId();
                    }
                }
            }
        }
        return $this->_addrId;
    }
}