<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Order.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Sales_Order extends Mage_Sales_Model_Order
{
    protected function _checkState()
    {
        if (Mage::getStoreConfig('moogento_shipeasy/general/ignore_status_check') && Mage::registry('ignore_status_check')) {
            return $this;
        }

        return parent::_checkState();
    }


    public function isStateProtected($state)
    {
        if (Mage::getStoreConfig('moogento_shipeasy/general/ignore_status_check') && Mage::registry('ignore_status_check')) {
            return false;
        }

        return parent::isStateProtected($state);
    }

}
