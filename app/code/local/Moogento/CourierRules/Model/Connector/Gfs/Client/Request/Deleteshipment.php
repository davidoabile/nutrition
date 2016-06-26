<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 13.02.15
 * Time: 13:26
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Deleteshipment
{
    /** @var  string */
    public $ConsignmentNo;
    /** @var  string */
    public $Carrier;

    public function setCarrier($carrier)
    {
        $this->Carrier = $carrier;
    }

    public function setConsignmentNo($serviceType)
    {
        $this->ConsignmentNo = $serviceType;
    }
}