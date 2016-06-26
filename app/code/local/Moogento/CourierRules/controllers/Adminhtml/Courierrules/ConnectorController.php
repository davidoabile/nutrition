<?php


class Moogento_CourierRules_Adminhtml_Courierrules_ConnectorController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function suggestionsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function useSuggestionAction()
    {
        $orderId = $this->getRequest()->getPost('order_id');
        $code = $this->getRequest()->getPost('code');

        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->getId()) {
            try {
                $helper = Mage::helper('moogento_courierrules');
                $result = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($code);

                $helper->updateCourierRule($order, NULL, $code, $result['service']->getLabel());
                $suggestion = Mage::getModel('moogento_courierrules/connector_suggestion')->load($order->getId(), 'order_id');
                $suggestion->delete();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => true)));
            } catch (Exception $e) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => false, 'message' => $e->getMessage())));
            }
        } else {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => false, 'message' => 'Order not found')));
        }

    }
} 