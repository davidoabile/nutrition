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
* File        Collection.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


/**
 * Description of Collection
 *
 * @author arkadij
 */
class Moogento_ShipEasy_Model_Mysql4_Sales_Order_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_isFlagSetAttachOrders = FALSE;

    public function _construct()
    {
        $this->_init('moogento_shipeasy/sales_order_log');
    }

    protected function _afterLoadData()
    {
        parent::_afterLoadData();
        $this->walk('getAction');
        if ($this->_isFlagSetAttachOrders) {
            $this->walk('getOrder');
        }
    }

    public function attachOrderInstances()
    {
        if ($this->isLoaded()) {
            $this->walk('getOrder');
        } else {
            $this->_isFlagSetAttachOrders = TRUE;
        }
    }

}
