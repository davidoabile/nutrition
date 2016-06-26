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
* File        mysql4-upgrade-0.1.7-0.1.8.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$this_column_change = 'szy_country';

$this->startSetup();
$installer = $this;
if (!$installer->columnExists('sales/order_grid', $this_column_change)) {
    $this->getConnection()->resetDdlCache($this->getTable('sales/order_grid'));
    $this->getConnection()->addColumn(
        $this->getTable('sales/order_grid'),
        $this_column_change,
        "varchar(255) not null default ''"
    );

    $this->getConnection()->addKey(
        $this->getTable('sales/order_grid'),
        $this_column_change,
        $this_column_change
    );

    $select = $this->getConnection()->select();

    $select->join(
        array('billing_address' => $this->getTable('sales/order_address')),
        $this->getConnection()->quoteInto(
            'billing_address.parent_id = order_grid.entity_id AND billing_address.address_type = ?',
            Mage_Sales_Model_Quote_Address::TYPE_BILLING
        ),
        array()
    );

    $select->joinLeft(
        array('shipping_address' => $this->getTable('sales/order_address')),
        $this->getConnection()->quoteInto(
            'shipping_address.parent_id = order_grid.entity_id AND shipping_address.address_type = ?',
            Mage_Sales_Model_Quote_Address::TYPE_SHIPPING
        ),
        array()
    );

    $select->columns(
        array(
            "$this_column_change" => new Zend_Db_Expr(
                '
            IF (
                shipping_address.firstname IS NULL,
                billing_address.country_id,
                shipping_address.country_id
            )
        '
            )
        )
    );

    $this->getConnection()->query(
        $select->crossUpdateFromSelect(
            array('order_grid' => $this->getTable('sales/order_grid'))
        )
    );
}

$this->endSetup();
