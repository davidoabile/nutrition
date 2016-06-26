<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:12
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requestedlocation
{
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Findavailableservicesrequest */
    public $FindServicesRequest;

    public function __construct()
    {
        $this->FindServicesRequest = Mage::getModel('moogento_courierrules/connector_gfs_client_request_findavailableservicesrequest');
    }
}