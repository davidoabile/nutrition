<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Validateaddress
{
    public $RequestedAddress;

    public function __construct()
    {
        $this->RequestedAddress = Mage::getModel('moogento_courierrules/connector_gfs_client_request_validateaddressrequest');
    }

    public function setAddress($address)
    {
        $this->RequestedAddress->setAddress($address);
    }

}