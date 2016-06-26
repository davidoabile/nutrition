<?php
class NWH_Sales_Block_Order_History extends Mage_Sales_Block_Order_History{
    public function TrackingNumber($orderId){
        $trackNo = "";
        $order = Mage::getModel('sales/order')->load($orderId);
        $shipment_collection = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($order)
                    ->load();
        foreach($shipment_collection as $shipment){
            foreach($shipment->getAllTracks() as $tracking_number){
                $trackNo =  $tracking_number->getNumber();
            }
        }
        return $trackNo;
    }
}