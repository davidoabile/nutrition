<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author david
 */
require_once 'abstract.php';

class NWH_RetailExpress extends Mage_Shell_Abstract {

    protected $stocklevelCollection = [];
    protected $toUpdate = '';
    protected $dbWrite = null;

    /**
     * Run script
     * 
     * @return void
     */
    public function run() {
        if ($this->getArg('sku')) {
            $this->toUpdate = (new DateTime(null, new DateTimeZone(NWH_RetailExpress_Helper_Data::TIMEZONE)))->format('Y-m-d H:i:s');
            $this->dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $this->syncStockLevels();
        }
    }

    public function syncStockLevels() {
        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/enable')) {
            $retailExpress = Mage::getModel('moogento_retailexpress/connector');
            $stocklevelModel = Mage::getModel('nwh_retailexpress/stocklevels');
            $this->stocklevelCollection = $stocklevelModel->getCollection()
                    ->addFieldToFilter('sku', array('eq' => $this->getArg('sku')));
            $storeCollection = $this->getStores();
            foreach ($storeCollection as $s => $store) {
                $rexProduct = null;
                if ($this->getArg('direct') !== false) {
                    $rexProduct = $retailExpress->ProductsGetDetailsStockPricingByChannel((int) $store['channel_id'], (int) $this->getArg('sku'), null, null, true);
                } else {
                    $toUpdate = Mage::helper('nwh_retailexpress')->needSyncQty($this->getArg('id'), $this->getArg('sku'));
                    if ((int) $toUpdate['shouldWeUpdate'] < 1 && $toUpdate['sku'] !== false) {
                        $rexProduct = $retailExpress->ProductsGetDetailsStockPricingByChannel((int) $store['channel_id'], (int) $this->getArg('sku'), null, null, true);
                    }
                }
                if ($rexProduct !== null) {
                    //Some products have manufacturer code assigned to them this returns all products
                    if (!isset($rexProduct['StockAvailable'])) {
                        $skus = [];
                        foreach ($rexProduct as $k => $sk) {
                            $skus[] = $sk['ProductId'];
                        }

                        $ids = $this->getIds($skus);

                        foreach ($rexProduct as $x => $product) {
                            if ($id = $ids[$product['ProductId']]) {
                                 $product['id'] = $id;
                                if (!$this->exists($store, $product)) {
                                    $product['storeId'] = $store['store_id'];
                                    $this->newItem($product, $id);
                                }
                                if ($store['code'] === 'default') {//Only HQ should update Magento's stock availability
                                    $this->updateStockitems($product, $id);
                                }
                            } elseif ($product === null && $store['code'] === 'default' && (int) $skus[$x] > 0) {
                                continue;
                                $product = Mage::getModel('catalog/product')->loadByAttribute($skus[$x], 'sku');
                                if ($product) {
                                    $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                                            ->save();
                                }
                            }
                        }
                    } else {
                        $rexProduct['id'] = $this->getArg('id');
                        if (!$this->exists($store, $rexProduct)) {
                            $rexProduct['storeId'] = $store['store_id'];
                            $this->newItem($rexProduct);
                        }
                        if ($store['code'] === 'default') {//Only HQ should update Magento's stock availability
                            $this->updateStockitems($rexProduct, (int) $this->getArg('id'));
                        }
                    }
                } elseif ($store['code'] === 'default') {//Only HQ should update Magento's stock 
                    $this->disableProduct($this->getArg('id'));
                }
            }
        }
    }

    protected function disableProduct($id) {
        return;
        $product = Mage::getModel('catalog/product')->load($id);
        if ($product) {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    ->save();
        }
    }

    protected function updateStockitems($stockItem, $productId) {
        $sql = "UPDATE " . $this->getTableName('cataloginventory_stock_item') . " csi,
                       " . $this->getTableName('cataloginventory_stock_status') . " css
                       SET
                       csi.qty = ?,
                       csi.is_in_stock = ?,
                       css.qty = ?,
                       css.stock_status = ?
                       WHERE
                       csi.product_id = ?
                       AND csi.product_id = css.product_id";
        $isInStock = $stockItem['StockAvailable'] > 0 ? 1 : 0;
        $stockStatus = $stockItem['StockAvailable'] > 0 ? 1 : 0;
        $this->dbWrite->query($sql, array($stockItem['StockAvailable'], $isInStock, $stockItem['StockAvailable'], $stockStatus, $productId));
    }

    protected function getIds($skus) {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $collection = $db->fetchAll("SELECT entity_id,sku FROM " . $this->getTableName('catalog_product_entity') . " WHERE sku IN('" . implode("','", $skus) . "')");
        $ids = [];
        foreach ($collection as $k => $v) {
            $ids[$v['sku']] = $v['entity_id'];
        }
        return $ids;
    }

    protected function getTableName($tableName) {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    protected function exists($store, $rexProduct) {
        $exists = false;
        foreach ($this->stocklevelCollection as $s => $stockLevel) {
            if ((int) $stockLevel->getChannelid() === (int) $store['channel_id'] && (int) $stockLevel->getProductId() === (int) $rexProduct['id']) {
                $exists = true;
                if ($stockLevel->getAutoUpdate() === 'Y') {
                    $stockLevel->setDump(json_encode($rexProduct));
                    $stockLevel->setQty($rexProduct['StockAvailable']);
                    $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
                    $stockLevel->setLastUpdated($this->toUpdate);
                    $stockLevel->save();
                }
                break;
            }
        }
        return $exists;
    }

    protected function newItem($rexProduct, $id = 0) {
        $productId = $id > 0 ? $id : (int) $this->getArg('id');
        $stockLevel = Mage::getModel('nwh_retailexpress/stocklevels');
        $stockLevel->setDump(json_encode($rexProduct));
        $stockLevel->setSku($rexProduct['ProductId']);
        $stockLevel->setQty($rexProduct['StockAvailable']);
        $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
        $stockLevel->setLastUpdated($this->toUpdate);
        $stockLevel->setStoreId($rexProduct['storeId']);
        $stockLevel->setChannelid($rexProduct['ChannelId']);
        $stockLevel->setProductId($productId);
        $stockLevel->save();
    }

    protected function getStores() {
        $storeCollection = Mage::app()->getStores();
        $stores = [];
        foreach ($storeCollection as $s => $st) {
            $stores[$st->getId()] = $st->getData();
        }
        //Try to have HQ to be processed first
        asort($stores);
        return $stores;
    }

}

$shell = new NWH_RetailExpress;
$shell->run();
