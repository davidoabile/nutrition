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
* File        Observer.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_Sales_Order_Observer
{
    protected $_order = null;

    public function beforeSaveAttribute($observer)
    {

        $this->_order = $observer->getEvent()->getObject();
        return $this;
    }

    public function beforeGridUpdate($observer)
    {
        if ($this->_order && !in_array($this->_order->getId(), $observer->getEvent()->getProxy()->getIds())) {
            $this->_order = null;
        }

        return $this;
    }

    public function initGridColumn($observer)
    {
        $resource = $observer->getEvent()->getResource();

        $resource->addVirtualGridColumn(
            'szy_payment_method',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'method'
        );
        $resource->addVirtualGridColumn(
            'szy_shipping_method',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'shipping_method'
        );
        $resource->addVirtualGridColumn(
            'szy_shipping_description',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'shipping_description'
        );
        $resource->addVirtualGridColumn(
            'szy_customer_email',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'customer_email'
        );
        $resource->addVirtualGridColumn(
            'szy_weight',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'weight'
        );
        $resource->addVirtualGridColumn(
            'szy_customer_group_id',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'customer_group_id'
        );

        if ($this->_order && $this->_order->getIsVirtual()) {

            $resource->addVirtualGridColumn(
                'szy_customer_name',
                'sales/order_address',
                array('billing_address_id' => 'entity_id'),
                'CONCAT(IFNULL({{table}}.firstname, ""), " ", IFNULL({{table}}.lastname, ""))'
            );
            $resource->addVirtualGridColumn(
                'szy_country',
                'sales/order_address',
                array('billing_address_id' => 'entity_id'),
                '{{table}}.country_id'
            );
            $resource->addVirtualGridColumn(
                'szy_region',
                'sales/order_address',
                array('billing_address_id' => 'entity_id'),
                '{{table}}.region'
            );
            $resource->addVirtualGridColumn(
                'szy_postcode',
                'sales/order_address',
                array('billing_address_id' => 'entity_id'),
                '{{table}}.postcode'
            );
        } else {
            $resource->addVirtualGridColumn(
                'szy_customer_name',
                'sales/order_address',
                array('shipping_address_id' => 'entity_id'),
                'CONCAT(IFNULL({{table}}.firstname, ""), " ", IFNULL({{table}}.lastname, ""))'
            );
            $resource->addVirtualGridColumn(
                'szy_country',
                'sales/order_address',
                array('shipping_address_id' => 'entity_id'),
                '{{table}}.country_id'
            );
            $resource->addVirtualGridColumn(
                'szy_region',
                'sales/order_address',
                array('shipping_address_id' => 'entity_id'),
                '{{table}}.region'
            );
            $resource->addVirtualGridColumn(
                'szy_postcode',
                'sales/order_address',
                array('shipping_address_id' => 'entity_id'),
                '{{table}}.postcode'
            );
        }
    }
    
    public function core_block_abstract_to_html_after(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $additionalBlock = Mage::app()->getLayout()->createBlock('adminhtml/template');
            $additionalBlock->setTemplate('moogento/shipeasy/sales/order/view/m2epro.phtml');
            $additionalBlock->setParentBlock($block);
            $additionalBlock->setOrder($block->getOrder());
            $html .= $additionalBlock->toHtml();
            $transport->setHtml($html);
        }
    }
}
