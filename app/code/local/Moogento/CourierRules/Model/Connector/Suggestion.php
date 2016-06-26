<?php


class Moogento_CourierRules_Model_Connector_Suggestion extends Mage_Core_Model_Abstract
{
    protected $_order = null;
    protected $_suggestions = null;
    protected $_activeSuggestions = null;
    protected $_disabledSuggestions = null;

    protected function _construct()
    {
        $this->_init('moogento_courierrules/connector_suggestion');
    }

    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    public function getSuggestions()
    {
        if (is_null($this->_suggestions)) {
            $this->_suggestions = $this->getData('suggestion') ? Mage::helper('core')->jsonDecode($this->getData('suggestion')) : array();
        }

        return $this->_suggestions;
    }

    public function getActiveSuggestions()
    {
        if (is_null($this->_activeSuggestions)) {
            $this->_activeSuggestions = array();
            foreach ($this->getSuggestions() as $connectorCode => $carriers) {
                $connector = Mage::getSingleton('moogento_courierrules/connector_manager')
                                 ->getConnector($connectorCode);
                if ($connector) {
                    foreach ($carriers as $carrierCode => $serviceList) {
                        $carrier = $connector->getCarrier($carrierCode);
                        if ($carrier) {
                            foreach ($serviceList as $serviceCode) {
                                $service = $carrier->getService($serviceCode);
                                if ($service && $service->getEnabled()) {
                                    if (!isset($this->_activeSuggestions[ $connectorCode ])) {
                                        $this->_activeSuggestions[ $connectorCode ] = array();
                                    }
                                    if (!isset($this->_activeSuggestions[ $connectorCode ][ $carrierCode ])) {
                                        $this->_activeSuggestions[ $connectorCode ][ $carrierCode ] = array();
                                    }
                                    $this->_activeSuggestions[ $connectorCode ][ $carrierCode ][] = $serviceCode;
                                }
                            }
                        }
                    }
                }
            }
        }


        return $this->_activeSuggestions;
    }

    public function getDisabledSuggestion()
    {
        if (is_null($this->_disabledSuggestions)) {
            $this->_disabledSuggestions = array();
            foreach ($this->getSuggestions() as $connectorCode => $carriers) {
                $connector = Mage::getSingleton('moogento_courierrules/connector_manager')
                                 ->getConnector($connectorCode);
                if ($connector) {
                    foreach ($carriers as $carrierCode => $serviceList) {
                        $carrier = $connector->getCarrier($carrierCode);
                        if ($carrier) {
                            foreach ($serviceList as $serviceCode) {
                                $service = $carrier->getService($serviceCode);
                                if ($service && !$service->getEnabled()) {
                                    if (!isset($this->_disabledSuggestions[ $connectorCode ])) {
                                        $this->_disabledSuggestions[ $connectorCode ] = array();
                                    }
                                    if (!isset($this->_activeSuggestions[ $connectorCode ][ $carrierCode ])) {
                                        $this->_disabledSuggestions[ $connectorCode ][ $carrierCode ] = array();
                                    }
                                    $this->_disabledSuggestions[ $connectorCode ][ $carrierCode ][] = $serviceCode;
                                }
                            }
                        }
                    }
                }
            }
        }


        return $this->_disabledSuggestions;
    }
}