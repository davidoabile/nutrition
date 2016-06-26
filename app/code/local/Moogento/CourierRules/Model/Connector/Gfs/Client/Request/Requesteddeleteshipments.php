<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 13.02.15
 * Time: 14:39
 */
class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requesteddeleteshipments extends Moogento_CourierRules_Model_Connector_Gfs_Client_Request
{
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Deleteshipment */
    public $RequestedShipments = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function addShipment($carrier, $consignmentNo)
    {
        $item = Mage::getModel('moogento_courierrules/connector_gfs_client_request_deleteshipment');
        $item->setCarrier($carrier);
        $item->setConsignmentNo($consignmentNo);
        $this->RequestedShipments[] = $item;
    }
}