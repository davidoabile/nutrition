<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Response
{
    public $Status;

    public function getStatus()
    {
        return $this->Status->Status;
    }

    public function getStatusDescription()
    {
        return $this->Status->ResponseDetails->StatusDescription;
    }
}