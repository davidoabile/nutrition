<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Shipreplies
{
    public function getShipments()
    {
        if ($this->Shipments && $this->Shipments->ProcessedShipment) {
            if (is_array($this->Shipments->ProcessedShipment)) {
                return $this->Shipments->ProcessedShipment;
            } else {
                return array($this->Shipments->ProcessedShipment);
            }
        }
        return array();
    }
} 