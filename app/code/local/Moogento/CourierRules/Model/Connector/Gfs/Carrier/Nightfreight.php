<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Nightfreight extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Nightfreight Domestic';

    protected $_package_required = true;

    public function getConnectorCode()
    {
        return 'NIGHTFREIGHT';
    }

    public function getCode()
    {
        return 'NIGHTFREIGHT';
    }

    protected $_limits = array(
        'name' => 20,
        'company' => 20,
        'street' => 30,
        'district' => 30,
        'town' => 30,
        'county' => 30,
        'reference' => 10,
        'instructions' => 75,
        'countent' => 0,
    );

    protected $_servicesConfig = array(
        'ON' => 'OVERNIGHT DELIVERY',
        '3D' => 'THREE DAY DELIVERY',
        '930' => 'OVERNIGHT BEFORE 9:30 DELIVERY',
        'AM' => 'OVERNIGHT AM DELIVERY',
        'S93' => 'SATURDAY 9:30 DELIVERY',
        'SAT' => 'SATURDAY DELIVERY',
    );

    protected $_packagesConfig = array(
        'PA' => 'CRATE',
        'PA2' => 'PALLET',
        'KG' => 'CARTON',
        'KG2' => 'JIFFY BAG',
        'ID' => 'IDW',
        'L3' => 'Length Over 3 Meters',
    );
}