<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Hermes extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Hermes';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'HERMES';
    }

    public function getCode()
    {
        return 'HERMES';
    }

    protected $_limits = array(
        'name' => 32,
        'company' => 32,
        'street' => 32,
        'district' => 30,
        'town' => 32,
        'county' => 30,
        'reference' => 20,
        'instructions' => 32,
        'countent' => 32,
    );

    protected $_servicesConfig = array(
        'NDAY' => 'NEXT DAY',
        '2DAY' => '2 DAY SERVICE',
        '3DAY' => '3 DAY SERVICE',
    );
}