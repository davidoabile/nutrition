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
* File        Sales.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Helper_Sales extends Mage_Core_Helper_Abstract
{
    protected $_productsCache = array();

    public function initInvoice($order)
    {
        if (!$order instanceof Mage_Sales_Model_Order) {
            $order = Mage::getModel('sales/order')->load($order);
        }
        $statusProcessing = Mage::getStoreConfig('moogento_statuses/settings/status_processing');
        if($statusProcessing == Moogento_Core_Model_System_Config_Source_Status_Processing::MAGENTO_DEFAULT) {
            if (!$order->getId() || !$order->canInvoice()) {
                return false;
            }
        }
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice(array());
        if (!$invoice->getTotalQty()) {
            return false;
        }
        return $invoice;
    }

    public function initShipment($order)
    {
        if (!$order instanceof Mage_Sales_Model_Order) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!$order->getId()) {
            return false;
        }

        if ($order->getForcedDoShipmentWithInvoice()) {
            return false;
        }
        $statusProcessing = Mage::getStoreConfig('moogento_statuses/settings/status_processing');
        if($statusProcessing == Moogento_Core_Model_System_Config_Source_Status_Processing::MAGENTO_DEFAULT) {
            if (!$order->canShip()) {
                return false;
            }
        }

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment(array());
        return $shipment;
    }

    public function saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave
            ->addObject($shipment)
            ->addObject($shipment->getOrder());
        $transactionSave->save();

        return $this;
    }

    public function prepareShipment($invoice)
    {
        $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())->prepareShipment(array());
        if (!$shipment->getTotalQty()) {
            return false;
        }
        $shipment->register();
        return $shipment;
    }

    public function getOrderLogRecord($bCount = 1)
    {
        $records = FALSE;

        $collection = Mage::getResourceModel('moogento_shipeasy/sales_order_log_collection');
        $collection->addOrder('updated_at', Varien_Data_Collection::SORT_ORDER_DESC)
            ->attachOrderInstances();

       if ($bCount <= 100) {
           $collection->setPageSize($bCount)->setCurPage(0);
       }
        if ($collection->getSize()) {
            $records = array_values($collection->getItems());
        }
        return $records;
    }

    public function getOrderItemProduct($item)
    {
        if (!$item->getData('product')) {
            if (!isset($this->_productsCache[$item->getProductId()])) {
                $this->_productsCache[$item->getProductId()] = Mage::getModel('catalog/product')->load($item->getProductId());
            }
            $item->setProduct($this->_productsCache[$item->getProductId()]);
        }

        return $item->getProduct();
    }

    public function updateOrderAttribute($order, $column, $data)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $orderId = $order;
        if ($order instanceof Mage_Sales_Model_Order) {
            $orderId = $order->getId();
        }

        $write->update(
            Mage::getSingleton('core/resource')->getTableName('sales/order'),
            array($column => $data),
            $write->quoteInto('entity_id = ?', $orderId)
        );

        $write->update(
            Mage::getSingleton('core/resource')->getTableName('sales/order_grid'),
            array($column => $data),
            $write->quoteInto('entity_id = ?', $orderId)
        );
    }
    
    public function updateOnlyOrderGrigAttribute($order, $column, $data)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $orderId = $order;
        if ($order instanceof Mage_Sales_Model_Order) {
            $orderId = $order->getId();
        }

        $write->update(
            Mage::getSingleton('core/resource')->getTableName('sales/order_grid'),
            array($column => $data),
            $write->quoteInto('entity_id = ?', $orderId)
        );
    }
    
    public function getOrderM2EProData($magento_order = null)
    {
        $data = array();
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $magento_order_id = $magento_order->getId();
            $order_m2epro = Mage::getModel("M2ePro/Order")->load($magento_order_id, 'magento_order_id');
            if($order_m2epro->getId()){
                $component_mode = $order_m2epro->getComponentMode();
                
                switch ($component_mode){
                    case "amazon":
                        $order = Mage::getModel("M2ePro/Amazon_Order")->load($order_m2epro->getId());
                        $data['target'] = $this->__('Amazon');
                        $data['data'] = array();

                        $data['products']= array(); 
                        foreach($magento_order->getAllItems() as $index => $item){
                            $additionalData = @unserialize($item->getAdditionalData());
                            if ($additionalData && isset($additionalData['m2epro_extension']['items'][0]['order_item_id'])) {
                                $product = Mage::getModel("M2ePro/Amazon_Order_Item")
                                               ->load($additionalData['m2epro_extension']['items'][0]['order_item_id'], 'amazon_order_item_id');
                                if ($product->getId()) {
                                    $data['products'][$index]                  = array();
                                    $data['products'][$index]['magento_link']  = $item->getProduct()->getProductUrl();
                                    $data['products'][$index]['merchant_link'] = Mage::helper('M2ePro/Component_Amazon')
                                                                                     ->getItemUrl($product->getGeneralId());
                                    $data['products'][$index]['name']          = $item->getName();
                                }
                            }
                        }
                        $data['order_link'] = Mage::helper('M2ePro/Component_Amazon')->getOrderUrl($order->getAmazonOrderId());
                        break;
                    case "ebay":
                        $order = Mage::getModel("M2ePro/Ebay_Order")->load($order_m2epro->getId());
                        $data['target'] = $this->__('Ebay');
                        $data['data'] = array();
                        
                        $data['data'][] = array(
                                    'title' => $this->__('User ID '),
                                    'data' => $order->getBuyerUserId()
                                );
                        foreach($magento_order->getAllItems() as $index => $item) {
                            $additionalData = @unserialize($item->getAdditionalData());
                            if ($additionalData && isset($additionalData['m2epro_extension']['items'][0]['item_id'])) {
                                $data['products'][$index]['magento_link']  = $item->getProduct()->getProductUrl();
                                $data['products'][$index]['merchant_link'] = Mage::helper('M2ePro/Component_Ebay')
                                                                                 ->getItemUrl($additionalData['m2epro_extension']['items'][0]['item_id']);
                                $data['products'][$index]['name']          = $item->getName();
                            }
                        }
                        break;
                    
                }
                if($order->getId()){
                    $data['id'] = $order->getId();
                    $data['data'][] = array(
                                'title' => $this->__('Email'),
                                'data' => $order->getData('buyer_email')
                            );
                    $data['data'][] = array(
                                'title' => $this->__('Actual date/time from ').$data['target'],
                                'data' => $order->getData('purchase_create_date')
                            );
                }
            }
        }
        return $data;
    }
}
