<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:40
 */
class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Findavailableservicesrequest extends Moogento_CourierRules_Model_Connector_Gfs_Client_Request
{

    /** @var string */
    public $Postcode;
    /** @var string */
    public $CountryCode;
    /** @var string */
    public $Carrier;
    /** @var string */
    public $DepartmentCode;

    public function setCountry($country)
    {
        $this->CountryCode = $country;
    }

    public function setCarrier($carrier)
    {
        $this->Carrier = $carrier;
    }

    public function setPostcode($postcode)
    {
        $this->Postcode = $postcode;
    }

    public function setDepartmentCode($departmentCode)
    {
        $this->DepartmentCode = $departmentCode;
    }

}
