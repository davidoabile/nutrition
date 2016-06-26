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
* File        mysql4-upgrade-0.1.8-0.1.9.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

$this_column_change = 'szy_product_skus';

$this->startSetup();
$installer = $this;

if (!$installer->columnExists('sales/order_grid', $this_column_change)) {
    $this->getConnection()->resetDdlCache($this->getTable('sales/order_grid'));
    $this->getConnection()->addColumn(
        $this->getTable('sales/order_grid'),
        $this_column_change,
        "tinytext"
    );

    $_allowedProductTypes = array('bundle', 'simple', 'virtual', 'downloadable');

    $select = $this->getConnection()->select();
    $select->from(
        $this->getTable('sales/order_item'),
        array('order_id', 'sku')
    );
    $select->where(
        $this->getConnection()->quoteInto(
            'product_type IN (?)',
            $_allowedProductTypes
        )
    );


    $result    = $this->getConnection()->fetchAll($select);
    $orderSkus = array();


    foreach ($result as $orderItem) {
        $orderId = $orderItem['order_id'];
        if (isset($orderSkus[$orderId])) {
            $orderSkus[$orderId] .= ',' . $orderItem['sku'];
        } else {
            $orderSkus[$orderId] = $orderItem['sku'];
        }
    }

    $counter = 0;
    $sql     = '';

    foreach ($orderSkus as $orderId => $productSkus) {
        $productSkus = $this->getConnection()->quote($productSkus);
        $sql .= "UPDATE `{$this->getTable('sales/order_grid')}` SET `" . $this_column_change
            . "` = {$productSkus} WHERE entity_id = {$orderId};";
        $counter++;
        if ($counter % 250 == 0) {
            $this->getConnection()->beginTransaction();
            $this->run($sql);
            $this->getConnection()->commit();
            $sql = '';
        }
    }

    if (!empty($sql)) {
        $this->getConnection()->beginTransaction();
        $this->run($sql);
        $this->getConnection()->commit();
    }
}

$this->endSetup();
