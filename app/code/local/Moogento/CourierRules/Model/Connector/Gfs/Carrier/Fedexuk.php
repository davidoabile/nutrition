<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Fedexuk extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'FedEx UK';

    protected $_package_required = true;

    public function getConnectorCode()
    {
        return 'FEDEXUK';
    }

    public function getCode()
    {
        return 'FEDEXUK';
    }

    protected $_limits = array(
        'name' => 25,
        'company' => 25,
        'street' => 25,
        'district' => 25,
        'town' => 25,
        'county' => 25,
        'reference' => 22,
        'instructions' => 96,
        'countent' => 50,
    );

    protected $_servicesConfig = array(
        'A' => 'Next day delivery',
        'B' => '2 day delivery',
        'NAM' => '9am weekday delivery',
        'TAM' => '10am weekday delivery',
        'TN' => '12 noon weekday delivery',
        'SAT' => 'Saturday delivery',
        'SNA' => '9am Saturday delivery',
        'STA' => '10am Saturday delivery',
        'STN' => '12noon Saturday delivery',
    );

    protected $_packagesConfig = array(
        'D' => 'Easipak Medium',
        'K' => 'Kilo',
        'L' => 'Pallet',
        'M' => 'Easipak Small',
        'P' => 'Parcel',
        'X' => 'Eaispak Large',
    );
}