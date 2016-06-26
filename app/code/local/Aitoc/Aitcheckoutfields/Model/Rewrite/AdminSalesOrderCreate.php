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
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate_Aittmp extends AdjustWare_Deliverydate_Model_Rewrite_AdminhtmlSalesOrderCreate {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate_Aittmp extends Mage_Adminhtml_Model_Sales_Order_Create {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate extends Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate_Aittmp
{
    // overwrite parent
    public function createOrder()
    {
        $mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        $oldOrderId='';
        
        /* {#AITOC_COMMENT_END#}
        if(isset($_SESSION['adminhtml_quote']['order_id'])||isset($_SESSION['adminhtml_quote']['reordered']))
        {
            $oldOrderId=isset($_SESSION['adminhtml_quote']['order_id'])?$_SESSION['adminhtml_quote']['order_id']:$_SESSION['adminhtml_quote']['reordered'];
            $oldOrder = Mage::getModel('sales/order')->load($oldOrderId);
            $storeId = $oldOrder->getStoreId();
            $websiteId = $oldOrder->getStore()->getWebsiteId();
        }else{
        	$quote = $this->getQuote();
        	$storeId = $quote->getStoreId();
            $websiteId = $quote->getStore()->getWebsiteId();
        }
        
        $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckoutfields')->getLicense()->getPerformer();
        $ruler = $performer->getRuler();
        if (!($ruler->checkRule('store',$storeId,'store') || $ruler->checkRule('store',$websiteId,'website')))
        {
        	if($oldOrderId)
        	{
        		$oldData = $mainModel->getOrderCustomData($oldOrderId, $storeId, true);
        		foreach ($oldData as $oldAttr){
        			if(in_array($oldAttr['type'],array('checkbox','radio','select','multiselect')))
        			{
        				$oldAttr['rawval'] = explode(',',$oldAttr['rawval']);
        			}
        			$_SESSION['aitoc_checkout_used']['adminorderfields'][$oldAttr['id']]=$oldAttr['rawval'];
        		} 
        	}
        }
        else 
        {
        {#AITOC_COMMENT_START#} */
            $attributeCollection = $mainModel->getAttributeCollecton();
            $data = Mage::app()->getRequest()->getPost('aitoc_checkout_fields');
            
            foreach($attributeCollection as $attribute)
            {
                if(isset($data[$attribute->getAttributeCode()]))
                {
                    if($attribute->getFrontend()->getInputType()!=='static')
                    {
                        $_SESSION['aitoc_checkout_used']['adminorderfields'][$attribute->getId()]=$data[$attribute->getAttributeCode()];
                    }
                }
            }
        /* {#AITOC_COMMENT_END#}
        }
        {#AITOC_COMMENT_START#} */
        
   	/************** AITOC DELIVERY DATE COMPATIBILITY MODE: START ********************/
        
        $val = Mage::getConfig()->getNode('modules/AdjustWare_Deliverydate/active');
        if ((string)$val == 'true')
        {
        	// START AITOC DELIVERY DATE
            $errors = Mage::getModel('adjdeliverydate/step')->process('shippingMethod');
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->getSession()->addError($error);
                }
                Mage::throwException('');
            }
            
    		// FINISH AITOC DELIVERY DATE
            
            $this->_validate();
    
            if (!$this->getQuote()->getCustomerIsGuest()) {
                $this->_putCustomerIntoQuote();
            }
    
            $quoteConvert = Mage::getModel('sales/convert_quote');
    
            /* @var $quoteConvert Mage_Sales_Model_Convert_Quote */
    
            $quote = $this->getQuote();
            if (!$this->getSession()->getOrder()->getId()) {
                $quote->reserveOrderId();
            }
    
            if ($this->getQuote()->getIsVirtual()) {
                $order = $quoteConvert->addressToOrder($quote->getBillingAddress());
            }
            else {
                $order = $quoteConvert->addressToOrder($quote->getShippingAddress());
            }
            $order->setBillingAddress($quoteConvert->addressToOrderAddress($quote->getBillingAddress()))
                ->setPayment($quoteConvert->paymentToOrderPayment($quote->getPayment()));
            if (!$this->getQuote()->getIsVirtual()) {
                $order->setShippingAddress($quoteConvert->addressToOrderAddress($quote->getShippingAddress()));
            }
    
            if (!$this->getQuote()->getIsVirtual()) {
                foreach ($quote->getShippingAddress()->getAllItems() as $item) {
                    /* @var $item Mage_Sales_Model_Quote_Item */
                    $orderItem = $quoteConvert->itemToOrderItem($item);
                    $options = array();
                    if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {
                        $productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
                        $options = $productOptions;
                    }
                    if ($addOptions = $item->getOptionByCode('additional_options')) {
                        $options['additional_options'] = unserialize($addOptions->getValue());
                    }
                    if ($options) {
                        $orderItem->setProductOptions($options);
                    }
    
                    if ($item->getParentItem()) {
                        $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                    }
    
                    $order->addItem($orderItem);
                }
            }
            if ($this->getQuote()->hasVirtualItems()) {
                foreach ($quote->getBillingAddress()->getAllItems() as $item) {
                    /* @var $item Mage_Sales_Model_Quote_Item */
                    $orderItem = $quoteConvert->itemToOrderItem($item);
                    $options = array();
                    if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {
                        $productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
                        $options = $productOptions;
                    }
                    if ($addOptions = $item->getOptionByCode('additional_options')) {
                        $options['additional_options'] = unserialize($addOptions->getValue());
                    }
                    if ($options) {
                        $orderItem->setProductOptions($options);
                    }
    
                    if ($item->getParentItem()) {
                        $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                    }
    
                    $order->addItem($orderItem);
                }
            }
    
            if ($this->getSendConfirmation()) {
                $order->setEmailSent(true);
            }
    
            if ($this->getSession()->getOrder()->getId()) {
                $oldOrder = $this->getSession()->getOrder();
    
                $originalId = $oldOrder->getOriginalIncrementId() ? $oldOrder->getOriginalIncrementId() : $oldOrder->getIncrementId();
                $order->setOriginalIncrementId($originalId);
                $order->setRelationParentId($oldOrder->getId());
                $order->setRelationParentRealId($oldOrder->getIncrementId());
                $order->setEditIncrement($oldOrder->getEditIncrement()+1);
                $order->setIncrementId($originalId.'-'.$order->getEditIncrement());
            }
    
            $order->place();
            $this->_saveCustomerAfterOrder($order);
            $order->save();
    
            if ($this->getSession()->getOrder()->getId()) {
                $oldOrder = $this->getSession()->getOrder();
    
                $this->getSession()->getOrder()->setRelationChildId($order->getId());
                $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
                $this->getSession()->getOrder()->cancel()
                    ->save();
                $order->save();
            }
    
    		// AITOC DELIVERY DATE
            $fields = Mage::getSingleton('adjdeliverydate/session')->getShippingMethod();
            
            //only one step and only two field for the first module version
            if (is_array($fields)){
                
                // fix for delivery time
                if (Mage::getStoreConfig('checkout/adjdeliverydate/show_time') AND !empty($fields['delivery_date']) AND !empty($fields['delivery_time']))
                {
                    $fields['delivery_date'] .= ' ' . implode(':', $fields['delivery_time']);
                    
                    unset($fields['delivery_time']);
                }
                
                foreach ($fields as $name=>$value){
                   $order->setData($name, $value);
                   $order->setData($name . '_is_formated', true);
                }
                $order->save();
            }
            
            Mage::getSingleton('adjdeliverydate/session')->setShippingMethod(null);
    		// END AITOC DELIVERY DATE
    
            if ($this->getSendConfirmation()) {
                $order->sendNewOrderEmail();
            }
        }
        else
        {
        	$order = parent::createOrder();
        }
    /************** AITOC DELIVERY DATE COMPATIBILITY MODE: FINISH ********************/
            
        if (isset($_SESSION['aitoc_checkout_used']['new_customer']))
        {
            $_SESSION['aitoc_checkout_used']['register'] = $_SESSION['aitoc_checkout_used']['adminorderfields'];
            $customerId = $order->getCustomerId();
            $mainModel->saveCustomerData($customerId);
        }
        
        $orderId = $order->getId();
        $mainModel->saveCustomOrderData($orderId, 'adminorderfields');
        
        Mage::dispatchEvent('aitcfm_order_save_after', array('order' => $order, 'checkoutfields' => $order->getCustomFields()));
        
        $mainModel->clearCheckoutSession('adminorderfields');
        Mage::getSingleton('adminhtml/session')->unsetData('aitcheckoutfields_admin_post_data');
           		
        return $order;
    }
    
    // overwrite parent
    public function importPostData($data){
        $toReturn = parent::importPostData($data);
        		
		if($postData = Mage::app()->getRequest()->getPost('aitoc_checkout_fields'))
		{
		    if(!Mage::getSingleton('adminhtml/session')->hasData('aitcheckoutfields_admin_post_data'))
			{
			    Mage::getSingleton('adminhtml/session')->addData(array('aitcheckoutfields_admin_post_data'=>$postData));
			}
			elseif($postData != Mage::getSingleton('adminhtml/session')->getData('aitcheckoutfields_admin_post_data'))
			{
			    Mage::getSingleton('adminhtml/session')->addData(array('aitcheckoutfields_admin_post_data'=>$postData));
			}
		}
		        
        return $toReturn; 
    }
}