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
 * File        ProcessController.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Adminhtml_Sales_Order_ProcessController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $actionCode = $this->getRequest()->getPost('action_code', null);
        $notify     = $this->getRequest()->getPost('notify', null);

        if (!is_null($actionCode) && !is_null($notify)) {
            $szyDefault = Mage::getSingleton('adminhtml/session')->getSzyDefault();
            if (!is_array($szyDefault)) {
                $szyDefault = array();
            }
            $szyDefault[ $actionCode ] = $notify;
            Mage::getSingleton('adminhtml/session')->setData('szy_default', $szyDefault);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function updateAttribute1Action()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $attrNo   = $this->getRequest()->getPost('attr', 1);
        $preset   = $this->getRequest()->getParam('preset' . $attrNo, '');

        if (is_array($orderIds) && count($orderIds) && $attrNo) {
            $countSuccess = 0;
            $countFail    = 0;
            $text         = '';
            if ($preset == 'custom') {
                $text   = $this->getRequest()->getParam('custom_text', '');
                $preset = 'transparent';
            } else {
                @list($text, $preset) = explode('|', $preset);
            }

            $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                try {
                    if ($attrNo == 3) {
                        $attributeCode = 'szy_custom_attribute3';
                    } else if ($attrNo == 2) {
                        $attributeCode = 'szy_custom_attribute2';
                    } else {
                        $attributeCode = 'szy_custom_attribute';
                    }

                    $resource->updateGridRow($orderId, $attributeCode, $text);
                    $countSuccess++;
                } catch (Exception $e) {
                    $countFail++;
                }
            }

            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) attributes cannot be updated.'));
                } else {

                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) attributes cannot be updated.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) attributes have been updated.'));
            }
        }
        $this->_redirect('*/sales_order/');
    }

    public function updateCustomAction()
    {
        $orderId  = (int) $this->getRequest()->getParam('orderId');
        $attrNo   = $this->getRequest()->getParam('attribute');
        $value    = $this->getRequest()->getParam('value');
        $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
        try {
            if ($attrNo == 3) {
                $attributeCode = 'szy_custom_attribute3';
            } else if ($attrNo == 2) {
                $attributeCode = 'szy_custom_attribute2';
            } else {
                $attributeCode = 'szy_custom_attribute';
            }
            if ($value == "{{date}}") {
                $value = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
            }

            $resource->updateGridRow($orderId, $attributeCode, $value);
            $return              = array();
            $return['orderId']   = $orderId;
            $return['attribute'] = $attrNo;
            $return['value']     = $value;
            $html                = '<div class="custom_color">' . $value . '</div>';
            $color               = '';
            $flag                = '';
            $check_value         = '';
            $render_data         = Mage::helper('moogento_shipeasy/functions')->renderCustom($attributeCode, $value);

            try {
                if (isset($render_data['flag']) && (strlen($render_data['flag']) > 0)) {
                    $flag      = $render_data['flag'];
                    $flag      = trim($flag);
                    $flag      = str_replace('{{', '', $flag);
                    $flag      = str_replace('}}', '', $flag);
                    $flag      = str_replace('{', '', $flag);
                    $flag      = str_replace('}', '', $flag);
                    $image_url = Mage::getDesign()->getSkinUrl('moogento/shipeasy/images/flag_images/' . $flag);
                    $html      = '<div class="custom_color"><img style="height: 25px !important;" class="custom_flag szy_grid_image" src="' . $image_url . '" /></div>';
                } else if (DateTime::createFromFormat('d-m-Y', $value) !== false) {
                    $html = '<div class="custom_color date_value">' . $value . '</div>';
                } else if (isset($render_data['color']) && (strlen($render_data['color']) > 0)) {
                    $color = $render_data['color'];
                    $color = ' background-color:' . $color;
                    $html  = '<div class="custom_color" style="padding: 2px;' . $color . '">' . $value . '</div>';
                } else {
                    $html = '<div class="custom_color">' . $value . '</div>';
                }
                $return['html'] = $html;
                echo json_encode($return);
                exit;

            } catch (Exception $e) {
                $html = '&nbsp;';
            }
        } catch (Exception $e) {
            $html = '&nbsp;';
        }

    }

    public function updateStatusAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('status')) {

            $orderIds     = $this->getRequest()->getPost('order_ids', array());
            $countSuccess = 0;
            $countFail    = 0;
            $status       = $this->getRequest()->getPost('status');

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            foreach ($orderIds as $id) {
                try {
                    if (Mage::helper('moogento_core')->changeOrderStatus($id, $status, '', $notifyCustomer)) {
                        $countSuccess++;
                    } else {
                        $countFail++;
                    }
                } catch (Exception $e) {
                    $countFail++;
                }
            }
            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) statuses cannot be updated.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) statuses cannot be updated.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) statuses have been updated.'));
            }

        }
        $this->_redirect('*/sales_order/');
    }
    
    public function updateStatusByJsAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('status')) {
            $result = array();
            
            $orderId      = $this->getRequest()->getPost('order_id');
            $countSuccess = 0;
            $countFail    = 0;
            $status       = $this->getRequest()->getPost('status');

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            try {
                if (Mage::helper('moogento_core')->changeOrderStatus($orderId, $status, '', $notifyCustomer)) {
                    $countSuccess++;
                } else {
                    $countFail++;
                }
            } catch (Exception $e) {
                $countFail++;
            }
            if ($countFail) {
                if ($countSuccess) {
                    $result[] = array('error' => Mage::helper('moogento_shipeasy')->__('The order statuses cannot be updated.'));
                } else {
                    $result[] = array('error' => Mage::helper('moogento_shipeasy')->__('The order statuses cannot be updated.'));
                }
            }
            if ($countSuccess) {
                $result[] = array('success' => Mage::helper('moogento_shipeasy')->__('Order status have been updated.'));
            }
            
            Mage::app()->getResponse()->setBody(json_encode($result));
        }

    }

    protected function _getColor($value, $attributeCode)
    {
        $configValues = Mage::getStoreConfig('moogento_shipeasy/grid/' . $attributeCode . '_preset');

        $configValuesLines = explode("\n", $configValues);
        foreach ($configValuesLines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            @list($label, $color) = explode('|', $line);
            if ($label == $value) {
                return $color;
            }
        }

        return 'transparent';
    }

    protected function _getFlag($value, $attributeCode)
    {
        $configValues = Mage::getStoreConfig('moogento_shipeasy/grid/' . $attributeCode . '_preset');

        $configValuesLines = explode("\n", $configValues);
        foreach ($configValuesLines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            @list($label, $flag) = explode('|', $line);
            if ($label == $value) {
                return $flag;
            }
        }

        return 'no-flag';
    }

    protected function _getPreset($attrNo)
    {
        $preset_return = array();

        for ($i = 1; $i <= 3; $i++) {
            if ($i == 1) {
                $configSuffix = 'szy_custom_attribute_preset';
            } else if ($i == 2) {
                $configSuffix = 'szy_custom_attribute2_preset';
            } else {
                $configSuffix = 'szy_custom_attribute3_preset';
            }
            $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $configSuffix);
            $configPresets = explode("\n", $configPresets);

            $presets = array();
            foreach ($configPresets as $preset) {
                $preset = trim($preset);
                if (empty($preset)) {
                    continue;
                }

                if (strpos($preset, '|') !== false) {
                    @list($label, $color) = explode('|', $preset);
                    $presets[ $preset ] = $label;
                } else {
                    $presets[] = $preset;
                }
            }
            $presets['custom'] = 'New Value';

            $preset_return[ $i ] = $presets;
        }

        return $preset_return[ $attrNo ];
    }

    public function updateshippingcostAction()
    {
        if ($this->getRequest()->isPost()) {
            $shippingCostInfo = $this->getRequest()->getPost('szy_base_shipping_cost', false);
            if (array($shippingCostInfo) && count($shippingCostInfo)) {
                $countSuccess = 0;
                $countFail    = 0;
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
                                $currencyCode = Mage::helper('moogento_shipeasy/currency')
                                                    ->getDefaultInputCurrencyCode();
                            }
                            $shippingCost = Mage::helper('moogento_shipeasy/currency')->convertCurrency(
                                $shippingCost,
                                $currencyCode,
                                $order->getGlobalCurrencyCode()
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
                        $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                              ->__('order(s) cannot be updated.'));
                    } else {
                        $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                           ->__('The order(s) cannot be updated.'));
                    }
                }
                if ($countSuccess) {
                    $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                               ->__('order(s) have been updated.'));
                }
            }
        }
        $this->_redirect('*/sales_order/');
    }

    public function massInvoiceAction()
    {
        if ($this->getRequest()->isPost()) {
            $trackingNumbers = $this->getRequest()->getPost('szy_tracking_number', array());
            $carriers = $this->getRequest()->getPost('tracking_carrier', array());

            $orderIds     = $this->getRequest()->getPost('order_ids', array());
            $countSuccess = 0;
            $countFail    = 0;

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            foreach ($orderIds as $id) {
                try {
                    if ($invoice = Mage::helper('moogento_shipeasy/sales')->initInvoice($id)) {
                        $invoice->setRequestedCaptureCase($invoice->canCapture() ? 'online' : 'offline');
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
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) cannot be invoiced.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) cannot be invoiced.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) have been invoiced.'));
            }
            $this->_processTrackingNumbers($trackingNumbers, $carriers);
        }
        $this->_redirect('*/sales_order/');
    }

    public function massShipAction()
    {
        if ($this->getRequest()->isPost()) {
            $trackingNumbers = $this->getRequest()->getPost('szy_tracking_number', array());
            $carriers = $this->getRequest()->getPost('tracking_carrier', array());

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            $orderIds   = $this->getRequest()->getPost('order_ids', array());

            $countSuccess = 0;
            $countFail    = 0;
            foreach ($orderIds as $id) {
                try {
                    if ($shipment = Mage::helper('moogento_shipeasy/sales')->initShipment($id)) {
                        $allow_ship_without_invoice
                                             = Mage::getStoreConfig('moogento_shipeasy/general/allow_ship_without_invoice');
                        $statusProcessing = Mage::getStoreConfig('moogento_statuses/settings/status_processing');
                        $order               = Mage::getModel('sales/order')->load($id);
                        $canShip            = false;
                        $status   = false;
                        if (count($order->getInvoiceCollection()) > 0) {
                            $canShip = true;
                        } else if (($statusProcessing == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM) && ($allow_ship_without_invoice == 1)) {
                            $status   = 'shipped';
                            $canShip = true;
                        }

                        if ($canShip) {
                            $shipment->register();
                            $shipment->setEmailSent($notifyCustomer);
                            $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);

                            if (isset($trackingNumbers[$id])) {
                                $trackingNumber = trim($trackingNumbers[$id]);
                                $carrier = isset($carriers[$id]) ? $carriers[$id] : '';
                                if ($trackingNumber) {
                                    Mage::helper('moogento_core/carriers')
                                        ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                    unset($trackingNumbers[$id]);
                                }
                            }
                            if ($status) {
                                $shipment->getOrder()->setStatus($status);
                            }

                            Mage::helper('moogento_shipeasy/sales')->saveShipment($shipment);
                            Mage::helper('moogento_core')->changeOrderStatus($id, $status, '', $notifyCustomer);
                            $shipment->sendEmail($notifyCustomer, '');
                            $countSuccess++;
                        } else {
                            $countFail++;
                        }
                    } else {
                        $countFail++;
                    }
                } catch (Exception $ex) {
                    $countFail++;
                }
            }

            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) cannot be shipped.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) cannot be shipped.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) have been shipped.'));
            }

            $this->_processTrackingNumbers($trackingNumbers, $carriers);
        }
        $this->_redirect('*/sales_order/');
    }

    public function massAssigntrackingAction()
    {
        $orders_without_shipment        = array();
        $orders_number_without_shipment = array();

        if ($this->getRequest()->isPost()) {

            $notifyCustomer = (bool) $this->getRequest()->getPost('customer_yes_no', 0);

            $trackNumber = $this->getRequest()->getPost('custom_text', "");

            $orderIds = $this->getRequest()->getPost('order_ids', array());

            $step = $this->getRequest()->getPost('step');

            switch ($step) {
                case 1:
                    $trackNumbers = array();
                    foreach ($orderIds as $id) {
                        $order = Mage::getModel('sales/order')->load($id);
                        if (count($order->getShipmentsCollection()) == 0) {
                            array_push($orders_without_shipment, $id);
                            array_push($orders_number_without_shipment, $order->getIncrementId());
                        } else {
                            foreach ($order->getShipmentsCollection() as $shipment) {
                                try {
                                    $track = Mage::getModel('sales/order_shipment_api')
                                                 ->addTrack($shipment->getIncrementId(), 'custom', 'Custom',
                                                     $trackNumber);
                                    Mage::log('Create TrackNumber #' . $trackNumber . ' for Shipment #'
                                              . $shipment->getIncrementId() . ' : ' . date('d/m/y H:i.s'), null,
                                        'szy.log');
                                } catch (Exception $ex) {
                                    Mage::log($ex . ' : ' . date('d/m/y H:i.s'), null, 'szy.log');
                                }
                                if ($notifyCustomer) {
                                    try {
                                        $shipment->sendEmail($notifyCustomer);
                                        $shipment->setEmailSent($notifyCustomer);
                                        $shipment->save();
                                        Mage::log('Send email for shipment #' . $shipment->getIncrementId() . ' : '
                                                  . date('d/m/y H:i.s'), null, 'szy.log');
                                    } catch (Exception $ex) {
                                        Mage::log($ex . ' : ' . date('d/m/y H:i.s'), null, 'szy.log');
                                    }
                                }
                            }
                        }
                    }
                    $result_array                   = array();
                    $result_array['orders_id']      = $orders_without_shipment;
                    $result_array['orders_numbers'] = $orders_number_without_shipment;
                    echo json_encode($result_array);
                    break;
                case 2:
                    $orders_for_new_shipment = json_decode($this->getRequest()->getPost('orders_for_new_shipment', ""));
                    foreach ($orders_for_new_shipment as $id) {
                        $order      = Mage::getModel('sales/order')->load($id);
                        $itemsarray = array();
                        foreach ($order->getAllItems() as $item) {
                            $item_id                = (int) $item->getItemId();
                            $qty                    = (int) $item->getQtyOrdered();
                            $itemsarray[ $item_id ] = $qty;
                        }
                        if ($order->canShip()) {
                            $incrementId = '';
                            try {
                                $shipment    = Mage::getModel('sales/order_shipment_api')
                                                   ->create($order->getIncrementId(), $itemsarray, 'Programming...',
                                                       false, 1);
                                $incrementId = $shipment;
                                Mage::log('Create Shipment #' . $shipment . ' for Order #' . $order->getId() . ' : '
                                          . date('d/m/y H:i.s'), null, 'szy.log');
                                $trackmodel = Mage::getModel('sales/order_shipment_api')
                                                  ->addTrack($shipment, 'custom', 'Custom', $trackNumber);
                                Mage::log('Create TrackNumber #' . $trackNumber . ' for Shipment #' . $shipment . ' : '
                                          . date('d/m/y H:i.s'), null, 'szy.log');
                            } catch (Exception $ex) {
                                Mage::log($ex . ' : ' . date('d/m/y H:i.s'), null, 'szy.log');
                            }
                            if ($notifyCustomer) {
                                try {
                                    $shipment = Mage::getModel('sales/order_shipment')
                                                    ->load($incrementId, 'increment_id');
                                    $shipment->sendEmail($notifyCustomer);
                                    $shipment->setEmailSent($notifyCustomer);
                                    $shipment->save();
                                    Mage::log('Send email for shipment #' . $incrementId . ' : ' . date('d/m/y H:i.s'),
                                        null, 'szy.log');
                                } catch (Exception $ex) {
                                    Mage::log($ex . ' : ' . date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
                                }
                            }
                        }
                    }
                case 3:
                    $this->_redirect('*/sales_order/');
                    break;
            }
        }
    }

    public function massProcessAction()
    {
        if ($this->getRequest()->isPost()) {
            $trackingNumbers = $this->getRequest()->getPost('szy_tracking_number', array());
            $carriers = $this->getRequest()->getPost('tracking_carrier', array());

            $notifyCustomer = (bool) $this->getRequest()->getPost('notify', 0);

            $orderIds                 = $this->getRequest()->getPost('order_ids', array());
            $countSuccess             = 0;
            $countFail                = 0;

            foreach ($orderIds as $id) {
                try {
                    if ($invoice = Mage::helper('moogento_shipeasy/sales')->initInvoice($id)) {
                        $invoice->setRequestedCaptureCase($invoice->canCapture() ? 'online' : 'offline');
                        $invoice->register();
                        $invoice->setEmailSent($notifyCustomer);
                        $invoice->getOrder()->setCustomerNoteNotify($notifyCustomer);
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = Mage::getModel('core/resource_transaction')
                                               ->addObject($invoice)
                                               ->addObject($invoice->getOrder());

                        $shipment = Mage::helper('moogento_shipeasy/sales')->prepareShipment($invoice);
                        if ($shipment) {
                            if (isset($trackingNumbers[$id])) {
                                $trackingNumber = trim($trackingNumbers[$id]);
                                $carrier = isset($carriers[$id]) ? $carriers[$id] : '';
                                if ($trackingNumber) {
                                    Mage::helper('moogento_core/carriers')
                                        ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                    unset($trackingNumbers[$id]);
                                }
                            }
                            $shipment->setEmailSent($notifyCustomer);
                            $transactionSave->addObject($shipment);
                        }
                        $transactionSave->save();

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

                        if (isset($trackingNumbers[$id])) {
                            $trackingNumber = trim($trackingNumbers[$id]);
                            $carrier = isset($carriers[$id]) ? $carriers[$id] : '';
                            if ($trackingNumber) {
                                Mage::helper('moogento_core/carriers')
                                    ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                unset($trackingNumbers[$id]);
                            }
                        }

                        Mage::helper('moogento_shipeasy/sales')->saveShipment($shipment);
                        $shipment->sendEmail($notifyCustomer, '');
                        $countSuccess++;

                    } else {
                        $countFail++;
                    }
                } catch (Exception $ex) {
                    Mage::log($ex);
                    $countFail++;
                }
            }

            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('order(s) cannot be processed.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The order(s) cannot be processed.'));
                }
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('order(s) have been processed.'));
            }

            $this->_processTrackingNumbers($trackingNumbers, $carriers);
        }
        $this->_redirect('*/sales_order/');
    }

    public function saveTrackingAction()
    {
        $result = array();

        $id    = $this->getRequest()->getPost('id');
        $order = Mage::getModel('sales/order')->load($id);
        if ($order->getId()) {
            $trackingNumber = $this->getRequest()->getPost('tracking_number');
            $carrier        = $this->getRequest()->getPost('carrier');
            if ($trackingNumber) {
                if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching') && !$carrier) {
                    $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackingNumber);
                    if (!$carrierInfo) {
                        $result['error'] = Mage::helper('moogento_core')->__('No matching carrier found');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        return;
                    }
                }
                    if ($carrier) {
                        $trackingNumber .= '||' . $carrier;
                    }
                    if ($order->getData('courierrules_tracking')) {
                        $order->setData('courierrules_tracking', $trackingNumber);
                    } else {
                        $order->setData('preshipment_tracking', $trackingNumber);
                    }

                    if ($order->getData('preshipment_tracking')) {
                        foreach ($order->getShipmentsCollection() as $shipment) {
                            if (count($shipment->getAllTracks()) == 0) {
                                @list($trackingNumber, $carrier) = explode('||',
                                    $order->getData('preshipment_tracking'));
                                Mage::helper('moogento_core/carriers')
                                    ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                $shipment->save();
                            }
                            $order->setData('preshipment_tracking', '');
                        }
                    }

                    $order->save();

                    $result['html'] = $this->getLayout()
                                           ->createBlock('moogento_shipeasy/adminhtml_widget_grid_column_renderer_input_label')
                                           ->render($order);
            } else {
                $result['error'] = Mage::helper('moogento_shipeasy')->__('Tracking number not specified');
            }
        } else {
            $result['error'] = Mage::helper('moogento_shipeasy')->__('No such order');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function deleteTrackingAction()
    {
        $id    = $this->getRequest()->getPost('id');
        $type  = $this->getRequest()->getPost('type');
        switch($type){
           case 'tracking_number':
               Mage::getModel('sales/order_shipment_track')->load($id)->delete();
               break;
           case 'track_link_not_fixed':
               $order = Mage::getModel('sales/order')->load($id);
               $order->setPreshipmentTracking("");
               $order->save();
               break;           
        }        
    }

    public function addTrackingAction()
    {
        $result = array();

        $id    = $this->getRequest()->getPost('id');
        $order = Mage::getModel('sales/order')->load($id);
        if ($order->getId()) {
            $trackingNumber = $this->getRequest()->getPost('tracking_number');
            $carrier        = $this->getRequest()->getPost('carrier');
            if ($trackingNumber) {
                if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching') && !$carrier) {
                    $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackingNumber);
                    if (!$carrierInfo) {
                        $result['error'] = Mage::helper('moogento_core')->__('No matching carrier found');
                    }
                }
                if (!isset($result['error'])) {
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        Mage::helper('moogento_core/carriers')
                            ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                        $shipment->save();
                        $shipment->sendEmail((int) $this->getRequest()->getPost('notify'), 0);
                    }

                    $order->save();

                    $result['html'] = $this->getLayout()
                                           ->createBlock('moogento_shipeasy/adminhtml_widget_grid_column_renderer_input_label')
                                           ->render($order);
                }
            } else {
                $result['error'] = Mage::helper('moogento_shipeasy')->__('Tracking number not specified');
            }
        } else {
            $result['error'] = Mage::helper('moogento_shipeasy')->__('No such order');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function editTrackingAction()
    {
        $result = array();

        $id    = $this->getRequest()->getPost('id');
        $order = Mage::getModel('sales/order')->load($id);
        if ($order->getId()) {
            $trackingNumber    = trim($this->getRequest()->getPost('tracking_number'));
            $carrier           = trim($this->getRequest()->getPost('carrier'));
            $oldTrackingNumber = trim($this->getRequest()->getPost('old_tracking_number'));
            if ($trackingNumber && $oldTrackingNumber) {
                if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching') && !$carrier) {
                    $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackingNumber);
                    if (!$carrierInfo) {
                        $result['error'] = Mage::helper('moogento_core')->__('No matching carrier found');
                    }
                }

                if (!isset($result['error'])) {
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        foreach ($shipment->getAllTracks() as $track) {
                            if (trim($track->getNumber()) == $oldTrackingNumber) {
                                $track->addData(Mage::helper('moogento_core/carriers')
                                                    ->getTrackingData($order, $carrier));
                                $track->setNumber($trackingNumber);
                                $track->save();
                            }
                        }
                        $shipment->save();
                        $shipment->sendEmail((int) $this->getRequest()->getPost('notify'), 0);
                    }

                    $order->save();

                    $result['html'] = $this->getLayout()
                                           ->createBlock('moogento_shipeasy/adminhtml_widget_grid_column_renderer_input_label')
                                           ->render($order);
                }
            } else {
                $result['error'] = Mage::helper('moogento_shipeasy')->__('Tracking number not specified');
            }
        } else {
            $result['error'] = Mage::helper('moogento_shipeasy')->__('No such order');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _processTrackingNumbers($trackingNumbers, $carriers)
    {
        if (count($trackingNumbers)) {
            $countSuccess = 0;
            $countFail = 0;
            $errors = array();
            foreach ($trackingNumbers as $order_id => $trackingNumber) {
                if (trim($trackingNumber)) {
                    $trackingNumber = trim($trackingNumber);
                    $carrier = isset($carriers[$order_id]) ? $carriers[$order_id] : '';
                    if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching') && !$carrier) {
                        $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackingNumber);
                        if (!$carrierInfo) {
                            $countFail++;
                            $errors[] = Mage::helper('moogento_core')->__('No matching carrier found for code %s', $trackingNumber);
                            continue;
                        }
                    }

                    $order = Mage::getModel('sales/order')->load($order_id);
                    if ($order->getId()) {
                        if ($carrier) {
                            $trackingNumber .= '||' . $carrier;
                        }
                        if ($order->getData('courierrules_tracking')) {
                            $order->setData('courierrules_tracking', $trackingNumber);
                        } else {
                            $order->setData('preshipment_tracking', $trackingNumber);
                        }

                        if ($order->getData('preshipment_tracking')) {
                            foreach ($order->getShipmentsCollection() as $shipment) {
                                if (count($shipment->getAllTracks()) == 0) {
                                    @list($trackingNumber, $carrier) = explode('||',
                                        $order->getData('preshipment_tracking'));
                                    Mage::helper('moogento_core/carriers')
                                        ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                    $shipment->save();
                                }
                                $order->setData('preshipment_tracking', '');
                            }
                        }

                        try {
                            $order->save();
                            $countSuccess++;
                        } catch (Exception $e) {
                            $countFail++;
                            $errors[] = $e->getMessage();
                        }
                    } else {
                        $countFail++;
                    }
                }
            }
            if ($countFail) {
                if ($countSuccess) {
                    $this->_getSession()->addError($countFail . ' ' . Mage::helper('moogento_shipeasy')
                                                                          ->__('tracking number(s) cannot be processed.'));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')
                                                       ->__('The tracking numbers cannot be processed.'));
                }
                $errors = array_unique($errors);
                $this->_getSession()->addError(implode('<br/>', $errors));
            }
            if ($countSuccess) {
                $this->_getSession()->addSuccess($countSuccess . ' ' . Mage::helper('moogento_shipeasy')
                                                                           ->__('tracking number(s) have been processed.'));
            }
        }
    }
    
    public function editCustomerAction()
    {
        $order_id    = $this->getRequest()->getParam('order_id');
        $customer_id  = $this->getRequest()->getPost('customer_id');
        
        $order = Mage::getModel('sales/order')->load($order_id);

        if($order->getId()) {
            $customer = Mage::getModel('customer/customer')->load($customer_id);

            $order->setCustomerId($customer_id);
            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerMiddlename($customer->getMiddlename());
            $order->setCustomerPrefix($customer->getPrefix());
            $order->setCustomerTaxvat($customer->getTaxvat());
            $order->setCustomerEmail($customer->getEmail());

            $order->save();
        }
    }
    
    public function orderEmailEditFromGridAction()
    {
        $order_id    = $this->getRequest()->getPost('orderid');
        $email  = $this->getRequest()->getPost('email');
        
        $order = Mage::getModel('sales/order')->load($order_id);

        if($order->getId()){
            $order->setEmailFromAdmin($email);
            $order->setCustomerEmail($email);

            $order->save();
        }
        
        $row = Mage::getResourceModel('sales/order_grid_collection')->addFilter('entity_id',$order_id)->getFirstItem();

        $result = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_grid_email')
            ->setOrder($row)
            ->toHtml();
        $this->getResponse()->setBody($result);
    }

    public function customerAutoCompleteAction()
    {
        $query = $this->getRequest()->getParam('q');

        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->getSelect()->limit(10);
        $collection->addAttributeToFilter(array(
            array('attribute' => 'firstname', 'like' => $query . '%'),
            array('attribute' => 'lastname', 'like' => $query . '%'),
        ));
        $result = array();

        foreach ($collection as $customer) {
            $result[$customer->getId()] = $customer->getName();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
