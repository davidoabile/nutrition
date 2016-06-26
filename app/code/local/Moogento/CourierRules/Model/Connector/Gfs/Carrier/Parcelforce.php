<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Parcelforce extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Parcelforce';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'PARCELFORCE';
    }

    public function getCode()
    {
        return 'PARCELFORCE';
    }

    protected $_limits = array(
        'name' => 25,
        'company' => 25,
        'street' => 24,
        'district' => 24,
        'town' => 24,
        'county' => 24,
        'reference' => 35,
        'instructions' => 100,
        'countent' => 0,
    );

    protected $_servicesConfig = array(
        'BFPO' => 'BPFO',
        '24' => 'Next Day',
        '48' => '2 Day',
        '09' => 'Next Day before 9',
        '10' => 'Next Day before 10',
        '12' => 'Next Day before 12',
        '48L' => '2 Day Large',
        'PM' => 'Afternoon large',
        'S24' => 'Saturday',
        'S09' => 'Saturday before 9',
        'S48' => 'Saturday 2 Day',
        'S10' => 'Saturday before 10',
        'S12' => 'Saturday before 12',
        'S48L' => 'Saturday 2 Day Large',
        'E48' => 'Euro 48 EPG',
        'EPH' => 'EuroPriority Home',
        'EPB' => 'EuroPriority Business',
    );
}