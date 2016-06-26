<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Tuffnells extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Tuffnells';

    protected $_package_required = true;

    public function getConnectorCode()
    {
        return 'TUFFNELLS';
    }

    public function getCode()
    {
        return 'TUFFNELLS';
    }

    protected $_limits = array(
        'name' => 30,
        'company' => 30,
        'street' => 30,
        'district' => 30,
        'town' => 30,
        'county' => 30,
        'reference' => 20,
        'instructions' => 60,
        'countent' => 0,
    );

    protected $_servicesConfig = array(
        'RMF2' => 'Packet Post Flat Second',
        'P1' => 'Next Day',
        'P1BN' => 'Next Day before noon',
        'PT30' => 'Next Day before 10:30',
        'P1SM' => 'Saturday AM',
        'P3' => '3 day service',
        'OFP1' => 'Next day offshore',
        'P1SD' => 'Saturday delivery',
        'OF' => '3 day offshore',
        'DB' => 'Next day databag',
        'DBBN' => 'Next day databag before noon',
        'DT30' => 'Next day databag before 10:30',
        'DBSM' => 'Saturday AM databag',
        'DBSD' => 'Saturday databag',
    );

    protected $_packagesConfig = array(
    );
}