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
* File        mysql4-upgrade-0.1.5-0.1.6.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$installer = $this;
$this->startSetup();
	
try {
    $statusTable = $installer->getTable('sales/order_status');

	$query = "SELECT `status` FROM `". $statusTable . "` WHERE `status` = 'shipped' LIMIT 1";
    $status_result = $installer->getConnection()->fetchOne($query);
	if(!$status_result) {
	    $installer->getConnection()->insertArray(
	        $statusTable,
	        array('status', 'label'),
	        array(
	            array(
	                'status' => 'shipped',
	                'label'  => 'Shipped'
	            )
	        )
	    );
	}
} catch (Mage_Core_Exception $ex) {
    /*
     * Do nothing - old magento version. Statues don't have own tables
     */
}
$this->endSetup();
