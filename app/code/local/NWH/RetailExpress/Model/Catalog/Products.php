<?php

class NWH_RetailExpress_Model_Catalog_Products {

    protected $defaultStore = [];
    protected $stores = [];
    protected $stockLevelCollections = null;
    protected $toUpdate = '';
    protected $dbWrite = null;

    public function __construct() {
        $this->getStores();
        $this->toUpdate = (new DateTime(null, new DateTimeZone(NWH_RetailExpress_Helper_Data::TIMEZONE)))->format('Y-m-d H:i:s');
        $this->dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function process($productData) {
        $skus = [];
        
        foreach ($productData as $k => $rex) {
            $skus[] = $rex['product_id'];
        }

        $ids = $this->getIds($skus);
        $this->getStockLevels($skus);

        foreach ($productData as $x => $product) {
            if (!$store = $this->stores[$product['channel_id']]) {
                continue;
            }
            if ($id = $ids[$product['product_id']]) {
                $product['id'] = $id;
                if (!$this->exists($store, $product)) {
                    $product['storeId'] = $store['store_id'];
                    $this->newItem($product, $id);
                }
                if ($store['code'] === 'default') {//Only HQ should update Magento's stock availability
                    $this->updateStockitems($product, $id);
                }
            }
        }
        $stockStatus = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
        if ($stockStatus->isLocked() === false) {
            $stockStatus->reindexAll();
        }
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
        $isInStock = (float) $stockItem['soh'] > 0 ? 1 : 0;
        $stockStatus = (float) $stockItem['soh'] > 0 ? 1 : 0;
        $this->dbWrite->query($sql, array($stockItem['soh'], $isInStock, $stockItem['soh'], $stockStatus, $productId));
    }

    protected function getTableName($tableName) {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    protected function exists($store, $rexProduct) {
        $exists = false;
        //This can be improved... this is based on the possibily of having 50 items to update evey 5 minutes
        //$collection = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection();
        // $collection->addFieldToFilter('sku', array('eq' => $rexProduct['ProductId']));
        // $collection->addFieldToFilter('product_id', array('eq' => $rexProduct['id']));
        // $collection->addFieldToFilter('channelid', array('eq' => $rexProduct['ChannelId']));

        foreach ($this->stockLevelCollections as $s => $stockLevel) {
            if ((int) $stockLevel->getChannelid() === (int) $store['channel_id'] && (int) $stockLevel->getProductId() === (int) $rexProduct['id']) {
                $exists = true;
                if ($stockLevel->getAutoUpdate() === 'Y') {
                    //  $stockLevel->setDump(json_encode($rexProduct));
                    $stockLevel->setQty($rexProduct['soh']);
                    $stockLevel->setStockOnOrder(0);
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
        // $stockLevel->setDump(json_encode($rexProduct));
        $stockLevel->setSku($rexProduct['product_id']);
        $stockLevel->setQty($rexProduct['soh']);
        $stockLevel->setStockOnOrder(0);
        $stockLevel->setLastUpdated($this->toUpdate);
        $stockLevel->setStoreId($rexProduct['storeId']);
        $stockLevel->setChannelid($rexProduct['channel_id']);
        $stockLevel->setProductId($productId);
        $stockLevel->setCartonQty($rexProduct['carton_qty']);
        $stockLevel->save();
    }

    protected function getStores() {
        $storeCollection = Mage::app()->getStores();
        $stores = [];
        foreach ($storeCollection as $s => $st) {
            $stores[$st->getChannelId()] = $st->getData();
            if ($st->getCode() === 'default') {
                $this->defaultStore = $st->getData();
            }
        }
        //Try to have HQ to be processed first
        asort($stores);
        $this->stores = $stores;
    }

    protected function getStockLevels($skus) {
        $this->stockLevelCollections = Mage::getModel('nwh_retailexpress/stocklevels')
                ->getCollection()
                ->addFieldToSelect(['channelid', 'product_id','auto_update'])
                ->addFieldToFilter('sku', array('IN' => $skus));
    }

}
