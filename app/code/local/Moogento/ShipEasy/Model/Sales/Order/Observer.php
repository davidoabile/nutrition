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


class Moogento_ShipEasy_Model_Sales_Order_Observer
{
    protected function _getBaseOrderCost(Mage_Sales_Model_Order $order)
    {
        $baseCost = 0;
        foreach($order->getAllVisibleItems() as $item) {
            $baseCost += $item->getBaseCost() * $item->getQtyOrdered();
        }

        return $baseCost;
    }

    protected function _checkIfOrderGetsCompleted($order)
    {
        if (!$order->isCanceled()
            && !$order->canUnhold()
            && !$order->canInvoice()
            && !$order->canShip()) {
            if ($order->canCreditmemo()) {
                if ($order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function _getAdditionalCharges($order)
    {

        $fixedAmount = Mage::getStoreConfig('moogento_shipeasy/charges/fixed_amount');
        $percentAmount = Mage::getStoreConfig('moogento_shipeasy/charges/percent_amount');
        return $order->getBaseGrandTotal() * $percentAmount / 100 + $fixedAmount;
    }

    public function orderBeforeSave($observer)
    {
        $order = $observer->getOrder();

        if (!$order->getId()) {
            $address = $order->getShippingAddress();
            if (!$address){
                $address = $order->getBillingAddress();
            }
            $country = (!$address->getCountry()) ? "" : $address->getCountry();
            $query = <<<HEREDOC
                SELECT tz.gmt_offset
                FROM `shipeasy_timezone_timezone` tz JOIN `shipeasy_timezone_zone` z
                ON tz.zone_id=z.zone_id
                WHERE tz.time_start < UNIX_TIMESTAMP(UTC_TIMESTAMP()) AND z.country_code='{$country}'
                ORDER BY tz.time_start DESC LIMIT 1;
HEREDOC;
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $data = $read->fetchOne($query);
            
            $order->setTimezoneOffset($data);
            
            $phone = $address->getTelephone();
            if($phone != ""){
                if(substr($phone, 0, 1) == 0){
                    try {
                        $country = Mage::getModel('directory/country')->loadByCode($address->getCountryId());
                        if ($country && $country->getId()) {
                            if (!is_null($country->getMobileCode())) {
                                $phone = "+" . $country->getMobileCode() . substr($phone, 1);
                            }
                        }
                    } catch (Exception $e) {}
                }
            }
            $order->setPhone($phone);

        }

        return $this;
    }

    public function orderAfterSave($observer)
    {
        $order = $observer->getOrder();
        $sku = false;
        $name = false;
        $_allowedProductTypes = array('bundle', 'simple', 'virtual', 'downloadable');
        foreach($order->getItemsCollection() as $orderItem) {
            $productType = $orderItem->getProductType();
            if (in_array($productType, $_allowedProductTypes)) {
                $sku = (!$sku) ? $orderItem->getSku() : $sku . ',' . $orderItem->getSku();
                $name = (!$name) ? $orderItem->getName() : $name . ',' . $orderItem->getName();
            }
        }

        if ($sku) {
            Mage::getResourceSingleton('moogento_shipeasy/sales_order')->updateGridRow(
                $order,
                'szy_product_skus',
                $sku
            );
        }

        if ($name) {
            Mage::getResourceSingleton('moogento_shipeasy/sales_order')->updateGridRow(
                $order,
                'szy_product_names',
                $name
            );
        }
        
        if(!$order->getOrigData('id')){
            $address = $order->getShippingAddress();
            if (!$address){
                $address = $order->getBillingAddress();
            }
            $email = $address->getEmail();
            
            if($cust_id = $order->getCustomerId()){
                $customer = Mage::getModel('customer/customer')->load($cust_id);
                $cust_email = $customer->getEmail();
                if($cust_email != $email) {
                    $customer_email_list = $customer->getEmail().' '.$email;
                } else {
                    $customer_email_list = $customer->getEmail();
                }
            } else {
                $customer_email_list = $email;
            }
            Mage::helper('moogento_shipeasy/sales')->updateOnlyOrderGrigAttribute($order->getId(),'customer_email_list',$customer_email_list);
        }
    }

    public function sales_order_shipment_save_before($observer)
    {
        $shipment = $observer->getShipment();

        $order = $shipment->getOrder();

        if ($order->getData('preshipment_tracking') && count($shipment->getAllTracks()) == 0) {
            @list($trackingNumber, $carrier) = explode('||', $order->getData('preshipment_tracking'));
            Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $trackingNumber, $carrier);
            $order->setData('preshipment_tracking', '');
            $order->save();
        }
    }
    
    public function saveAddressAfter($observer)
    {
        $address = $observer->getAddress();
        $order = $address->getOrder();
        if($order->getId()) {
            if($address->getAddressType() == "shipping") {
                $shippingAddress = $address;
                $billingAddress = $order->getBillingAddress();
            } else {
                $billingAddress = $address;
                $shippingAddress = $order->getShippingAddress();
            }
            if ($shippingAddress && $shippingAddress->getTelephone()) {
                $phone = $shippingAddress->getTelephone();
            } else {
                $phone = $billingAddress->getTelephone();
            }
            if ($shippingAddress) {
                $postcode = $shippingAddress->getPostcode();
            } else {
                $postcode = $billingAddress->getPostcode();
            }

            if($phone){
                if(substr($phone, 0, 1) == 0){
                    try {
                        $country = Mage::getModel('directory/country')->loadByCode($address->getCountryId());
                        if ($country->getId()) {
                            if (!is_null($country->getMobileCode())) {
                                $phone = "+" . $country->getMobileCode() . substr($phone, 1);
                            }
                        }
                    } catch (Exception $e) {}
                }
            }
            $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            $resource->updateGridRow($order->getId(), "phone", (string)$phone);
            $resource->updateGridRow($order->getId(), "szy_postcode", (string)$postcode);
        }
    }
    
    /* Set fieald in_stock_at_create_moment 
     * 0 - not in stock
     * 1 - in stock but (Produqt QTY - Item QTY) < 0
     * 2 - in stock and (Produqt QTY - Item QTY) >= 0
     */
    public function itemBeforeSave($observer)
    {
        $item = $observer->getItem();
        if($item->isObjectNew()){
            $product_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
            $stock = $product_stock->getIsInStock();
            /*if(!$stock){
                $item->setData('in_stock_at_create_moment', 0);
            } else {*/
                if ($product_stock->getQty() >= $product_stock->getMinQty()){
                    $item->setData('in_stock_at_create_moment', 2);
                } elseif (($product_stock->getQty() + $item->getQtyOrdered()) > $product_stock->getMinQty()) {
                    $item->setData('in_stock_at_create_moment', 1);
                } else {
                    $item->setData('in_stock_at_create_moment', 0);
                }
            //}
        }
        return $this;
    }
}
