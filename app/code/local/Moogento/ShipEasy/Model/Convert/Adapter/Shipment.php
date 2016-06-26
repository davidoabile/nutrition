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
 * File        Shipment.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Model_Convert_Adapter_Shipment extends Mage_Eav_Model_Convert_Adapter_Entity
{
    protected $_shipment;
    protected $_invoice;
    protected $_shipped = false;
    protected $_tracked = false;
    protected $_invoiced = false;
    protected $_processed = false;

    public function parse()
    {
        $batchModel = Mage::getSingleton('dataflow/batch');
        /* @var $batchModel Mage_Dataflow_Model_Batch */

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds        = $batchImportModel->getIdCollection();


        foreach ($importIds as $importId) {
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();
            $this->saveRow($importData);
        }
    }

    public function saveRow(array $importData, $additionalParams = array())
    {
        $this->_shipment = null;
        $this->_invoice  = null;
        $this->_shipped  = false;
        $this->_tracked  = false;
        $this->_invoiced = false;
        $this->_processed = false;

        $skuColumn = Mage::getStoreConfig('moogento_shipeasy/import/sku_column');
        $skuColumn = $skuColumn ? $skuColumn : 'sku';

        $special_flag = false;
        if ($additionalParams['action'] == 'ship') {
            if (isset($importData[ $skuColumn ]) && $importData[ $skuColumn ]) {
                $special_flag = true;
            }
        }

        $orderIncrementIdField = Mage::getStoreConfig('moogento_shipeasy/import/order_increment_id');
        $orderIncrementIdField = ($orderIncrementIdField) ? $orderIncrementIdField : 'order_increment_id';

        if (!isset($importData[ $orderIncrementIdField ]) || empty($importData[ $orderIncrementIdField ])) {
            $message = 'Required field ' . $orderIncrementIdField . ' ' . Mage::helper('moogento_shipeasy')->__(
                    'not defined : Skipping import row.'
                );
            Mage::throwException($message);
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($importData[ $orderIncrementIdField ]);
        if (!$order->getId()) {
            $message = 'Order ' . $importData[ $orderIncrementIdField ] . ' : ' . Mage::helper('moogento_shipeasy')->__(
                    'Skipping import row, this order was not found.'
                );
            Mage::throwException($message);
        }

        if ($order->getData('state') == Mage_Sales_Model_Order::STATE_HOLDED) {
            Mage::throwException('Order #' . $order->getIncrementId() . ' is holded,can not process it.');
        }

        if ($special_flag) {
            $this->_fixedRow($importData, $order);

            return;
        }

        $this->_importComments($order, $importData);

        switch ($additionalParams['action']) {
            case "ship":
                $this->_shipOrder($order, $importData, $additionalParams);
                break;
            case "invoice":
                $this->_invoiceOrder($order, $importData, $additionalParams);
                break;
            case "ship_invoice":
                $this->_invoiceShipOrder($order, $importData, $additionalParams);
                break;
            case "change_status":
                $this->_changeStatusOrder($order, $importData, $additionalParams);
                break;
            default:
                $this->_updateOrder($order, $importData, $additionalParams);
        }

    }

    protected function _fixedRow(array $importData, $order)
    {
        $session      = Mage::getSingleton('core/session');
        $tracking_key = Mage::getStoreConfig('moogento_shipeasy/import/tracking_info');
        $order_id_key = Mage::getStoreConfig('moogento_shipeasy/import/order_increment_id');
        $skuColumn    = Mage::getStoreConfig('moogento_shipeasy/import/sku_column');
        $skuColumn    = $skuColumn ? $skuColumn : 'sku';
        $qtyColumn    = Mage::getStoreConfig('moogento_shipeasy/import/qty_column');
        $qtyColumn    = $qtyColumn ? $qtyColumn : 'qty';
        $import_array = $session->getData('import_data_for_save') ? $session->getData('import_data_for_save') : array();
        $order_id     = $importData[ $order_id_key ];
        $tracking     = isset($importData[ $tracking_key ]) ? $importData[ $tracking_key ] : '__notracking__';
        $sku          = trim($importData[ $skuColumn ]);
        $skuFound     = false;
        foreach ($order->getAllItems() as $item) {
            if ($item->getSku() == $sku) {
                $skuFound = true;
            }
        }
        if (!$skuFound) {
            $message = 'Order ' . $order->getIncrementId() . ' : ' . Mage::helper('moogento_shipeasy')->__(
                    'Skipping import row, sku "' . $sku . '" not found in order'
                );
            Mage::throwException($message);
        }
        $qty
                                             =
            isset($importData[ $qtyColumn ]) && $importData[ $qtyColumn ] ? $importData[ $qtyColumn ] : false;
        $array_for_save                      = isset($import_array[ $order_id ]) ? $import_array[ $order_id ] : array();
        $array_for_save[ $tracking ]         = isset($array_for_save[ $tracking ]) ? $array_for_save[ $tracking ]
            : array();
        $array_for_save[ $tracking ][ $sku ] = $qty;
        $import_array[ $order_id ]           = $array_for_save;

        $session->setData('import_data_for_save', $import_array);
    }

    protected function _shipOrder($order, $importData = array(), $additionalParams = array())
    {
        $notifyCustomer = $additionalParams['notify_customer'];
        try {
            $this->_doShipOrder($order, $notifyCustomer);
            $this->_updateOrder($order, $importData, $additionalParams);
        } catch (Exception $e) {
            $message = 'Order ' . $order->getIncrementId() . ' : ' . Mage::helper('moogento_shipeasy')->__(
                    'Skipping import row, order already shipped.'
                );
            Mage::throwException($message);
        }
    }

    protected function _doShipOrder($order, $notifyCustomer)
    {
        if ($shipment = Mage::helper('moogento_shipeasy/sales')->initShipment($order)) {
            if ($order->canShip()) {
                $shipment->register();
                $shipment->setEmailSent($notifyCustomer);
                $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);
                $this->_shipped  = true;
                $this->_shipment = $shipment;
            }
        }
    }

    protected function _invoiceOrder($order, $importData = array(), $additionalParams = array())
    {
        $notifyCustomer = (empty($additionalParams['notify_customer'])) ? false : true;
        try {
            $this->_doInvoiceOrder($order, $notifyCustomer);
            $this->_updateOrder($order, $importData, $additionalParams);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage() . 'when process order #' . $order->getIncrementId());
        }
    }

    protected function _doInvoiceOrder($order, $notifyCustomer)
    {
        if ($invoice = Mage::helper('moogento_shipeasy/sales')->initInvoice($order)) {
            $invoice->setRequestedCaptureCase('online');
            $invoice->register();
            $invoice->setEmailSent($notifyCustomer);
            $invoice->getOrder()->setCustomerNoteNotify($notifyCustomer);
            $invoice->getOrder()->setIsInProcess(true);
            $this->_invoiced = true;
            $this->_invoice  = $invoice;
        }
    }

    public function _invoiceShipOrder($order, $importData = array(), $additionalParams = array())
    {
        $notifyCustomer = (empty($additionalParams['notify_customer'])) ? false : true;
        try {
            $this->_doShipOrder($order, $notifyCustomer);
            $this->_doInvoiceOrder($order, $notifyCustomer);
            $this->_updateOrder($order, $importData, $additionalParams);
        } catch (Exception $ex) {
            Mage::throwException($ex->getMessage() . '.End importing order #' . $order->getIncrementId());
        }
    }

    public function _changeStatusOrder($order, $importData = array(), $additionalParams = array())
    {
        $notifyCustomer = (empty($additionalParams['notify_customer'])) ? false : true;
        $status         = $additionalParams['status'];
        try {
            Mage::helper('moogento_core')->changeOrderStatus($order, $status, $notifyCustomer);
            $this->_updateOrder($order, $importData, $additionalParams);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage() . 'when process order #' . $order->getIncrementId());
        }
    }

    protected function _updateOrder($order, $importData, $additionalParams)
    {
        $notifyCustomer = (empty($additionalParams['notify_customer'])) ? false : true;

        $transactionSave = Mage::getModel('core/resource_transaction');
        if ($this->_invoice) {
            $transactionSave->addObject($this->_invoice);
        }
        if ($this->_shipment) {
            $transactionSave->addObject($this->_shipment);
        }
        $transactionSave->addObject($order);

        $trackingInfoField = Mage::getStoreConfig('moogento_shipeasy/import/tracking_info');
        $trackingInfoField = ($trackingInfoField) ? $trackingInfoField : 'tracking_info';

        $trackingInfo = array();
        if (isset($importData[ $trackingInfoField ]) && !empty($importData[ $trackingInfoField ])) {
            $trackingInfo = $importData[ $trackingInfoField ];
            if (strpos($trackingInfo, ',') !== false) {
                $trackingInfo = explode(',', $trackingInfo);
            } else {
                $trackingInfo = array($trackingInfo);
            }
        }

        if ($this->_shipment) {
            $shipment = $this->_shipment;
        } else {
            $shipment = $order->getShipmentsCollection()->getFirstItem();
        }

        if ($shipment->getId()) {
            $_currentTracks = array();
            foreach ($shipment->getAllTracks() as $_track) {
                $_currentTracks[] = md5($_track->getNumber() . $_track->getCarrierCode() . $_track->getTitle());
            }
            foreach ($trackingInfo as $trackText) {
                $track     = Mage::helper('moogento_core/carriers')
                                 ->addTrackingToShipment($shipment, trim($trackText), false, true);
                $trackHash = md5(
                    $track->getNumber() . $track->getCarrierCode() . $track->getTitle()
                );
                if (!in_array($trackHash, $_currentTracks)) {
                    $this->_tracked = true;
                    $shipment->addTrack($track);
                }
            }
            if ($this->_tracked) {
                $transactionSave->addObject($shipment);
            }
            if ($this->_tracked && $notifyCustomer && Mage::getStoreConfigFlag('moogento_shipeasy/import/add_track_email')) {
                Mage::helper('moogento_shipeasy/sales_order_shipment')->sendNewTracksEmail(
                    $shipment, $notifyCustomer
                );
            }
        } else {
            if (count($trackingInfo)) {
                $order->setData('preshipment_tracking', implode(',', $trackingInfo));
                $this->_tracked = true;
            }
        }


        $message = false;

        $result = new Varien_Object();
        Mage::dispatchEvent('moogento_shipeasy_import_csv', array(
            'order'      => $order,
            'import_data' => $importData,
            'result'     => $result,
        ));

        if ($result->getError()) {
            $message = $result->getMessage();
        }
        $this->_processed = $result->getProcessed();

        if (!$message) {
            if (!$this->_tracked && !$this->_invoiced && !$this->_shipped && !$this->_processed) {
                $message = 'Order ' . $order->getIncrementId() . ' : ' . Mage::helper('moogento_shipeasy')->__(
                        'Skipping import row, no action taken'
                    );
            }
        }

        if ($message) {
            Mage::throwException($message);
        }

        $transactionSave->save();
        if ($notifyCustomer) {
            if ($this->_invoice) {
                $this->_invoice->sendEmail($notifyCustomer, '');
            }
            if ($this->_shipment) {
                $this->_shipment->sendEmail($notifyCustomer, '');
            }
        }

        if (isset($additionalParams['additional_action_1'])
            && $additionalParams['additional_action_1']
        ) {
            Mage::getResourceModel('moogento_shipeasy/sales_order')->updateGridRow(
                $order->getId(),
                'szy_custom_attribute',
                $additionalParams['additional_action_value_1']
            );
        }

        if (isset($additionalParams['additional_action_2'])
            && $additionalParams['additional_action_2']
        ) {
            Mage::getResourceModel('moogento_shipeasy/sales_order')->updateGridRow(
                $order->getId(),
                'szy_custom_attribute2',
                $additionalParams['additional_action_value_2']
            );
        }

        if (isset($additionalParams['additional_action_3'])
            && $additionalParams['additional_action_3']
        ) {
            Mage::getResourceModel('moogento_shipeasy/sales_order')->updateGridRow(
                $order->getId(),
                'szy_custom_attribute3',
                $additionalParams['additional_action_value_3']
            );
        }

        $order->save();
    }

    protected function _importComments($order, $importData)
    {
        $commentInfoField = Mage::getStoreConfig('moogento_shipeasy/import/order_comment');
        $commentInfoField = ($commentInfoField) ? $commentInfoField : 'order_comment';

        $commentInfo = array();
        if (isset($importData[ $commentInfoField ]) && !empty($importData[ $commentInfoField ])) {
            $commentInfo = $importData[ $commentInfoField ];
            if (strpos($commentInfo, ';') !== false) {
                $commentInfo = explode(';', $commentInfo);
            } else {
                $commentInfo = array($commentInfo);
            }
        }

        if (count($commentInfo)) {
            foreach ($commentInfo as $comment) {
                $order->addStatusHistoryComment($comment);
            }
        }
        
        $commentPublicInfoField = Mage::getStoreConfig('moogento_shipeasy/import/order_comment_public');
        $commentPublicInfoField = ($commentPublicInfoField) ? $commentPublicInfoField : 'order_comment_public';

        $commentPublicInfo = array();
        if (isset($importData[ $commentPublicInfoField ]) && !empty($importData[ $commentPublicInfoField ])) {
            $commentPublicInfo = $importData[ $commentPublicInfoField ];
            if (strpos($commentPublicInfo, ';') !== false) {
                $commentPublicInfo = explode(';', $commentPublicInfo);
            } else {
                $commentPublicInfo = array($commentPublicInfo);
            }
        }

        if (count($commentPublicInfo)) {
            foreach ($commentPublicInfo as $comment) {
                $order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true);
            }
        }
        
        if (count($commentPublicInfo) || count($commentInfo)) {
            $order->save();
        }
    }
}
