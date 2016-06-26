<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Royalmail extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Royal Mail';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'ROYAL MAIL';
    }

    public function getCode()
    {
        return 'ROYALMAIL';
    }

    protected $_limits = array(
        'name' => 40,
        'company' => 40,
        'street' => 40,
        'district' => 35,
        'town' => 30,
        'county' => 35,
        'reference' => 20,
        'instructions' => 30,
        'countent' => 0,
    );

    protected $_servicesConfig = array(
        'RMNS' => 'TRACKED NEXT DAY (SIGNATURE)',
        'RMNN' => 'TRACKED NEXT DAY (NON SIGNATURE)',
        'RM2L' => 'TRACKED STANDARD (NON SIGNATURE)',
        'RM2H' => 'TRACKED (NON SIGNATURE)',
        'RMP1' => 'Packet Post Daily First',
        'RMP2' => 'Packet Post Daily Second',
        'RMLS' => 'TRACKED STANDARD (SIGNATURE)',
        'RMHS' => 'TRACKED (SIGNATURE)',
        'RMF1' => 'Packet Post Flat First',
    );
}