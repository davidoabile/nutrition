<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Dpd extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Geopost DPD';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'DPD';
    }

    public function getCode()
    {
        return 'DPD';
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
        '10' => 'DPD Express Parcel',
        '19' => 'European Road',
        '70' => 'DPD Express EU',
        '30' => 'DPD Express Document',
        '11' => '2 day delivery',
        '12' => 'Next Day delivery',
        '13' => 'Next Day by Noon',
        '14' => 'Next Day by 10am',
        '16' => 'Saturday delivery',
        '17' => 'Saturday by Noon',
        '18' => 'Saturday by 10am',
        '21' => 'Home Delivery Evening',
        '23' => 'Home Delivery Morning',
        '25' => 'Home Delivery Afternoon',
        '32' => 'Next Day < 5KG',
        '33' => 'Next Day by Noon < 5KG',
        '34' => 'Next Day by 10am < 5KG',
        '36' => 'Saturday delivery < 5KG',
        '37' => 'Saturday by Noon < 5KG',
        '38' => 'Saturday by 10am < 5KG',
        '41' => '2 Day Collect and Deliver Service',
        '42' => 'Next Day Collect and Deliver Service',
        '43' => 'Next Day by Noon Collect and Deliver Service',
        '44' => 'Next Day by 10am Collect and Deliver Service',
        '46' => 'Saturday Collect and Deliver Service',
        '47' => 'Saturday by Noon Collect and Deliver Service',
        '48' => 'Saturday by 10am Collect and Deliver Service',
        '15' => 'Timed delivery service',
        '62' => 'Direct Next Day delivery',
        '63' => 'Direct Next Day by Noon delivery',
        '64' => 'Direct Next Day by 10am delivery',
        '66' => 'Direct Saturday delivery',
        '26' => 'Afternoon Contract Service',
        '27' => 'Evening Contract Service',
        '71' => 'Pallet 2 Day',
        '72' => 'Pallet Next Day',
        '73' => 'Pallet Next Day by Noon',
        '74' => 'Pallet Next Day by 10am',
        '76' => 'Pallet SAT',
        '77' => 'Pallet SAT by Noon',
        '78' => 'Pallet SAT by 10am',
        '81' => '2 Day Freight parcel delivery',
        '82' => 'Next Day Freight parcel delivery',
        '83' => 'Next Day by Noon Freight parcel delivery',
        '84' => 'Next Day by 10am Freight parcel delivery',
        '86' => 'SAT Freight parcel delivery',
        '87' => 'SAT by Noon Freight parcel delivery',
        '88' => 'SAT by 10am Freight parcel delivery',
        '91' => '2 Day Contract Service',
        '92' => 'Next Day Contract Service',
        '99' => 'By Noon Contract Service',
        '96' => 'SAT Contract Service',
        '97' => 'SAT by Noon Contract Service',
        '98' => 'SAT by 10am Contract Service',
        '24' => 'Mail Plus',
        '01' => 'DPD Intra Ireland',
        '49' => 'Afternoon Collect and Deliver Service',
        '35' => 'Timed < 5KG',
        '45' => 'Timed Collect and Delivery Service',
        '53' => 'Evening Collect and Delivery Service',
        '55' => 'Reverse-IT Next Day',
        '56' => 'Reverse-IT Two Day',
        '75' => 'Pallet Timed',
        '85' => 'Timed Freight parcel delivery',
        '94' => 'Next Day by 10am Contract Service',
        '95' => 'Timed Contract Service',
    );
}