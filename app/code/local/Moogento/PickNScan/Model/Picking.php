<?php

class Moogento_PickNScan_Model_Picking extends Mage_Core_Model_Abstract
{
    const STATUS_ASSIGNED = 1;
    const STATUS_STARTED = 2;
    const STATUS_COMPLETE_ANOMALIES = 3;
    const STATUS_COMPLETE = 4;

    protected $_order = null;

    protected function _construct()
    {
        $this->_init('moogento_pickscan/picking');
    }


    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->load($this->getEntityId());
        }

        return $this->_order;
    }

    public function start()
    {
        $this->setStarted(Mage::getModel('core/date')->date());
        $this->setStatus(Moogento_PickNScan_Model_Picking::STATUS_STARTED);
        $helper = Mage::helper('moogento_pickscan');
        $users = $helper->getUsers();
        $this->setResults(
            $helper->__('Assigned to ') . $users[$this->getUserId()] . '<br/>' . $helper->__('Start: ') . $this->getStarted()
        );
        $this->save();

        $changeStatus = Mage::getStoreConfig('moogento_pickscan/settings/status_on_start');
        if ($changeStatus) {
            Mage::helper('moogento_core')->changeOrderStatus($this->getOrder()->getId(), $changeStatus, '', Mage::getStoreConfigFlag('moogento_pickscan/settings/notify_status'));
        }

        return $this;
    }

    public function abort()
    {
        $this->setStarted(null);
        $this->setFinished(null);
        $this->setItemsCount(0);
        $this->setSubstitutedCount(0);
        $this->setIgnoredCount(0);
        $this->setStatus(Moogento_PickNScan_Model_Picking::STATUS_ASSIGNED);
        $helper = Mage::helper('moogento_pickscan');
        $users = $helper->getUsers();
        $this->setResults(
            $helper->__('Assigned to ') . $users[$this->getUserId()]
        );
        $this->save();

        return $this;
    }

    public function finish($results)
    {
        unset($results['id']);
        $this->addData($results);
        $this->setFinished(Mage::getModel('core/date')->date());
        if ($this->getSubstitutedCount() > 0 || $this->getIgnoredCount()) {
            $this->setStatus(Moogento_PickNScan_Model_Picking::STATUS_COMPLETE_ANOMALIES);
        } else {
            $this->setStatus(Moogento_PickNScan_Model_Picking::STATUS_COMPLETE);
        }
        $helper = Mage::helper('moogento_pickscan');
        $users = $helper->getUsers();
        $this->setResults(
            $helper->__('Assigned to ') . $users[$this->getUserId()] . '<br/>' . $helper->__('Start: ') . $this->getStarted() . '<br/>' . $helper->__('Finish: ') . $this->getFinished() . '<br/>' . $this->getResults()
        );

        $errors = array();
        if (isset($results['barcode_updates']) && $results['barcode_updates']) {
            $barcodeCode = Mage::getStoreConfig('moogento_pickscan/settings/barcode');
            foreach ($results['barcode_updates'] as $newBarcodeData) {
                $product = Mage::getModel('catalog/product')->load($newBarcodeData['id']);
                if ($product->getId()) {
                    $oldBarcode = $product->getData($barcodeCode);
                    $product->setData($barcodeCode, $newBarcodeData['barcode']);
                    $error = false;
                    try {
                        $product->validate();
                        $product->save();
                    } catch (Exception $e) {
                        $error = $helper->__('Failed to updated product barcode %s with new value %s: %s', $oldBarcode, $newBarcodeData['barcode'], $e->getMessage());
                    }
                    if ($error) {
                        $errors[] = $error;
                        $this->setResults($this->getResults() . '<br />' . $error);
                    } else {
                        $this->setResults($this->getResults() . '<br />' . $helper->__('Product barcode %s updated with new value %s', $oldBarcode, $newBarcodeData['barcode'] . ($newBarcodeData['auth'] ? ' (Auth by ' . $newBarcodeData['auth'] . ')' : '')));
                    }
                }
            }
        }
        $this->save();
        Mage::dispatchEvent('moogento_pickscan_finish_picking', array('order_id' => $this->getEntityId()));

        if ($results['newStatus']) {
            $changeStatus = $results['newStatus'];
        } else {
            $changeStatus = Mage::getStoreConfig('moogento_pickscan/settings/status_on_finish');
        }

        $order = $this->getOrder();

        if (isset($results['tracking']) && $results['tracking']) {
            if (count($order->getShipmentsCollection())) {
                foreach ($order->getShipmentsCollection() as $shipment) {
                    Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $results['tracking'], isset($results['carrier']) ? $results['carrier'] : false);
                    $shipment->save();
                }
            } else {
                if (Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')) {
                    $order->setData('preshipment_tracking', $results['tracking'] . (isset($results['carrier']) && $results['carrier'] ? '||' . $results['carrier'] : ''));
                } else if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
                    $order->setCourierrulesTracking($results['tracking'] . (isset($results['carrier']) && $results['carrier'] ? '||' . $results['carrier'] : ''));
                }
            }
        }

        if (count($results['comments'])) {
            $comment = '';
            foreach ($results['comments'] as $commentData) {
                $comment .= $commentData['sku'] . ': ' . $commentData['message'] . '<br/>';
            }
            $order->addStatusHistoryComment($comment, false);
        }
        $order->addStatusHistoryComment("Pick'N'Scan: <br/>" . $this->getResults(), false);

        if (Mage::helper('core')->isModuleEnabled('Moogento_ShipEasy')) {
            if ($results['newFlag']) {
                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                try {
                    $text = '';
                    if ($results['newFlag']['value'] == 'custom') {
                        $text = $results['newFlag']['value_custom'];
                    } else {
                        list($text, $preset) = explode('|', $results['newFlag']['value']);
                    }
                    $attributeCode = 'szy_custom_attribute' . ($results['newFlag']['number'] > 1 ? $results['newFlag']['number'] : '');
                    $resource->updateGridRow($order->getEntityId(), $attributeCode, $text);
                } catch(Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        $this->getOrder()->save();

        if ($changeStatus) {
            Mage::helper('moogento_core')->changeOrderStatus($order->getId(), $changeStatus, '', Mage::getStoreConfigFlag('moogento_pickscan/settings/notify_status'));
        }

        if (count($errors)) {
            return array('errors' => $errors);
        }
        return array('success' => true);
    }

    public function getStarted()
    {
        $started = $this->getData('started');

        return $started ? date('Y-m-d H:i', strtotime($started)) : '';
    }

    public function getFinished()
    {
        $finished = $this->getData('finished');

        return $finished ? date('Y-m-d H:i', strtotime($finished)) : '';
    }
} 