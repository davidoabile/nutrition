<?php
class Balance_Addressduplicate_Model_Observer{
	
    function fix_duplicate_shipping_lastname($observer){
        $order = $observer->getEvent()->getOrder();
        $shippingmodel = Mage::getModel('sales/order_address');
        $shipping = $order->getShippingAddress()->getData();
        $shippingmodel->load($shipping['entity_id']);
        $firstname=$shippingmodel->getFirstname();
        $lastname=$shippingmodel->getLastname();
        $_fn=explode(' ',$firstname);
        $new_fn=array();
        foreach($_fn as $name){
            if(strtolower($name) != strtolower($lastname))
            $new_fn[]=$name;
        }
        $firstname=implode(' ',$new_fn);
        if(!empty($firstname)) $shippingmodel->setFirstname($firstname)->save();
    }
}