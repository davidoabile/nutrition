<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 13.02.15
 * Time: 13:26
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Carrierservicegroup
{
    /** @var  string */
    public $Carrier;

    /** @var  string "DOMESTIC","DOMESTIC_FREIGHT","EUROROAD","INTERNATIONAL","ANY,"DOMESTIC_DETAIL" */
    public $ServiceType;

    public function setCarrier($carrier)
    {
        $this->Carrier = $carrier;
    }

    public function setServiceType($serviceType)
    {
        $this->ServiceType = $serviceType;
    }
}