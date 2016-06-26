<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Carrier_Service extends Mage_Core_Model_Abstract
{
    public function getEnabled()
    {
        return $this->getData('enabled') ? true : false;
    }

    public function getAutoCreateShipment()
    {
        return $this->getData('auto_create_shipment') ? true : false;
    }

    public function getSaveNotValid()
    {
        return $this->getData('save_not_valid') ? true : false;
    }

    public function getDespatchDate()
    {
        $offset = $this->getData('dispatch_date');

        return strtotime('+' . $offset . 'days');
    }

    public function getLabel()
    {
        return Mage::helper('moogento_courierrules')->__($this->getData('label'));
    }

    public function loadConfig()
    {
        $serviceCode = $this->getCode();
        $carrierCode = $this->getCarrier()->getCode();
        $connectorCode = $this->getCarrier()->getConnector()->getCode();
        $data = Mage::helper('core')->jsonDecode(Mage::getStoreConfig('moogento_connectors/'.$connectorCode.'/carriers_' . $carrierCode . '_services_used'));
        if (is_array($data)) {
            foreach ($data as $row) {
                if ($row['code'] == $serviceCode) {
                    unset($row['code']);
                    foreach ($row as $key => $val) {
                        $this->setData($key, $val);
                    }
                }
            }
        }
        return false;
    }
}