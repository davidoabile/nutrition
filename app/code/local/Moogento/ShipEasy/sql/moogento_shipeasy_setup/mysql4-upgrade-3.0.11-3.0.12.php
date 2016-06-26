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
* File        mysql4-upgrade-0.1.11-0.1.12.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$this_column_change = 'szy_shipping_method';

$this->startSetup();
$installer = $this;

$this->getConnection()->resetDdlCache($this->getTable('sales/order_grid'));

if ($installer->columnExists('sales/order_grid', 'szy_shipping_method')) {
    $this->getConnection()->dropKey(
        $this->getTable('sales/order_grid'),
        $this_column_change
    );

    $this->getConnection()->dropColumn(
        $this->getTable('sales/order_grid'),
        $this_column_change
    );
}

$this->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    $this_column_change,
    'varchar(255) default NULL'
);
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    $this_column_change,
    $this_column_change
);

//Left column: order_grid | right: order_table
$select = $this->getConnection()->select();
$select->join(
    array('order_table'=>$this->getTable('sales/order')),
    'order_table.entity_id = order_grid.entity_id',
    array($this_column_change => 'shipping_method')
);

$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);



//Step 3: remove column
$this_column_change = 'szy_shipping_description';
if ($installer->columnExists('sales/order_grid', 'szy_shipping_method')) {

    $this->getConnection()->dropColumn(
        $this->getTable('sales/order_grid'),
        $this_column_change
    );
}

$this->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    $this_column_change,
    'varchar(255) default NULL'
);
$select = $this->getConnection()->select();
$select->join(
    array('order_table'=>$this->getTable('sales/order')),
    'order_table.entity_id = order_grid.entity_id',
    array($this_column_change => 'shipping_description')
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$this->endSetup();
