<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Dhl extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'DHL';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'DHL';
    }

    public function getCode()
    {
        return 'DHL';
    }

    protected $_limits = array(
        'name' => 35,
        'company' => 35,
        'street' => 35,
        'district' => 35,
        'town' => 35,
        'county' => 35,
        'reference' => 35,
        'instructions' => 254,
        'countent' => 70,
    );

    protected $_servicesConfig = array(
        'DATA' => 'Data',
        'ECO' => 'Economy 2/3 Day',
        'STD' => 'Standard Next Day',
        'DIA' => 'Diamond before 10 AM',
        'ADIA' => 'Air Diamond before 10AM',
        'DIAS' => 'Saturday DIA',
        'NOON' => 'Omega 12 Next Day',
        'SAT' => 'Saturday AM',
        'GRN' => 'GRN Next Day',
        'NIA' => 'N.Ireland Next Day Delivery',
        'NIS' => 'N.Ireland Delivery 2/3 Day',
        'ISLE' => 'Offshore Isle',
        'RTN' => 'Return PARCEL',
        'X'   => 'Freight EXPRESS',
        'A' => 'Freight Express AM',
        'XTA' => 'Freight EXPRESS AM T/LIFT',
        'T' => 'Freight EXPRESS TIMED',
        'TL' => 'Freight TAIL LIFT',
        'B' => 'Freight Book IN',
        'BTL' => 'Freight Book IN + T/LIFT',
        'BWE' => 'Freight Book IN Weekend',
        'H' => 'Freight HAZARD AM',
        'HAB' => 'Freight HAZARD BOOK-IN',
        'HTL' => 'Freight HAZARD T/LIFT EXPRESS',
        '1' => 'Freight Hazard Express',
        'HWE' => 'Freight HAZARD WEEK-END',
        'W' => 'Freight TAIL LIFT',
        'EC' => 'Freight EuroFreight',
        'HR1' => 'Freight AUTHORISED RETURN',
        'SAM' => 'Select before Noon',
        'SSAT' => 'Select Saturday AM',
        'SDIA' => 'Select Diamond',
        'HDN' => 'Home Del.Next Working Day',
        'HECO' => 'Home Del.2/3 day By DHL',
        'SSTD' => 'Select Next Day',
        'DOX' => 'Document Express',
        'TDK' => 'Startday Express (Pre 9am)',
        'TDT' => 'Midday Express (Pre 12)',
        'WPX' => 'World Parcel Express',
        'ECX' => 'European Express',
        'DOM' => 'Domestic Express',
        'CRE' => 'GRN Ecomomy',
        'CRS' => 'GRN Saturday',
        'CRNI' => 'GRN NI Air',
        'CRD' => 'GRN Diamond',
        'CR2' => 'GRN before Noon',
        'TDX' => 'Startday Express (Pre 9)',
        'EHZ' => 'Freight EuroHazard',
        'ER' => 'Freight EURAPID',
        'EV' => 'Freight EVENING DELIVERY',
        'F' => 'Freight FULL-LOAD',
        'HO1' => 'Freight HOME DELY OFFSHORE',
        'HS1' => 'Freight HOME DELY SAT AM',
        'M' => 'Freight ADDITIONAL MAN',
        'OOH' => 'Freight OUT OF HOURS',
        'S' => 'Freight SAME DAY/SPECIAL',
        'EPS' => 'EUROPACK',
        'EPL' => 'EUROPLUS',
        'SURM' => 'NI to Mainland 2/3 Day',
        'AIRM' => 'NI to Mainland Nextday',
    );
}