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
* File        Log.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


/**
 *
 * @category   Moogento
 * @package    Moogento_ShipEasy
 */
class Moogento_ShipEasy_Model_Mysql4_Sales_Order_Log extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('moogento_shipeasy/sales_order_log', 'id');
    }

    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->hasDataChanges()) {
            $object->setData('updated_at', Mage::getSingleton('core/date')->date());
        }
        return parent::_beforeSave($object);
    }

}
