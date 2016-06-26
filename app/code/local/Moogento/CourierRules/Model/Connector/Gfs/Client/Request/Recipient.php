<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Recipient
{
    /** @var  int */
    public $SequenceId = 1;
    /** @var  string[] */
    public $AdditionalRefrences = array();
    /** @var  string */
    public $ShipmentReference;
    /** @var  string */
    public $ConsigneeReference;
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Party */
    public $AddressAndContact;

    public $ConsigneeNotifications = array(
        'NotificationTypes' => array(),// SMS FAX E_MAIL
        'NotificationLevel' => 'NONE', //PRE_DELIVERY ALL_TRACKING_ADVICE
    );

    public function __construct()
    {
        $this->AddressAndContact = Mage::getModel('moogento_courierrules/connector_gfs_client_request_party');
    }

    public function setSequenceId($id)
    {
        $this->SequenceId = $id;
    }

    public function setAddress($address)
    {
        $this->AddressAndContact->setAddress($address);
    }

    public function setShipmentReference($shipmentReference)
    {
        $this->ShipmentReference = $shipmentReference;
    }
} 