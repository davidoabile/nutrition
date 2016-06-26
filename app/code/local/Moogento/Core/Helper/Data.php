<?php


class Moogento_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_statuses = null;

    public function isInstalled($moduleName)
    {
        return Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true');
    }

    protected function _getStateByStatus($status)
    {
        if (is_null($this->_statuses)) {
            $this->_statuses = array();
            $collection = Mage::getResourceModel('sales/order_status_collection')->joinStates();
            foreach($collection as $one) {
                $this->_statuses[$one->getStatus()] = $one->getState();
            }
        }

        return isset($this->_statuses[$status]) ? $this->_statuses[$status] : null;
    }

    public function changeOrderStatus($orderId, $newStatus, $comment = '', $notifyCustomer = false, $additionalSettings = array())
    {
        if (Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM) {
            Mage::unregister('ignore_status_check');
            Mage::register('ignore_status_check', 1);
        }

        /** @var Mage_Sales_Model_Order $order */
        if ($orderId instanceof Mage_Sales_Model_Order) {
            $order = $orderId;
        } else {
            $order = Mage::getModel('sales/order')->load($orderId);
        }

        $currentState = $order->getState();
        $newState = $this->_getStateByStatus($newStatus);

        if ($newState && $order->getState() != $newState) {
            switch ($newState) {
                case Mage_Sales_Model_Order::STATE_CANCELED:
                    return $this->_customCancelOrder($order, $comment, $notifyCustomer, $additionalSettings);
                case Mage_Sales_Model_Order::STATE_HOLDED:
                    if($order->canHold()) {
                        if ($currentState == Mage_Sales_Model_Order::STATE_CANCELED) {
                            $this->_warnCancell();
                        }
                        $order->setHoldBeforeState($order->getState());
                        $order->setHoldBeforeStatus($order->getStatus());
                        $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, '', $notifyCustomer);
                        $order->save();
                        if ($notifyCustomer) {
                            $order->sendOrderUpdateEmail($notifyCustomer, '');
                        }
                        Mage::unregister('ignore_status_check');
                        return true;
                    }
                    break;
                case Mage_Sales_Model_Order::STATE_COMPLETE:
                    if ($currentState == Mage_Sales_Model_Order::STATE_CANCELED) {
                        $this->_warnCancell();
                    }
                    $transaction = Mage::getModel('core/resource_transaction');
                    $order = Mage::getModel('sales/order')->load($order->getId());

                    if ($order->canUnhold()) {
                        $order->setState($order->getHoldBeforeState(), $order->getHoldBeforeStatus(), '', $notifyCustomer);
                        $order->setHoldBeforeState(null);
                        $order->setHoldBeforeStatus(null);
                        $order->save();
                    }
                    $invoice = false;
                    $shipment = false;
                    if (Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM) {
                        if ($order->canInvoice()) {
                            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice(array());
                            $invoice->register();
                            $invoice->getOrder()->setIsInProcess(true);
                            $transaction->addObject($invoice);
                            $transaction->addObject($order);
                        }

                        if ($order->canShip()) {
                            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment(array());
                            $shipment->register();
                            $shipment->getOrder()->setIsInProcess(true);
                            $transaction->addObject($shipment);
                            $transaction->addObject($order);
                        }
                    }

                    $transaction->save();

                    $order = Mage::getModel('sales/order')->load($order->getId());
                    $order->setData('state', $newState);

                    // add status history
                    if ($newStatus) {
                        if ($newStatus === true) {
                            $newStatus = $order->getConfig()->getStateDefaultStatus($newState);
                        }
                        $order->setStatus($newStatus);
                        $order->addStatusHistoryComment($comment, false)
                            ->setIsVisibleOnFront($notifyCustomer)
                            ->setIsCustomerNotified($notifyCustomer); // no sense to set $status again
                    }

                    $order->save();

                    if ($notifyCustomer && Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM) {
                        if ($invoice) {
                            $invoice->sendEmail();
                        }
                        if ($shipment) {
                            $shipment->sendEmail();
                        }
                        if (Mage::getStoreConfigFlag('moogento_statuses/settings/send_complete_email')) {
                            $this->sendCompleteEmail($order);
                        }
                    }

                    Mage::unregister('ignore_status_check');
                    return true;
                default:
                    if ($currentState == Mage_Sales_Model_Order::STATE_CANCELED) {
                        $this->_warnCancell();
                    }
                    if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                        if($order->canUnhold()) {
                            $order->setState($order->getHoldBeforeState(), $order->getHoldBeforeStatus(), '', $notifyCustomer);
                            $order->setHoldBeforeState(null);
                            $order->setHoldBeforeStatus(null);
                            $order->save();
                        }
                    }

                    $order->setState($newState, $newStatus, '', $notifyCustomer);
                    $order->save();
                    if ($notifyCustomer) {
                        $order->sendOrderUpdateEmail($notifyCustomer, '');
                    }
                    Mage::unregister('ignore_status_check');
                    return true;
            }

            Mage::unregister('ignore_status_check');
            return false;
        } else {
            $order->addStatusHistoryComment($comment, $newStatus)
                ->setIsVisibleOnFront($notifyCustomer)
                ->setIsCustomerNotified($notifyCustomer);
            $order->save();
            Mage::unregister('ignore_status_check');
            return true;
        }
    }

    protected function _warnCancell()
    {
        Mage::getSingleton('adminhtml/session')->addWarning($this->__('Warning! You have changed the status from Canceled - Magento will now act unpredictably with things like: Payments, Refunds, Order Totals and Stats, Inventory. We recommend leaving the order Canceled, and making a new order.'));
    }

    protected function _getAllFieldsAttribute()
    {
        $addressForm = Mage::getModel('customer/form')
                           ->setFormCode('adminhtml_customer_address')
                           ->setStore(Mage::app()->getStore()->getId())
                           ->setEntity(Mage::getModel('customer/address'));
        $attributes = $addressForm->getAttributes();

        $list = array();
        foreach($attributes as $attribute){
            $list[] = $attribute->getAttributeCode();
        }
        return $list;
    }
    
    private function _getFormat($country_id)
    {
        $fields = $this->_getAllFieldsAttribute();
        $format = $this->getTemplate($country_id);
        $format = preg_replace("|(\r)?\n|im", '', $format);
        $format = preg_replace('|(<br(.*?)>){2,}|im', '', $format);

        return $format;
    }
    
    public function getTemplate($country_id = "")
    {
        if(!Mage::getStoreConfig('moogento_core/config/use_custom_address_formatting')){
            return Mage::getStoreConfig('customer/address_templates/html');
        } else {
            if(empty($country_id)){
                return Mage::getStoreConfig('customer/address_templates/html');
            } else { 
                $templates = Mage::getModel("moogento_core/country_template")->getCollection()
                        ->addFieldToFilter("country_code", $country_id)
                        ->addFieldToFilter("enable", 1)
                        ->setOrder('sort_number', 'ASC');
                if(count($templates) == 0){
                    return Mage::getStoreConfig('customer/address_templates/html');
                } else {
                    return $templates->getFirstItem()->getCountryTemplate();
                }
            }
        }
    }    

    public function formatAddress($address)
    {
        $format = $this->_getFormat($address->getCountryId());

        $type = new Varien_Object();
        $type->setCode('default')
            ->setDefaultFormat($format);

        $type->setRenderer(
            Mage::helper('customer/address')
                ->getRenderer('customer/address_renderer_default')->setType($type)
        );

        return $type->getRenderer()->render($address);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string $comment
     * @param boolean $isCustomerNotified
     * @param array $additionalSettings
     *
     * @return bool
     */
    protected function _customCancelOrder($order, $comment, $isCustomerNotified, $additionalSettings = array())
    {
        if (Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::MAGENTO_DEFAULT) {
            if (!$order->canCacell()) {
                return false;
            }
        }

        if ($order->canUnhold()) {
            $order->unhold()->save();
        }

        $transactionSave    = Mage::getModel('core/resource_transaction')
                                  ->addObject($order);
        $creditMemo         = false;
        $productBackToStock = isset($additionalSettings['product_back_to_stock'])
            ? (bool) $additionalSettings['product_back_to_stock']
            : Mage::getStoreConfigFlag('moogento_statuses/settings/return_items_to_stock');
        $refundPffline      = isset($additionalSettings['creditmemo_offline'])
            ? (bool) $additionalSettings['creditmemo_offline']
            : Mage::getStoreConfigFlag('moogento_statuses/settings/refund_offline');
        if ($order->canCreditmemo()) {
            $service = Mage::getModel('sales/service_order', $order);

            $creditMemo = $service->prepareCreditmemo(array());
            foreach ($creditMemo->getAllItems() as $item) {
                $product = Mage::getModel('catalog/product')->load($item->getOrderItem()->getProductId());
                if ($product->getId() && $product->getStockItem()->getManageStock()) {
                    $item->setBackToStock($productBackToStock);
                } else {
                    $item->setBackToStock(false);
                }
            }

            $args = array('creditmemo' => $creditMemo, 'request' => Mage::app()->getRequest());
            Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

            $creditMemo->setOfflineRequested($refundPffline);

            $creditMemo->register();
            $creditMemo->setEmailSent($isCustomerNotified);
            $creditMemo->getOrder()->setCustomerNoteNotify($isCustomerNotified);

            $transactionSave->addObject($creditMemo);
        }

        if ($order->canCancel()) {
            $order->getPayment()->cancel();
            $order->registerCancellation();
        }

        if ($creditMemo) {
            $creditMemo->sendEmail($isCustomerNotified, $comment);
        }

        try {

            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::STATE_CANCELED, $comment,
                $isCustomerNotified);

            $transactionSave->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        Mage::unregister('ignore_status_check');

        return true;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function sendCompleteEmail($order)
    {
        $storeId = $order->getStoreId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_COPY_TO, $storeId);
        $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig('moogento_statuses/settings/complete_email_guest_template', $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig('moogento_statuses/settings/complete_email_template', $storeId);
            $customerName = $order->getCustomerName();
        }

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is
        // 'copy' or a customer should not be notified
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'   => $order,
                'billing' => $order->getBillingAddress(),
                'tracking' => Mage::app()->getLayout()->createBlock('moogento_core/email_tracking')->setOrder($order)->toHtml(),
            )
        );

        $mailer->send();

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $this;
    }

    protected function _getEmails($configPath, $storeId = null)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
} 