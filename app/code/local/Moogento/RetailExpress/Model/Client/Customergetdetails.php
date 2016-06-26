<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:18
 */

class Moogento_RetailExpress_Model_Client_Customergetdetails
{
    public $CustomerId;

    public function __construct()
    {
        $this->CustomerId;
    }

    public function setCustomerId($customerId)
    {
        $this->CustomerId = $customerId;
    }
}