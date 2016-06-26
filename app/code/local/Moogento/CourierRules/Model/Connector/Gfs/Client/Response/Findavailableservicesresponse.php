<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Findavailableservicesresponse extends Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Response
{
    public $FoundAvailableServices;

    public function getAvailableServices()
    {

        if(is_array($this->FoundAvailableServices)) {
            return $this->FoundAvailableServices;
        }
        else {
            $return = array();
            $return[] = $this->FoundAvailableServices;
            return $return;
        }
    }
}