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
* File        Currency.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Helper_Currency extends Mage_Core_Helper_Abstract
{
    public function convertCurrency($price, $from, $to)
    {
        if ($from == $to) {
            return $price;
        }

        $from = Mage::getModel('directory/currency')->load($from);
        $to = Mage::getModel('directory/currency')->load($to);

        if ($rate = $from->getRate($to)) {
            return $price*$rate;
        } else if ($rate = $to->getRate($from)) {
            return $price / $rate;
        } else {
			throw new Exception(Mage::helper('directory')->__('Undefined rate from').' '.$from->getCode().'-'.$to->getCode().'.');

        }
    }

    public function getDefaultInputCurrencyCode()
    {
        return Mage::getStoreConfig('moogento_shipeasy/shipping_cost/default_currency');
    }
}
