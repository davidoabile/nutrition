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
* File        mysql4-upgrade-0.1.1-0.1.2.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$installer = $this;
$this->startSetup();

$this->getConnection()->resetDdlCache($this->getTable('sales/order_grid'));
if (!$installer->columnExists('sales/order_grid', 'szy_weight')) {
    $this->getConnection()->addColumn(
        $this->getTable('sales/order_grid'),
        'szy_weight',
        "decimal(12,4) default NULL"
    );

    $this->getConnection()->addKey(
        $this->getTable('sales/order_grid'),
        'szy_weight',
        'szy_weight'
    );
}
$this->endSetup();
