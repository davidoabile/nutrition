<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Commitshipments
{
    public $CarrierShipments;

    public function __construct()
    {
        $this->CarrierShipments = Mage::getModel('moogento_courierrules/connector_gfs_client_request_commitshipmentsrequest');
    }

    /**
     * @param $carrier
     * @param $serviceType "DOMESTIC","DOMESTIC_FREIGHT","EUROROAD","INTERNATIONAL","ANY","DOMESTIC_DETAIL"
     */
    public function addCarrierServiceGroup($carrier, $serviceType = "ANY")
    {
        $possibleValues = array("DOMESTIC","DOMESTIC_FREIGHT","EUROROAD","INTERNATIONAL","ANY","DOMESTIC_DETAIL");
        if(array_search($serviceType, $possibleValues) === false) {
            $serviceType = "ANY";
        }
        $this->CarrierShipments->addCarrierServiceGroup($carrier, $serviceType);
    }
}