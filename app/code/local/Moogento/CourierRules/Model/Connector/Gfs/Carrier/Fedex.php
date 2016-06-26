<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Fedex extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'FedEx International Air';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'FEDEX';
    }

    public function getCode()
    {
        return 'FEDEX';
    }

    protected $_limits = array(
        'name' => 35,
        'company' => 35,
        'street' => 35,
        'district' => 35,
        'town' => 35,
        'county' => 14,
        'reference' => 35,
        'instructions' => 450,
        'countent' => 450,
    );

    protected $_servicesConfig = array(
        'EUR' => 'EUROPE FIRST INTERNATIONAL PRIORITY',
        'INTE' => 'INTERNATIONAL ECONOMY',
        'FRE' => 'INTERNATIONAL ECONOMY FREIGHT',
        'INT1' => 'INTERNATIONAL FIRST',
        'INT' => 'INTERNATIONAL PRIORITY',
        'FRP' => 'INTERNATIONAL PRIORITY FREIGHT',
    );
}