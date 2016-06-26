<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Gfs_Carrier_Hdnl extends Moogento_CourierRules_Model_Connector_Carrier_Abstract
{
    protected $_label = 'Yodel';

    protected $_package_required = false;

    public function getConnectorCode()
    {
        return 'HDNL';
    }

    public function getCode()
    {
        return 'HDNL';
    }

    protected $_limits = array(
        'name' => 50,
        'company' => 50,
        'street' => 50,
        'district' => 50,
        'town' => 50,
        'county' => 50,
        'reference' => 22,
        'instructions' => 50,
        'countent' => 20,
    );

    protected $_servicesConfig = array(
        'NON' => 'Next Day Non Signature',
        'S48' => '48 Hour Non Signature',
        'POD' => 'Next Day Signature',
        '2NON' => 'Next Day Non Signature',
        'P48' => '48 Hour Signature',
        '2S48' => '48 Hour Non Signature',
        '2POD' => 'Next Day Signature',
        '2P48' => '48 Hour Signature',
        'SNO' => 'Saturday Non Signature',
        'SPO' => 'Saturday Signature',
        '2SNO' => 'Saturday Non Signature',
        '2SPO' => 'Saturday Signature',
        'S72N' => '72hr Non Signature',
        'S72P' => '72hr Signature',
        'SPOA' => 'Saturday AM Signature',
        'SPOP' => 'Saturday PM Signature',
        'SNOA' => 'Saturday AM Non Signature',
        'SNOP' => 'Saturday PM Non Signature',
        'PODA' => 'Next Day AM Signature',
        'PODP' => 'Next Day PM Signature',
        'PODE' => 'Next Day Evening Signature',
        'PODS' => 'Next Day Avoid School Run Signature',
        'NONA' => 'Next Day AM Non Signature',
        'NONP' => 'Next Day PM Non Signature',
        'NONE' => 'Next Day Evening Non Signature',
        'NONS' => 'ext Day Avoid School Run Non Signature',
        'S48A' => '48 Hour AM Non Signature',
        'S48P' => '48 Hour PM Non Signature',
        'S48E' => '48 Hour Evening Non Signature',
        'S48S' => '48 Hour Avoid School Run Non Signature',
        'P48A' => '48 Hour AM Signature',
        'P48P' => '48 Hour PM Signature',
        'P48E' => '48 Hour Evening Signature',
        'P48S' => '48 Hour Avoid School Run Signature',
        'NDP' => 'Nominated Day Signature',
        'NDPA' => 'Nominated Day AM Signature',
        'NDPP' => 'Nominated Day PM Signature',
        'NDPE' => 'Nominated Day Evening Signature',
        'NDPS' => 'Nominated Day Avoid School Run Signature',
        'NDN' => 'Nominated Day Non Signature',
        'NDNA' => 'Nominated Day AM Non Signature',
        'NDNP' => 'Nominated Day PM Non Signature',
        'NDNE' => 'Nominated Day Evening Non Signature',
        'NDNS' => 'Nominated Day Avoid School Run Non Signature',
    );
}