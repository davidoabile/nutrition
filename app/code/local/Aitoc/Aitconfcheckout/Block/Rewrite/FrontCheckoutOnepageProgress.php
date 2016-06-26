<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CC Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitconfcheckout
 * @version      1.0.9 - 2.1.23
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitconfcheckout_Block_Rewrite_FrontCheckoutOnepageProgress extends Mage_Checkout_Block_Onepage_Progress
{
    public function getBilling()
    {
    // start aitoc
        return Mage::helper('aitconfcheckout/onepage')->getAddress(parent::getBilling());
    // finish aitoc        
    // return $this->getQuote()->getBillingAddress();
    }

    public function getShipping()
    {
    // start aitoc
        return Mage::helper('aitconfcheckout/onepage')->getAddress(parent::getShipping());
    // finish aitoc        
    // return $this->getQuote()->getShippingAddress();
    }

    public function checkStepActive($sStepCode)
    {
        return Mage::helper('aitconfcheckout')->checkStepActive($this->getQuote(), $sStepCode);
    }

    public function getProcessAddressHtml($sHtml)
    {
        $sHtml = nl2br($sHtml);

        $sHtml = str_replace(array('<br/>','<br />'), array('<br>', '<br>'), $sHtml); 
        
        $aReplace = array
        (
'<br><br>',    
    
'<br>
<br>',        

', <br>', ',  <br>'        
        );       
        
        while (strpos($sHtml, $aReplace[0]) !== false OR strpos($sHtml, $aReplace[1]) !== false) 
        {
        	$sHtml = str_replace($aReplace, '<br>', $sHtml);
        }

        if (strpos($sHtml, '<br>') === 0)
        {
            $sHtml = substr($sHtml, 4);
        }
           
        return $sHtml;
    }      
    
}