<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Method.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Shipping_Method
{
    public function getAllMethods()
    {
        $methods = array();
        $carriers = Mage::getStoreConfig('carriers');
        foreach($carriers as $code => $info) {
            $methods[$code] = (isset($info['title'])) ? Mage::helper('moogento_shipeasy')->__($info['title'])." ({$code})" : $code;
        }

        return $methods;
    }

    public function toOptionArray($withCustom = true)
    {
        $methods = array();
        $carriers = Mage::getStoreConfig('carriers');
        $helper = Mage::helper('moogento_shipeasy');
        foreach($carriers as $code => $info) {
            $methods[] = array(
                'value' => $code,
                'label' => (isset($info['title'])) ? $helper->__($info['title'])." ({$code})" : $code,
            );
        }

        if ($withCustom) {
            $methods[] = array(
                'value' => 'custom_value',
                'label' => $helper->__('Custom value'),
            );
        }

        return $methods;
    }
}
