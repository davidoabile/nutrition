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
 * @author arkadij
 */
class Moogento_ShipEasy_Model_Sales_Order_Log extends Mage_Core_Model_Abstract
{

    protected static $_massActionItemsCache;

    public function _construct()
    {
        parent::_construct();
        $this->_init('moogento_shipeasy/sales_order_log');
    }

    /**
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->getData('order')) {
            $order = Mage::getModel('sales/order')->load($this->getOrderId());
            $this->setData('order', $order);
        }
        return $this->getData('order');
    }

    public function getAction()
    {
        if (!$this->getData('action')) {
            $action = unserialize($this->getData('actions_serialized'));
            $this->setData('action', $action);
        }
        return $this->getData('action');
    }

    public function getActionTitle($action)
    {
        if (!self::$_massActionItemsCache) {
            self::$_massActionItemsCache = Mage::helper('moogento_shipeasy/grid')->getMassActionItems();
        }

        return self::$_massActionItemsCache[$action['action']]['label'];
    }

    /**
     *
     * @return array
     */
    public function getActionStatus()
    {
        if (!$this->getData('action_status')) {
            $action = $this->getAction();
            if (isset($action['action_status']) && is_array($action['action_status'])) {
                $this->setData('action_status', $action['action_status']);
            } else {
                $this->setData('action_status', array());
            }
        }
        return $this->getData('action_status');
    }

    public function parseActionArguments($action)
    {
        $arguments = array();
        $helper = Mage::helper('moogento_shipeasy');
        
        switch ($action['action']) {
            case 'custom_order_attribute':

                if($action['szy_attr_no'] == 3)
                    $szy_custom_attribute = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute3_header');
                else
                    if($action['szy_attr_no'] == 2)
                        $szy_custom_attribute = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute2_header');
                    else
                        $szy_custom_attribute = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute_header');

                $arguments[$helper->__('Attribute')] = $szy_custom_attribute;
                $preset = $action['szy_attr_preset_' . $action['szy_attr_no']];
                $arguments[$helper->__('Value')] = ('custom' == $preset) ? $action['szy_attr_custom_text'] : $preset;
                break;
            case 'updateshippingcost_order':
                /*$value = array_shift($action['szy_base_shipping_cost']);
                $arguments[$helper->__('Shipping Cost')] = Mage::helper('core')->currency($value, TRUE, FALSE);
                */
                break;
            case 'ship_invoice_order':
            	$arguments[$helper->__('Notify Customer')] = $action['notify'] ? $helper->__('Yes') : $helper->__('No');
// 				$arguments[$helper->__('Action')] = $helper->__('Ship and Invoice');   
            case 'ship_order':
                $arguments[$helper->__('Notify Customer')] = $action['notify'] ? $helper->__('Yes') : $helper->__('No');
// 				$arguments[$helper->__('Action')] = $helper->__('Ship and Invoice');   

// 			   $track = array_shift($action['szy_tracking_number']);
//              $arguments[$helper->__('Tracking Number')] = "'$track'";  

                break;
            case 'assign_tracking':
                $arguments[$helper->__('Contact customer?')] = $action['customer_yes_no'] ? $helper->__('Yes') : $helper->__('No');
                $arguments[$helper->__('Tracking number')] = $action['custom_text'];
                break;
            case 'invoice_order':
				$arguments[$helper->__('Notify Customer')] = $action['notify'] ? $helper->__('Yes') : $helper->__('No');
// 				$arguments[$helper->__('Action')] = $helper->__('Invoice');   
                break;
            case 'order_change_status':
                $arguments[$helper->__('Notify Customer')] = $action['notify'] ? $helper->__('Yes') : $helper->__('No');
                $value = $action['status'];
// 				$arguments[$helper->__('Action')] = $helper->__('Update Status');   
                $arguments[$helper->__('Current Status')] = $this->getOrder()->getStatusLabel();
                break;
            default:
	            $arguments[]=$action['action'];
	            break; 
        }
		
        return $arguments;
    }

}

?>
