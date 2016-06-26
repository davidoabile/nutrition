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
* File        mysql4-upgrade-0.1.6-0.1.7.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$installer = $this;
$this->startSetup();

$actionXml = '<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path"><![CDATA[{{path}}]]></var>
    <var name="filename"><![CDATA[{{filename}}]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>
<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">moogento_shipeasy/convert_adapter_shipment</var>
    <var name="method">parse</var>
</action>';


$guiData = array(
   'export' => array(
       'add_url_field' => 0
   ),
   'import' => array(
       'number_of_records' => 1,
       'decimal_separator' => '.'
   ),
   'file' => array(
       'type' => 'file',
       'filename' => '',
       'path' => '',
       'host' => '',
       'user' => '',
       'password' => '',
       'passive' => ''
   ),
   'parse' => array(
       'type' => 'csv',
       'single_sheet' => '',
       'delimiter' => ',',
       'enclose' => '"',
       'fieldnames' => 'true'
   ),
   'map' => array(
       'only_specified' => '',
   )
);

$statusTable = $this->getTable('dataflow_profile');

$query = "SELECT count(*) FROM `". $statusTable . "` WHERE `profile_id` = '0' LIMIT 1";
$status_result = $installer->getConnection()->fetchOne($query);

if(!$status_result)
{
	$this->startSetup()->run("
	INSERT INTO `{$this->getTable('dataflow_profile')}` (`profile_id`, `name`, `created_at`, `updated_at`, `actions_xml`, `gui_data`, `direction`, `entity_type`, `store_id`, `data_transfer`) VALUES
	(0, 'Import Shipments', NOW(), NOW(), '".$actionXml."', '".serialize($guiData)."', 'import', 'shipment', 0, 'file')
	");
}
$this->endSetup();
