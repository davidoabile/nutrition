<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Nightline extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Nightline';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'NIGHTLINE';
    }

    public function getCode()
    {
        return 'NIGHTLINE';
    }

    protected $_limits = array(
        'name' => 50,
        'company' => 50,
        'street' => 40,
        'district' => 35,
        'town' => 40,
        'county' => 35,
        'reference' => 32,
        'instructions' => 80,
        'countent' => 0,
    );

    protected $_servicesConfig = array(
        'NXT' => 'Next Day Parcel',
        'NXTH' => 'Next Day Pallet',
        'SWAP' => 'SWAP Parcel',
        'SWAH' => 'SWAP Pallet',
    );
}