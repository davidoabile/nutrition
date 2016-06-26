<?php

class Moogento_CourierRules_Adminhtml_Sales_ProcessingController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function deleteLabelAction()
    {
        $id = $this->getRequest()->getPost('id');
        $connectorData = Mage::getModel('moogento_courierrules/connector')->load($id);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        try {
            $connectorData->deleteShipment();

            $block = $this->getLayout()->createBlock('moogento_courierrules/adminhtml_sales_order_connector_tab');
            $block->setOrder(Mage::getModel('sales/order_shipment')->load($id)->getOrder());

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => true, 'html' => $block->toHtml())));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => false, 'message' => $e->getMessage())));
        }
    }

    public function processCourierRulesAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                Mage::helper('moogento_courierrules')->processOrder($order);
                $this->_getSession()->addSuccess(
                    $this->__('The order courier rules was processed.')
                );
            }
            catch (Moogento_CourierRules_Model_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order courier rules could not be processed.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    public function massProcessCourierRulesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $processed = 0;
        $notProcessed = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            try {
                Mage::helper('moogento_courierrules')->processOrder($order);
                $processed++;
            } catch (Moogento_CourierRules_Model_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $notProcessed++;
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('Courier rules for order #%s could not be processed.', $order->getIncrementId()));
                $notProcessed++;
            }
        }
        if ($notProcessed) {
            $this->_getSession()->addError($this->__('%s order(s) have not been processed', $notProcessed));
        }
        if ($processed) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been processed.', $processed));
        }
        $this->_redirect('*/sales_order/');
    }

    public function massPrintConnectorLabelsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());

        require_once 'mpdf/mpdf.php';

        $mpdf=new mPDF('', 'A4', 12, 'dejavusans', 5, 5, 5, 5);

        $pages = 0;

        foreach ($orderIds as $orderId) {

            $collection = Mage::getModel('moogento_courierrules/connector')->getCollection();
            $collection->getSelect()->join(
                array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
                'main_table.shipment_id = shipment.entity_id',
                array('shipment.increment_id')
            );
            $collection->getSelect()->where('shipment.order_id = ?', $orderId);

            foreach ($collection as $connector) {
                $labelUris = $connector->getLabelDataUri();
                if (count($labelUris)) {
                    foreach ($labelUris as $uri) {
                        $mpdf->AddPage('P');

                        $mpdf->WriteHTML('<img src="' . $uri . '" />');

                        $pages++;
                    }
                }
            }
        }

        if (!$pages) {
            $this->_getSession()->addError(Mage::helper('moogento_courierrules')->__('There are no labels in selected orders'));
            $this->_redirectReferer();
            return;
        }

        return $this->_prepareDownloadResponse('raw_connector_labels.pdf', $mpdf->Output('', 'S'), 'application/pdf');
    }
} 