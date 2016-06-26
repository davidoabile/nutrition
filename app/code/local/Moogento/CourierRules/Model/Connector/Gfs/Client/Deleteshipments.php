<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Deleteshipments
{
    public $Shipments;


    public function __construct()
    {
        $this->Shipments = Mage::getModel('moogento_courierrules/connector_gfs_client_request_requesteddeleteshipments');
    }

    public function addShipment($shipment)
    {
        $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($shipment->getConnectorData());
        $carrier = $connectorInfo['carrier'];
        $this->Shipments->addShipment($carrier->getConnectorCode(), $shipment->getConsignment());
    }

}