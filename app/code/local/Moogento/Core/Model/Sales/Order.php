<?php


class Moogento_Core_Model_Sales_Order extends Mage_Sales_Model_Order
{
    protected $_mdnOrder = null;

    protected function _construct()
    {
        parent::_construct();
        if (Mage::helper('moogento_core')->isInstalled('MDN_AdvancedStock') && mageFindClassFile('MDN_AdvancedStock_Model_Sales_Order')) {
            $this->_mdnOrder = Mage::getModel('MDN_AdvancedStock_Model_Sales_Order');
        }
    }

    protected function _checkState()
    {
        if (Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM && Mage::registry('ignore_status_check')) {
            return $this;
        }

        return parent::_checkState();
    }


    public function isStateProtected($state)
    {
        if (Mage::getStoreConfig('moogento_statuses/settings/status_processing') == Moogento_Core_Model_System_Config_Source_Status_Processing::CUSTOM && Mage::registry('ignore_status_check')) {
            return false;
        }

        return parent::isStateProtected($state);
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!is_null($this->_mdnOrder)) {
            $this->_mdnOrder->setData($this->getData());
        }
    }

    public function __call($method, $args)
    {
        if (!is_null($this->_mdnOrder) && method_exists($this->_mdnOrder, $method)) {
            return call_user_func_array(array($this->_mdnOrder, $method), $args);
        }

        return parent::__call($method, $args);
    }

    public function getShippingMethod($asObject = false)
    {
        $shippingMethod = parent::getShippingMethod();
        if ($asObject) {
            list($carrierCode, $method) = explode('_', $shippingMethod, 2);
            $shippingMethod = new Varien_Object(array(
                'carrier_code' => $carrierCode,
                'method'       => $method
            ));
        }
        $data = new Varien_Object(array(
            'method' =>$shippingMethod,
            'as_object' => $asObject,
        ));

        Mage::dispatchEvent('moogento_core_order_get_shipping_method',
            array('order' => $this, 'values' => $data));
        return $data->getMethod();
    }

    public function getShippingDescription()
    {
        $shippingDescription = parent::getShippingDescription();
        $data = new Varien_Object(array(
            'description' =>$shippingDescription,
        ));

        Mage::dispatchEvent('moogento_core_order_get_shipping_description',
            array('order' => $this, 'values' => $data));
        return $data->getDescription();
    }
}
