<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 13.02.15
 * Time: 13:26
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Validateaddressrequest extends Moogento_CourierRules_Model_Connector_Gfs_Client_Request
{
    public $RequestedParty;

    public function __construct()
    {
        parent::__construct();

        $this->RequestedParty = Mage::getModel('moogento_courierrules/connector_gfs_client_request_party');
    }

    public function setAddress($address)
    {
        return $this->RequestedParty->setAddress($address);
    }
}