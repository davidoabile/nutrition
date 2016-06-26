<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Commitshipmentsrequest extends Moogento_CourierRules_Model_Connector_Gfs_Client_Request
{
    /** @var int */
    public $ManifestCopies;
    /** @var string */
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Printspecificatio */
    public $PrintSpecification;
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservicegroup */
    public $CarrierServiceGroups = array();

    public function __construct()
    {
        parent::__construct();

        $this->PrintSpecification = Mage::getModel('moogento_courierrules/connector_gfs_client_request_printspecification');
    }

    public function setManifestCopies($copies)
    {
        $this->ManifestCopies = $copies;
    }

    public function addCarrierServiceGroup($carrier, $serviceType)
    {
        $item = Mage::getModel('moogento_courierrules/connector_gfs_client_request_carrierservicegroup');

        $item->setCarrier($carrier);
        $item->setServiceType($serviceType);

        $this->CarrierServiceGroups[] = $item;
    }
}