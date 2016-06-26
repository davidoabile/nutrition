<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 21:13
 */
class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requestedshipmentdetails
{

    /** @var  boolean */
    public $SaveNotValid;

    /** @var  string */
    public $DespatchDate; //TODO add to config

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservice */
    public $CarrierService;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Party */
    public $Shipper;

    public $SaturdayDeliv = false;

    public $ConsolidateShipment = false;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Money */
    public $Cost; //Cost of shipment, required for non-EU destinations

    /** @var  string */
    public $Instructions = '';

    public $Content = '';

    public $TotalWeight = 0;

    public $Packs = 1;

    public $ShipmentID;

    public function __construct()
    {
        $despatchDate = new DateTime();
        $this->DespatchDate = $despatchDate->format('Y-m-d');

        $this->CarrierService = Mage::getModel('moogento_courierrules/connector_gfs_client_request_carrierservice');

        $this->Cost = Mage::getModel('moogento_courierrules/connector_gfs_client_request_money');

        $this->Shipper = Mage::getModel('moogento_courierrules/connector_gfs_client_request_party');
    }

    public function setDespatchDate($date)
    {
        $this->DespatchDate($date);
    }
}