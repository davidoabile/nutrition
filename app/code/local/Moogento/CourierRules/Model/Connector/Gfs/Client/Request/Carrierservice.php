<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservice
{
    /** @var string */
    public $ContractNo;
    /** @var string */
    public $RouteMapCode;
    /** @var string */
    public $Carrier;
    /** @var string */
    public $ServiceCode;
    /** @var string */
    public $PackageCode;
    /** @var string "REGULAR_PICKUP","REQUEST_COURIER","DROP_BOX","BUSINESS_SERVICE_CENTER","STATION" */
    public $Dropoff = "DROP_BOX";

    public function setContractNo($contractNo)
    {
        $this->ContractNo = $contractNo;
    }

    public function setCarrier($carrier)
    {
        $this->Carrier = $carrier;
    }

    public function setService($serviceCode)
    {
        $this->ServiceCode = $serviceCode;
    }

    public function setPackageCode($packageCode)
    {
        $this->PackageCode = $packageCode;
    }
}