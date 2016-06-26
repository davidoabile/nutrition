<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp extends AdjustWare_Deliverydate_Model_Rewrite_FrontCheckoutTypeMultishipping {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp extends Mage_Checkout_Model_Type_Multishipping {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping extends Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp
{
    public function createOrders()
    {
        $data = Mage::app()->getFrontController()->getRequest()->getPost('multi');
        $cfmModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        
        if ($data) {
            foreach ($data as $key => $val) {
                $cfmModel->setCustomValue($key, $val, 'multishipping');
            }
        }
        
        $result = parent::createOrders();

        // save attribute data to DB
        $orderIdHash = Mage::getSingleton('core/session')->getOrderIds(true);
        Mage::getSingleton('core/session')->setOrderIds($orderIdHash);

        if ($orderIdHash) {
            foreach ($orderIdHash as $orderId => $val) {
                $cfmModel->saveCustomOrderData($orderId, 'multishipping');
                
                $order = Mage::getModel('sales/order')->load($orderId);
                Mage::dispatchEvent('aitcfm_order_save_after', array('order' => $order, 'checkoutfields' => $order->getCustomFields()));
            }
            
            $cfmModel->clearCheckoutSession('multishipping');
        }
        
        return $result;
    }    
}