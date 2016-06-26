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
* File        Region.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Customergroup
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
    	$data_value = $row->getData('szy_customer_group_id');
    	if($data_value != null)
    	{
    		$group = Mage::getSingleton('customer/group')->load($data_value);
    		return $group->getData('customer_group_code');
    	}
    	else
    	{
    		$order_id = $row->getData('increment_id');
			$order = Mage::getModel('sales/order')
			  ->getCollection()
			  ->addAttributeToFilter('increment_id', $order_id)
			  ->getFirstItem();
			$group = Mage::getSingleton('customer/group')->load($order->getData('customer_group_id'));
    		$resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            $resource->updateGridRow($order->getData('entity_id'),'szy_customer_group_id',$order->getData('customer_group_id'));
			return $group->getData('customer_group_code');
    	}
    }
}
