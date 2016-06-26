<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        mysql4-upgrade-0.1.16-0.1.17.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$this_column_change = 'szy_tracking_number';

$installer = $this;
$installer->startSetup();


$select = $this->getConnection()->select();
$select->from(
    array('config_table' => $this->getTable('core/config_data')),
    array('value' => 'value')
)->where(
    'config_table.path = "moogento_shipeasy/carriers/base_format"'
);

$codes = array();
$currentCarrierFormat = $this->getConnection()->fetchOne($select);
if ($currentCarrierFormat) {
    try {

    } catch (Exception $e) {
        $currentCarrierFormat = unserialize($currentCarrierFormat);
        $codes = array();
        foreach($currentCarrierFormat as $rowId => $carrierData) {
            $codes[$carrierData['code']] = $rowId;
        }
    }
}

$shipmentTrackFields = $installer->getConnection()->describeTable($this->getTable('sales/shipment_track'));
$trackNumberField = (isset($shipmentTrackFields['number'])) ? 'number' : 'track_number';


$select = $this->getConnection()->select();
$select->from(
    array('track_table' => $this->getTable('sales/shipment_track')),
    array(
        'order_id',
        $trackNumberField,
        'title',
        'carrier_code'
    )
);

$data = $this->getConnection()->fetchAll($select);
if ($data) {
    $orderTracks = array();
    foreach($data as $carrierInfo) {
        $trackInternalCode = '';
        foreach($codes as $internalCode => $rowId) {
            if (strpos(strtolower($carrierInfo[$trackNumberField]), strtolower($internalCode)) === 0) {
                $trackInternalCode = $internalCode;
                break;
            }
        }
        if ($trackInternalCode) {
            $orderTracks[$carrierInfo['order_id']][] = array(
                'code' => $trackInternalCode,
                'number' => $carrierInfo[$trackNumberField]
            );
        } else {
            $orderTracks[$carrierInfo['order_id']][] = array(
                'code' => 'custom',
                'number' => $carrierInfo[$trackNumberField]
            );
        }
    }

    foreach($orderTracks as $orderId => $trackingData) {
        $this->getConnection()->update(
            array('order_grid_table' => $this->getTable('sales/order_grid')),
            array($this_column_change => serialize($trackingData)),
            "entity_id = {$orderId}"
        );
    }
}

$installer->endSetup();
