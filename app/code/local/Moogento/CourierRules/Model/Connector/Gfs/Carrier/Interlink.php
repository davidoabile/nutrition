<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Interlink extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Interlink';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'INTERLINK';
    }

    public function getCode()
    {
        return 'INTERLINK';
    }

    protected $_limits = array(
        'name' => 35,
        'company' => 35,
        'street' => 35,
        'district' => 35,
        'town' => 35,
        'county' => 35,
        'reference' => 25,
        'instructions' => 50,
        'countent' => 50,
    );

    protected $_servicesConfig = array(
        '12' => 'Next Day Delivery',
        '10' => 'Parcel By Air',
        '19' => 'European Road',
        '70' => 'European By Air',
        '30' => 'Document By Air',
        '14' => 'Next Day by 10am Delivery',
        '11' => '2 Day Delivery',
        '20' => 'Next Day by 3 p.m.',
        '13' => 'Next Day by Noon Delivery',
        '08' => 'Next Day by 10am Delivery',
        '17' => 'Saturday by Noon Delivery',
        '09' => 'Saturday by 9.30am Delivery',
        '22' => 'OFF SHORE',
        '40' => 'ext Day Delivery < 5KG Bag',
        '33' => 'Next Day by Noon < 5KG Bag',
        '28' => 'Next Day by 9.30am < 5KG Bag',
        '37' => 'SAT by Noon < 5KG Bag',
        '29' => 'SAT by 9.30am < 5KG Bag',
        '02' => 'Next Day Delivery < 1KG Bag',
        '03' => 'Next Day by Noon < 1KG Bag',
        '04' => 'Next Day by 9.30am < 1KG Bag',
        '05' => 'SAT by Noon < 1KG Bag',
        '06' => 'SAT by 9.30am < 1KG Bag',
        '07' => 'Next Day < 5KG Bag',
        '90' => 'Next Day Freight Delivery',
        '83' => 'Next Day by Noon Freight Delivery',
        '65' => 'Next Day by 9.30am Freight Delivery',
        '87' => 'SAT by Noon Freight Delivery',
        '69' => 'SAT by 9.30am Freight Delivery',
        '31' => 'Home Del. < 5KG Bag',
        '57' => 'Home Del. Evening',
        '01' => 'Home Del.< 1KG Bag',
        '67' => 'Home Freight Delivery',
        '16' => 'Saturday Delivery',
        '18' => 'Saturday by 9.30am Delivery',
        '21' => 'Home Del. Evening',
        '32' => 'Next Day Delivery < 5KG Bag',
        '34' => 'Next Day by 9.30am < 5KG Bag',
        '36' => 'SAT by Noon < 5KG Bag',
        '38' => 'SAT by 9.30am < 5KG Bag',
        '42' => 'Next Day Delivery < 1KG Bag',
        '43' => 'Next Day by Noon < 1KG Bag',
        '44' => 'Next Day by 9.30am < 1KG Bag',
        '46' => 'SAT by Noon < 1KG Bag',
        '48' => 'SAT by 9.30am < 1KG Bag',
        '62' => 'Next Day < 5KG Bag',
        '82' => 'Next Day Freight Delivery',
        '84' => 'Next Day by 9.30am Freight Delivery',
        '86' => 'SAT by Noon Freight Delivery',
        '88' => 'SAT by 9.30am Freight Delivery',
        '54' => 'REVERSE-IT',
        '56' => 'REVERSE_IT',
    );
}