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
* File        Backorders.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 



class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Backorders
    extends Mage_Adminhtml_Block_Template
{
    protected $_xmlPathFillColor = 'moogento_shipeasy/grid/backorder_fill_color';
    protected $_xmlPathCriteria = 'moogento_shipeasy/grid/backorder_product_availability';
    protected $_xmlPathCriteriaCustomQty = 'moogento_shipeasy/grid/backorder_custom_qty';
    
    protected $_xmlPathColorUnavailable = 'moogento_shipeasy/grid/backorder_fully_unavailable';
    protected $_xmlPathColorFullyAvailable = 'moogento_shipeasy/grid/backorder_fully_available';
    protected $_xmlPathColorPartiallyAvailable = 'moogento_shipeasy/grid/backorder_partially_available';
    protected $_xmlPathTransparentStatus = 'moogento_shipeasy/grid/backorder_transparent_status';
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/backorders.phtml');
    }

    public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection()->addFieldToFilter('product_type', array('neq' => 'configurable'));
    }

    protected function _ifOrderTransparent()
    {
        $images_array = Mage::getStoreConfigFlag("moogento_shipeasy/grid/backorder_images_of_status");
        if (is_null($images_array) || ($images_array === "")) {
            return true;
        }        
        $transparent_arr = Array();
        if (($transparent_statuses = Mage::getStoreConfig('moogento_shipeasy/grid/backorder_transparent_status')))
            $transparent_arr = explode(',',$transparent_statuses);
        foreach($transparent_arr as $stran_status){
            if($this->getOrder()->getStatus() == $stran_status) return true;
        }
        return false;
    }
}
