<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:40
 */
class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Shiprequest extends Moogento_CourierRules_Model_Connector_Gfs_Client_Request
{
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Printspecificatio */
    public $PrintSpec;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Shipment */
    public $Shipment;

    public function __construct()
    {
        parent::__construct();

        $this->Shipment = Mage::getModel('moogento_courierrules/connector_gfs_client_request_requestedshipment');

        $this->PrintSpec = Mage::getModel('moogento_courierrules/connector_gfs_client_request_printspecification');
    }

    public function setShipment($shipment)
    {
        $this->Shipment->setShipment($shipment);
    }
}