<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Response_Processedshipment
{
    public $ShipmentStatus;
    public $ShipmentID;
    public $ConsignmentNo;
    public $Packages;

    public function getStatus()
    {
        return $this->ShipmentStatus->Status;
    }

    public function getStatusDescription()
    {
        return $this->ShipmentStatus->StatusDescription;
    }

    public function getMagentoShipmentId()
    {
        return $this->ShipmentID;
    }

    public function getConsignmentNo()
    {
        return $this->ConsignmentNo;
    }

    public function getLabelImage()
    {
        $labels = array();
        if ($this->Packages && isset($this->Packages->Labels)) {
            if (is_array($this->Packages->Labels)) {
                $key = 'label';
                $i = 1;
                foreach ($this->Packages->Labels as $label) {
                    if (isset($label->Image)) {
                        $labels[$key . ($i > 1 ? '_' . $i : '')] = $label->Image;
                        $i++;
                    }
                }

            } else {
                if (isset($this->Packages->Labels->Image)) {
                    $labels['label'] = $this->Packages->Labels->Image;
                }
            }
        }
        return $labels;
    }

    public function getTrackingNumber()
    {
        return $this->Packages && isset($this->Packages->PackageNo) ? $this->Packages->PackageNo : null;
    }
} 