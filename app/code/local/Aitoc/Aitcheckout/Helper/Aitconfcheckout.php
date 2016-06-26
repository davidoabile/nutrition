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
class Aitoc_Aitcheckout_Helper_Aitconfcheckout extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;
    
    protected $_disabledSteps = null;
    
    protected $_configs = array();

    /**
     * Check whether the CC module is active or not
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        if($this->_isEnabled === null)
        {
            $this->_isEnabled = $this->isModuleEnabled('Aitoc_Aitconfcheckout')?true:false;
        }
        return $this->_isEnabled;
    }
    
    /**
     * Retrieve disabled sections hash from the CC module
     * 
     * @return array
     */
    public function getDisabledSectionHash()
    {
        if($this->_disabledSteps === null)
        {
            if($this->isEnabled())
            {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $this->_disabledSteps = Mage::getModel('aitconfcheckout/aitconfcheckout')->getDisabledSectionHash($quote);
            }
            else
            {
                $this->_disabledSteps = array();
            }
        }
        return $this->_disabledSteps;
    }
    
    /**
      * Check whether checkout step is active or not
     * 
     * @param string $stepCode Unique checkout step code
     * 
     * @return boolean
     */
    public function checkStepActive($stepCode)
    {
        if($this->isEnabled())
        {
            return !in_array($stepCode, $this->getDisabledSectionHash());
        }
        else
        {
            return true;
        }
    }
    
    public function checkSkipShippingAllowed()
    {
        if($this->isEnabled())
        {
            $aAllowedBillingHash = Mage::helper('aitconfcheckout')->getAllowedFieldHash('billing');        
            $aAllowedShipingHash = Mage::helper('aitconfcheckout')->getAllowedFieldHash('shipping');        
            
            $aRequiredHash = array('address', 'city', 'region', 'country', 'postcode', 'telephone');
            
            foreach ($aAllowedShipingHash as $sKey => $bFieldActive)
            {
                if ($bFieldActive AND in_array($sKey, $aRequiredHash) AND !$aAllowedBillingHash[$sKey])
                {
                    return false;
                }
            }
        }
        return true;    
    }
    
    /**
     * Check whether some field is allowed or not
     * 
     * @param string $stepCode Unique checkout step code
     * @param string $fieldName Some field name
     * 
     * @return boolean
     */
    public function checkFieldShow($stepCode, $fieldName)
    {
        if($this->isEnabled())
        {
            $config = $this->_getStepConfig($stepCode);
           
            if (!$fieldName || !isset($config[$fieldName]) || !$config[$fieldName])
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Retrieve step fields config from the CC module
     * 
     * @param string $stepCode Unique checkout step code
     * 
     * @return array
     */
    protected function _getStepConfig($stepCode)
    {
        if(!isset($this->_configs[$stepCode]))
        {
            $this->_configs[$stepCode] = array();
       
            $allowedFieldHash = Mage::helper('aitconfcheckout')->getAllowedFieldHash($stepCode);
            foreach ($allowedFieldHash as $field => $status)
            {
                $this->_configs[$stepCode][$field] = $status;
            }
        }
        return $this->_configs[$stepCode];
    }
    
    public function getDefaultCountryId()
    {
        return Mage::helper('aitconfcheckout')->getDefaultCountryId(); 
    }
    
    public function getAddressesHtmlSelect($html)
    {
        if($this->isEnabled())
        {
            if ($html)
            {
                for ($i=1;$i<=10; $i++)
                {
                    $html = str_replace(array(', , ', ' , 
                            </option>', ', </option>', ' , ', ',,'), array(', ', '</option>', '</option>', ', ', ','), $html);   
                }        
            }
        }
        return $html;
    }
    
    public function getAddress($address)
    {
        if($this->isEnabled())
        {
            if ($address AND $data = $address->getData())
            {
                foreach ($data as $sKey => $mVal)
                {
                    if ($mVal == Mage::getModel('aitconfcheckout/aitconfcheckout')->getSubstituteCode())
                    {
                        $data[$sKey] = '';
                    }
                }
                $address->addData($data);
				$address->setCollectShippingRates(true);
            }
        }
        return $address;
    }
}