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
* File        mysql4-upgrade-0.1.15-0.1.16.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$installer = $this;
$installer->startSetup();

$select = $this->getConnection()->select();
$select->from(
    array('config_table' => $this->getTable('core/config_data')),
    array('value' => 'value')
)->where(
    'config_table.path = "moogento_shipeasy/carriers/base_format"'
);

$currentCarrierFormat = $this->getConnection()->fetchOne($select);
$currentCarrierFormat = explode("\n", $currentCarrierFormat);

$newCarrierFormat = '';
$counter = 0;
foreach($currentCarrierFormat as $carrierFormat) {
    $carrierFormat = trim($carrierFormat);
    if (!$carrierFormat) {
        continue;
    }
    $carrierFormat = explode(':', $carrierFormat, 3);
    if (!is_array($carrierFormat)) {
        continue;
    }

    $code = $carrierFormat[0];
    $title = $carrierFormat[1];
    $link = (!empty($carrierFormat[2])) ? $carrierFormat[2] : '';

    if (!is_array($newCarrierFormat)) {
        $newCarrierFormat = array();
    }
    $counter++;
    $newCarrierFormat["row_{$counter}"] = array(
        'code' => $code,
        'title' => $title,
        'link'  => $link
    );
}

if (is_array($newCarrierFormat)) {
    $newCarrierFormat = serialize($newCarrierFormat);
}

$this->getConnection()->update(
    array('config_table' => $this->getTable('core/config_data')),
    array('value' => $newCarrierFormat),
    'path = "moogento_shipeasy/carriers/base_format"'
);

$installer->endSetup();
