<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:20
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Authenticationdetails
{
    public $VersionId = array(
        'Major' => 5,
        'Minor' => 0,
        'Intermediate' => 1,
    );

    public $UserID;
    public $UserPassword;

    public function __construct()
    {
        $this->UserID = Mage::getStoreConfig('moogento_connectors/gfs/user_id');
        $this->UserPassword = Mage::getStoreConfig('moogento_connectors/gfs/user_password');
    }
}