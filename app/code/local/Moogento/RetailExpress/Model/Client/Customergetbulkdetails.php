<?php


class Moogento_RetailExpress_Model_Client_Customergetbulkdetails
{
    public $OnlyCustomersWithEmails = 1;
    public $LastUpdated;

    public function setLastUpdated($datetime)
    {
        $this->LastUpdated = date_format(new DateTime($datetime), 'Y-m-d\TH:i:s.000\Z');
    }
} 