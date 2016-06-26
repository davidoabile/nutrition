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
class Aitoc_Aitcheckout_Helper_Login extends Aitoc_Aitcheckout_Helper_Abstract
{

    /**
     * @return boolean
     */
    private function _isCheckoutLoginPersistent()
	{
        return Mage::getConfig()->getModuleConfig('Mage_Persistent')->is('active', 'true');
    }
	
	/**
     * Return login block tempates. There are no persistent template in old versions of magento.
     *
     * @return string
     */
    public function getLoginTemplatePath()
	{
        if($this->_isCheckoutLoginPersistent()){
            return "persistent/checkout/onepage/login.phtml";
        }
        return  "checkout/onepage/login.phtml";
    }

}