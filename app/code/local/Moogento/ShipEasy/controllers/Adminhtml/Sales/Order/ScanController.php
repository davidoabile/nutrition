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
* File        ScanController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Adminhtml_Sales_Order_ScanController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @var Moogento_ShipEasy_Helper_Data
     */
    protected $_helper;

    protected $_fields = array( 
                'sales_order_grid_massaction_1',
                'sales_order_grid_massaction_2'
    );
    /**
     * @var array
     */
    protected $_scanParams = array();

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    protected $_messages = array();

    protected function _isAllowed()
    {
        return true;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('moogento_shipeasy');
    }

    protected function _addError($msg)
    {
        $this->_messages['error'][] = $msg;
        $this->_scanParams['action_status']['fail'] = $msg;
    }

    protected function _addSuccess($msg)
    {
        $this->_messages['success'][] = $msg;
        $this->_scanParams['action_status']['success'] = $msg;
    }

     public function formAction()
    {
    	$resource = Mage::getSingleton('core/resource');
     	try{
            $this->_initLayoutMessages('adminhtml/session');
            $items = Mage::helper('moogento_shipeasy/grid')->getMassActionItems();

            $formBlock = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history')
                    ->setItemsJson(json_encode($items))
                    ->setData("massaction_fields",$this->_fields)
                    ->setTemplate('moogento/shipeasy/sales/order/popup/scanform.phtml')
                    ->setReturnUrl($this->getUrl('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id', 0))));

            $historyBlock = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history')
                        ->setTemplate('moogento/shipeasy/sales/order/popup/history.phtml');
            
            $formBlock->setChild('actions_history', $historyBlock);

            foreach ($this->_fields as $element){
                $elem = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_extraaction');
                $elem   ->addItems($items)
                        ->setHtmlId($element);
                $formBlock->setChild($element, $elem);
            }

            $this->getResponse()->appendBody(
                $formBlock->toHtml()
            );
        }
        catch (Exception $e)
        {
            die($e->getMessage());
                $this->_initLayoutMessages('adminhtml/session');
                $formBlock = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history')
                        ->setTemplate('moogento/shipeasy/sales/order/popup/scanform.phtml')
                        ->setReturnUrl($this->getUrl('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id', 0))));

                $items = Mage::helper('moogento_shipeasy/grid')->getMassActionItems($formBlock);

            $historyBlock = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history')
                        ->setTemplate('moogento/shipeasy/sales/order/popup/history.phtml');
                $formBlock->setChild('actions_history', $historyBlock);

                $extraactionBlock_1 = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_extraaction');
                $extraactionBlock_1->addItems($items)
                        ->setHtmlId('sales_order_grid_massaction_1');
                $formBlock->setChild('sales_order_grid_massaction_1', $extraactionBlock_1);

                $extraactionBlock_2 = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_extraaction');
                $extraactionBlock_2->addItems($items)
                        ->setHtmlId('sales_order_grid_massaction_2');
                $formBlock->setChild('sales_order_grid_massaction_2', $extraactionBlock_2);

                $this->getResponse()->appendBody(
                        $formBlock->toHtml()
                );
        }
		
       
    }

    public function postAction()
    {
        $scanParameters = $this->getRequest()->getParam('scan', 0);
        $this->_messages = array();
        
        if (is_array($scanParameters)) {
            $bGotOrder = TRUE;
            $order = Mage::getModel('sales/order')->loadByIncrementId($scanParameters['order_number']);
            $scanParameters['order_id'] = $order->getId();
            
            if ($bGotOrder) {
                $this->_order = $order;
                $scanParameters['sales_order_grid_massaction_1']['order_ids'] = $scanParameters['sales_order_grid_massaction_2']['order_ids'] = array($scanParameters['order_id']);

                $this->_prepareRoutines($scanParameters['sales_order_grid_massaction_1']);
                $this->_prepareRoutines($scanParameters['sales_order_grid_massaction_2']);

				foreach($scanParameters as $key1 => $value1)
				{
					if(is_array($value1))
						foreach($value1 as $key2 => $value2)
						{
							if(is_string($value2))
								if(strpos($value2,'{{date}}|') !== false)
								{
									$today_date = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
									$scanParameters[$key1][$key2] = str_replace('{{date}}|',$today_date,$scanParameters[$key1][$key2]);
								}
						}
				}
				
                $scanParams = array(
                    $scanParameters['sales_order_grid_massaction_1'],
                    $scanParameters['sales_order_grid_massaction_2']
                );

                
                $log = Mage::getModel('moogento_shipeasy/sales_order_log');
                $log
                    ->setData(array(
                        'order_id' => $this->_order->getId(),
                        'actions_serialized' => serialize($scanParams),
                    ))
                    ->save();

            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Order number is invalid.'));
            }


            $historyBlock = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history')
                ->setTemplate('moogento/shipeasy/sales/order/popup/history.phtml');
            $historyBlock->setAjaxFor(true);

            $result = array(
                'msg' => $this->_messages,
                'history_content' => $historyBlock->toHtml()
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    protected function _prepareRoutines(&$scanParams)
    {
        if (isset($scanParams['szy_tracking_number'])) {
            $scanParams['szy_tracking_number'] = array($scanParams['order_ids'][0] => $scanParams['szy_tracking_number']);
        }
        if (isset($scanParams['szy_base_shipping_cost'])) {
            $scanParams['szy_base_shipping_cost'] = array($scanParams['order_ids'][0] => $scanParams['szy_base_shipping_cost']);
        }
        $this->_scanParams = $scanParams;

        $actionCode = $this->_getScanParam('action', '');
        if ('sEzySeparator1' != $actionCode) {
            $notify = $this->_getScanParam('notify', 0);

            if (!is_null($actionCode) && !is_null($notify)) {
                $szyDefault = Mage::getSingleton('adminhtml/session')->getSzyDefault();
                if (!is_array($szyDefault)) {
                    $szyDefault = array();
                }
                $szyDefault[$actionCode] = $notify;
                Mage::getSingleton('adminhtml/session')->setData('szy_default', $szyDefault);
            }
            switch ($actionCode) {
                case 'updateshippingcost_order':
                    $this->_updateShippingCostRoutine();
                    break;
                case 'ship_order':
                    $this->_massShipRoutine();
                    break;
                case 'invoice_order':
                    $this->_massInvoiceRoutine();
                    break;
                case 'ship_invoice_order':
                    $this->_massProcessRoutine();
                    break;
                case 'order_change_status':
                    $this->_updateStatusRoutine();
                    break;
                case 'custom_order_attribute':
                    $this->_updateAttributeRoutine();
                    break;
                case 'assign_tracking':
                    $this->_updateAssignTrackingRoutine();
                    break;
            }

        }

        return $this;
    }

    protected function _updateAssignTrackingRoutine()
    {
        $notifyCustomer = (bool)$this->_scanParams['customer_yes_no'];

        $trackNumber = $this->_scanParams['custom_text'];

        if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching')) {
            $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackNumber);
            if (!$carrierInfo) {
                $this->_getSession()->addError(Mage::helper('moogento_core')->__('No matching carrier found'));
            }
        }

        $orderIds = $this->_scanParams['order_ids'];

        $step = $this->_scanParams['step'];
            
        foreach($orderIds as $id) {
            $order = Mage::getModel('moogento_shipeasy/sales_order')->load($id);
            if(count($order->getShipmentsCollection()) == 0){
                if($step){
                    $itemsarray = array();
                    foreach($order->getAllItems() as $item) {
                        $item_id = (int)$item->getItemId();
                        $qty = (int)$item->getQtyOrdered();
                        $itemsarray[$item_id] = $qty;
                    }
                    if($order->canShip())
                    {
                        $incrementId = '';
                        try{
                            $shipment = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsarray ,'Programming...' ,false,1);
                            $incrementId = $shipment;
                            Mage::log('Create Shipment #'.$shipment.' for Order #'.$order->getId().' : '.date('d/m/y H:i.s'), null, 'szy.log');
                            Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $trackNumber);
                            Mage::log('Create TrackNumber #'.$trackNumber.' for Shipment #'.$shipment.' : '.date('d/m/y H:i.s'), null, 'szy.log');
                        } catch (Exception $ex) {
                            Mage::log($ex.' : '.date('d/m/y H:i.s'), null, 'szy.log');
                        }
                        if($notifyCustomer){
                            $shipment = Mage::getModel('sales/order_shipment')->load($incrementId, 'increment_id');
                            $this->_sendNotifyForCustomer($shipment, $incrementId);
                        }
                    }       
                }
            } else {
                foreach($order->getShipmentsCollection() as $shipment) {
                    try{
                        Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $trackNumber);
                        $shipment->save();
                        Mage::log('Create TrackNumber #'.$trackNumber.' for Shipment #'.$shipment->getIncrementId().' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
                    } catch (Exception $ex) {
                        Mage::log($ex.' : '.date('d/m/y H:i.s'), null, 'szy.log');
                    }
                    if($notifyCustomer){
                        $this->_sendNotifyForCustomer($shipment, $shipment->getIncrementId());
                    }
                }
            }
        }
        
    }
    
    protected function _sendNotifyForCustomer($shipment, $incrementId)
    {
        try{
            $shipment->sendEmail(true);
            $shipment->setEmailSent(true);
            $shipment->save();
            Mage::log('Send email for shipment #'.$incrementId.' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
        } catch (Exception $ex) {
            Mage::log($ex.' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
        }
    }
    
    protected function _updateAttributeRoutine()
    {
        $orderIds = $this->_scanParams['order_ids'];

        $attrNo = $this->_getScanParam('szy_attr_no', '');
        $preset = $this->_getScanParam('szy_attr_preset_' . $attrNo, '');

        if (is_array($orderIds) && count($orderIds) && $attrNo) {
            $countSuccess = 0;
            $countFail = 0;


            $text = '';

            if ($preset == 'custom') {
                $text = $this->_scanParams['szy_attr_custom_text'];
                $preset = 'transparent';
            } else {
                list($text, $preset) = explode('|', $preset);
            }
            
            if($text == "{{date}}")
			{
				$value = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
			}

            $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            foreach ($orderIds as $orderId) {
                try {
                    if($attrNo == 3)
                        $attributeCode = 'szy_custom_attribute3';
                    else
                        if($attrNo == 2)
                            $attributeCode = 'szy_custom_attribute2';
                        else
                            $attributeCode = 'szy_custom_attribute'; 
                    $resource->updateGridRow($orderId, $attributeCode, $text);
                    $countSuccess++;
                } catch (Exception $e) {
                    $countFail++;
                }
            }

            if ($countFail) {
                if ($countSuccess) {
                    $this->_addError($countFail.' '.$this->_helper->__('order(s) attributes cannot be updated.'));
                } else {
                    $this->_addError($this->_helper->__('The order(s) attributes cannot be updated.'));
                }
            }
            if ($countSuccess) {
                $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) attributes have been updated.'));
            }
        }
    }

    protected function _updateStatusRoutine()
    {
        if ($status = $this->_getScanParam('status', FALSE)) {

            $orderIds = $this->_scanParams['order_ids'];
            $countSuccess = 0;
            $countFail = 0;

            $notifyCustomer = (bool) $this->_getScanParam('notify', 0);

            foreach ($orderIds as $id) {
                try {
                    $order = Mage::getModel('sales/order')->load($id);

                    $order->addStatusHistoryComment('', $status)
                        ->setIsVisibleOnFront($notifyCustomer)
                        ->setIsCustomerNotified($notifyCustomer);
                    if ($notifyCustomer) {
                        $order->sendOrderUpdateEmail($notifyCustomer, '');
                    }
                    $order->setStatus($status);
                    $order->save();
                    $countSuccess++;
                } catch (Exception $e) {
                    $countFail++;
                }
            }
            if ($countFail) {
                if ($countSuccess) {
                    $this->_addError($countFail.' '.$this->_helper->__('order(s) statuses cannot be updated.'));
                } else {
                    $this->_addError($this->_helper->__('The order(s) statuses cannot be updated.'));
                }
            }
            if ($countSuccess) {
                $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) statuses have been updated.'));
            }
        }
    }

    protected function _updateShippingCostRoutine()
    {
        if ($this->getRequest()->isPost()) {

            $shippingCostInfo = $this->_getScanParam('szy_base_shipping_cost', array());

            if (array($shippingCostInfo) && count($shippingCostInfo)) {
                $countSuccess = 0;
                $countFail = 0;
                foreach ($shippingCostInfo as $orderId => $shippingCost) {

                    if (empty($shippingCost)) {
                        continue;
                    }

                    $order = Mage::getModel('sales/order')->load($orderId);
                    if ($order && $order->getId()) {
                        try {

                            preg_match("/^[A-Z]*/", $shippingCost, $matches);
                            $currencyCode = '';
                            if (count($matches) && isset($matches[0]) && !empty($matches[0])) {
                                $currencyCode = $matches[0];
                                $shippingCost = str_replace($currencyCode, '', $shippingCost);
                            } else {
                                $currencyCode = Mage::helper('moogento_shipeasy/currency')->getDefaultInputCurrencyCode();
                            }

                            $shippingCost = Mage::helper('moogento_shipeasy/currency')->convertCurrency(
                                $shippingCost, $currencyCode, $order->getGlobalCurrencyCode()
                            );

                            $order->setBaseShippingCost(($shippingCost) ? $shippingCost : 0);
                            $order->save();
                            $countSuccess++;
                        } catch (Exception $e) {
                            $countFail++;
                            Mage::logException($e);
                        }
                    } else {
                        $countFail++;
                    }
                }
                if ($countFail) {
                    if ($countSuccess) {
                        $this->_addError($countFail.' '.$this->_helper->__('order(s) cannot be updated.'));
                    } else {
                        $this->_addError($this->_helper->__('The order(s) cannot be updated.'));
                    }
                }
                if ($countSuccess) {
                    $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) have been updated.'));
                }
            }
        }
    }

    protected function _massInvoiceRoutine()
    {
        $orderIds = $this->_scanParams['order_ids'];
        $countSuccess = 0;
        $countFail = 0;

        $notifyCustomer = (bool) $this->_getScanParam('notify', 0);

        foreach ($orderIds as $id) {
            try {
                if ($invoice = Mage::helper('moogento_shipeasy/sales')->initInvoice($id)) {
                    $invoice->register();
                    $invoice->setEmailSent($notifyCustomer);
                    $invoice->getOrder()->setCustomerNoteNotify($notifyCustomer);
                    $invoice->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();
                    try {
                        $invoice->sendEmail($notifyCustomer, '');
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    $countSuccess++;
                } else {
                    $countFail++;
                }
            } catch (Exception $ex) {
                $countFail++;
            }
        }

        if ($countFail) {
            if ($countSuccess) {
                $this->_addError($countFail.' '.$this->_helper->__('order(s) cannot be invoiced.'));
            } else {
                $this->_addError($this->_helper->__('The order(s) cannot be invoiced.'));
            }
        }
        if ($countSuccess) {
            $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) have been invoiced.'));
        }
    }

    protected function _massShipRoutine()
    {
        $notifyCustomer = (bool) $this->_getScanParam('notify', 0);

        $shippingCostInfo = $this->_getScanParam('szy_base_shipping_cost', array());

        $orderIds = $this->_scanParams['order_ids'];

        $countSuccess = 0;
        $countFail = 0;
        foreach ($orderIds as $id) {
            try {
                if ($shipment = Mage::helper('moogento_shipeasy/sales')->initShipment($id)) {
                    $shipment->register();
                    $shipment->setEmailSent($notifyCustomer);
                    $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);

                    if (isset($shippingCostInfo[$id])) {
                        if (!empty($shippingCostInfo[$id])) {

                            $shippingCost = $shippingCostInfo[$id];

                            preg_match("/^[A-Z]*/", $shippingCost, $matches);
                            $currencyCode = '';
                            if (count($matches) && isset($matches[0]) && !empty($matches[0])) {
                                $currencyCode = $matches[0];
                                $shippingCost = str_replace($currencyCode, '', $shippingCost);
                            } else {
                                $currencyCode = Mage::helper('moogento_shipeasy/currency')->getDefaultInputCurrencyCode();
                            }

                            $shippingCost = Mage::helper('moogento_shipeasy/currency')->convertCurrency(
                                $shippingCost, $currencyCode, $shipment->getOrder()->getGlobalCurrencyCode()
                            );

                            $shipment->getOrder()->setBaseShippingCost($shippingCost);
                        }
                    }

                    Mage::helper('moogento_shipeasy/sales')->saveShipment($shipment);
                    $shipment->sendEmail($notifyCustomer, '');
                    $countSuccess++;
                } else {
                    $countFail++;
                }
            } catch (Exception $ex) {
                $countFail++;
            }
        }

        if ($countFail) {
            if ($countSuccess) {
                $this->_addError($countFail.' '.$this->_helper->__('order(s) cannot be shipped.'));
            } else {
                $this->_addError($this->_helper->__('The order(s) cannot be shipped.'));
            }
        }
        if ($countSuccess) {
            $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) have been shipped.'));
        }
    }

    protected function _massProcessRoutine()
    {
        $orderIds = $this->_scanParams['order_ids'];

        $notifyCustomer = (bool) $this->_getScanParam('notify', 0);
        $shippingCostInfo = $this->_getScanParam('szy_base_shipping_cost', array());
        $countSuccess = 0;
        $countFail = 0;

        foreach ($orderIds as $id) {
            try {
                if ($invoice = Mage::helper('moogento_shipeasy/sales')->initInvoice($id)) {
                    $invoice->register();
                    $invoice->setEmailSent($notifyCustomer);
                    $invoice->getOrder()->setCustomerNoteNotify($notifyCustomer);
                    $invoice->getOrder()->setIsInProcess(true);

                    if (isset($shippingCostInfo[$id])) {
                        if (!empty($shippingCostInfo[$id])) {

                            $shippingCost = $shippingCostInfo[$id];

                            preg_match("/^[A-Z]*/", $shippingCost, $matches);
                            $currencyCode = '';
                            if (count($matches) && isset($matches[0]) && !empty($matches[0])) {
                                $currencyCode = $matches[0];
                                $shippingCost = str_replace($currencyCode, '', $shippingCost);
                            } else {
                                $currencyCode = Mage::helper('moogento_shipeasy/currency')->getDefaultInputCurrencyCode();
                            }

                            $shippingCost = Mage::helper('moogento_shipeasy/currency')->convertCurrency(
                                $shippingCost, $currencyCode, $invoice->getOrder()->getGlobalCurrencyCode()
                            );

                            $invoice->getOrder()->setBaseShippingCost(
                                $shippingCost
                            );
                        }
                    }

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());

                    $shipment = Mage::helper('moogento_shipeasy/sales')->prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($notifyCustomer);
                        $transactionSave->addObject($shipment);
                    }
                    $transactionSave->save();
                    try {
                        $invoice->sendEmail($notifyCustomer, '');
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    if ($shipment) {
                        try {
                            $shipment->sendEmail($notifyCustomer, '');
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                    $countSuccess++;
                } else if ($shipment = Mage::helper('moogento_shipeasy/sales')->initShipment($id)) {
                    $shipment->register();
                    $shipment->setEmailSent($notifyCustomer);
                    $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);

                    if (isset($shippingCostInfo[$id])) {
                        if (!empty($shippingCostInfo[$id])) {
                            $shipment->getOrder()->setBaseShippingCost(
                                $shippingCostInfo[$id]
                            );
                        }
                    }

                    Mage::helper('moogento_shipeasy/sales')->saveShipment($shipment);
                    $shipment->sendEmail($notifyCustomer, '');
                    $countSuccess++;
                } else {
                    $countFail++;
                }
            } catch (Exception $ex) {
                $countFail++;
            }
        }

        if ($countFail) {
            if ($countSuccess) {
                $this->_addError($countFail.' '.$this->_helper->__('order(s) cannot be processed.'));
            } else {
                $this->_addError($this->_helper->__('The order(s) cannot be processed.'));
            }
        }
        if ($countSuccess) {
            $this->_addSuccess($countSuccess.' '.$this->_helper->__('order(s) have been processed.'));
        }
    }

    protected function _getScanParam($paramName, $paramDefault)
    {
        $scan = $this->_scanParams;
        return isset($scan[$paramName]) ? $scan[$paramName] : $paramDefault;
    }

    public function checkOrderShipmentAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $_order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if ($_order->getId()){
            if($_order->canShip()){
                if($_order->hasShip()){
                    $this->getResponse()->setBody(json_encode(array("result" => 2, "message" => "This order has shipment")));
                } else {
                    $this->getResponse()->setBody(json_encode(array("result" => 1, "message" => "This order hasn't shipment")));
                }
            } else {
                $this->getResponse()->setBody(json_encode(array("result" => 0, "message" => "This order can't have shipment")));
            }
        } else {
            $this->getResponse()->setBody(json_encode(array("result" => 0, "message" => "This order isn't exist")));
        }
    }
    
}
