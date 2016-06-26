<?php

class Moogento_Clean_Adminhtml_Sales_NotificationsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function checkNotificationsAction()
    {
        $last_checked = "";
        $type_result = false; /* false - empty notification; true - message;  */
        $massage = "";
        
        $cookies_last_checked = Mage::getModel('core/cookie')->get('last_checked_order');
        
        $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('entity_id',array('gt' => "{$cookies_last_checked}"))
                ->setOrder('created_at','DESC');

        if (!is_null($cookies_last_checked)){
            $orderCnt = $orders->count();
            if( $orderCnt >= Mage::getStoreConfig('moogento_clean/notifications/delivery_rate_orders') ){
                if((Mage::getStoreConfig('moogento_clean/notifications/delivery_rate_orders') == 1) && (Mage::getStoreConfig('moogento_clean/notifications/show_order_summary')) && ($orderCnt ==1)){
                    foreach ($orders as $order){
                        $address = $order->getShippingAddress();
                        $region = (!is_null($address->getRegionId())) ? $address->getRegionCode() : $order->getRegion();
                        $massage.= Mage::getModel('core/store')->load($order->getStoreId())->getWebsite()->getName()." : ".$this->__('New order')."\n";
                        $massage.= $address->getFirstname();
                        if(!empty($region)){
                            $massage.= ' ('.$region.')';
                        }
                        $massage.= ' '.$address->getCountryId()."\n";
                        $items = $order->getAllVisibleItems();
                        foreach($items as $item){
                            $massage .= round($item->getQtyOrdered()).' x '.$item->getSku()."\n";
                        }
                    }
                } else {
                    $massage = $orderCnt.(($orderCnt == 1) ? $this->__(" new order!") : $this->__(" new orders!"));
                }
                $type_result = true;
                Mage::getModel('core/cookie')->set('last_checked_order', $orders->getFirstItem()->getEntityId(), time()+365*24*3600);
            }
        } else {
            Mage::getModel('core/cookie')->set('last_checked_order', $orders->getFirstItem()->getEntityId(), time()+365*24*3600);
            $this->getResponse()->setBody(json_encode(array("type" => false, "message" => "")));
            return;
        }

        if(Mage::getStoreConfig('moogento_clean/notification/item_out_of_stock')){
            $last_visit = !is_null(Mage::getModel('core/cookie')->get('last_date_visit')) ? Mage::getModel('core/cookie')->get('last_date_visit') : new DateTime('01/15/2010');;
            $products = Mage::getModel('moogento_clean/notification')->getCollection()->addFieldToFilter('create_at',array('gt' => "{$last_visit}"));
            $productsCnt = $products->count();
            if($productsCnt){
                $text_with_out_stock_product = "";
                foreach($products as $product){
                    $prod = Mage::getModel('catalog/product')->load($product->getProductId());
                    $text_with_out_stock_product .= $prod->getName()." (".$product->getProductId().") is out of stock. ";
                }
                if($type_result){
                    $massage .= $text_with_out_stock_product;
                } else {
                    $massage = $text_with_out_stock_product;
                }
                $type_result = true;
            }
        }
        
        Mage::getModel('core/cookie')->set('last_date_visit', date('Y-m-d H:i:s'), time()+365*24*3600);
        $this->getResponse()->setBody(json_encode(array("type" => $type_result, "message" => $massage)));
        return;
    }
    
    public function resetColorAction()
    {
        $this->getResponse()->setBody(json_encode(Mage::getStoreConfig('moogento_clean/colors_default')));
    }
}
 