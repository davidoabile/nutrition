<?php


class Moogento_CourierRules_Model_Connector_Manager extends Mage_Core_Model_Abstract
{
    public function getConnectorCollection()
    {
        $connectorObjects = array();
        $connectors = Mage::getConfig()->getNode('global/courier_connectors')->asArray();
        foreach($connectors as $key => $connector) {
            $connectorObject = Mage::getModel($connector['model']);
            $connectorObject->setConfig($connector);
            $connectorObjects[$key] = $connectorObject;
        }

        return $connectorObjects;
    }

    public function getConnector($code)
    {
        $connectors = $this->getConnectorCollection();
        if(isset($connectors[$code])) {
            $connector = $connectors[$code];
            return $connector;
        }
        return false;
    }
} 