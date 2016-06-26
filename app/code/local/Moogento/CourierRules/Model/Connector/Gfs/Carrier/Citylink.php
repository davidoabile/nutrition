<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Citylink extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'City Link';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'CITY LINK';
    }

    public function getCode()
    {
        return 'CITYLINK';
    }

    protected $_limits = array(
        'name' => 30,
        'company' => 30,
        'street' => 30,
        'district' => 30,
        'town' => 30,
        'county' => 30,
        'reference' => 30,
        'instructions' => 30,
        'countent' => 30,
    );

    protected $_servicesConfig = array(
        '34' => 'LINK LETTER 0730',
        '35' => 'LINK LETTER 0900',
        '36' => 'LINK LETTER 1030',
        '37' => 'LINK LETTER 1200',
        '38' => 'LINK LETTER 1730',
        '39' => 'CITY PACK 0730',
        '40' => 'CITY PACK 0900',
        '41' => 'CITY PACK 1030',
        '42' => 'CITY PACK 1200',
        '43' => 'CITY PACK 1730',
        '44' => 'CITY BAG 0730',
        '45' => 'CITY BAG 0900',
        '46' => 'CITY BAG 1030',
        '47' => 'CITY BAG 1200',
        '48' => 'CITY BAG 1730',
        '49' => 'NEXT DAY BY 0730',
        '50' => 'NEXT DAY BY 0900',
        '51' => 'NEXT DAY BY 1030',
        '52' => 'NEXT DAY BY 1200',
        '53' => 'NEXT DAY PARCEL',
        '54' => 'PALLET 0900',
        '55' => 'PALLET 1030',
        '56' => 'PALLET 1200',
        '57' => 'PALLET 1730',
        '58' => 'DOMESTICPOST',
        '59' => 'MARKET LINK',
        '60' => 'NOMINATED',
        '61' => 'SIGNATURE',
        '62' => 'SAME DAY',
        '63' => 'SATURDAY DEL',
        '64' => 'X-CHANGE RETURN',
        '65' => 'PAY ON DELIVERY',
        '66' => 'AIR EXPRESS',
        '67' => 'INTER VALUE',
        '68' => 'EUROPEANROAD',
        '69' => 'INTERFREIGHT',
        '70' => 'INTER MAIL',
        '100' => 'EUROPEAN BAG',
    );
}