<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Dhlexpress extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'DHL_EXPRESS';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'DHL EXPRESS';
    }

    public function getCode()
    {
        return 'DHLEXPRESS';
    }

    protected $_limits = array(
        'name' => 22,
        'company' => 35,
        'street' => 35,
        'district' => 35,
        'town' => 35,
        'county' => 22,
        'reference' => 20,
        'instructions' => 0,
        'countent' => 82,
    );

    protected $_servicesConfig = array(
        'DOM' => 'DOMESTIC EXPRESS',
        'ESI' => 'ECONOMY SELECT (DUTIABLE)',
        'ESU' => 'ECONOMY SELECT',
        'TDM' => 'EXPRESS 10:30 (DUTIABLE)',
        'TDL' => 'EXPRESS 10:30',
        'TDY' => 'EXPRESS 12:00 (DUTIABLE)',
        'TDT' => 'EXPRESS 12:00',
        'TDK' => 'EXPRESS 9:00',
        'TDE' => 'EXPRESS 9:00 (DUTIABLE)',
        'ECX' => 'EXPRESS WORLDWIDE (EU)',
        'WPX' => 'EXPRESS WORLDWIDE (DUTIABLE)',
        'DOX' => 'EXPRESS WORLDWIDE (NON EU)',
        'EDOM' => 'EXCHANGE NEXT DAY',
        'ETDT' => 'EXCHANGE NOON',
        'ETDK' => 'EXCHANGE 9:00',
    );
}