<?php

class Moogento_RetailExpress_Model_Cron {

    protected $stocklevelCollection = [];
    protected $mageSimpleProductsCollection = [];
    protected $outlets = [];
    protected $dbWrite = null;
    protected $dbRead = null;

    public function __construct() {
        if (file_exists('inventory.csv')) {
            $csv = new Varien_File_Csv();
            $data = $csv->getData('inventory.csv'); //path to csv
            $this->dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $this->dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            foreach ($data as $k => $v) {
                if ($k === 0) {
                    continue;
                }
                $this->outlets[$v[1]][] = ['sku' => $v[5]];
            }
        }
    }

    public function importProducts() {
        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/debug')) {
            return;
        }

        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/enable') &&
                Mage::getStoreConfig('moogento_retailexpress/product/mode') == Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Product_Mode::MODE_IMPORT
        ) {
            $connector = Mage::getModel('moogento_retailexpress/connector');

            $lastUpdate = Mage::getStoreConfig('moogento_retailexpress/product/last_import_date');
            if (!$lastUpdate) {
                $lastUpdate = date('U') - 86400000;
            }
            $connector->ProductsGetBulkDetailsByChannel(Mage::getStoreConfig('moogento_retailexpress/general/channel_id'), date('Y-m-d\Th:m:s', $lastUpdate));

            $config = new Mage_Core_Model_Config();
            $config->saveConfig('moogento_retailexpress/product/last_import_date', $lastUpdate);
        }
    }

    public function exportOrders() {
        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/debug'))
            return;

        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/enable')) {
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->getSelect()->where('retail_express_status in (?)', array(
                Moogento_RetailExpress_Model_Retailexpress_Status::PENDING,
                Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY
            ));
            $collection->getSelect()->order('created_at DESC');

            $statusesToProcess = explode(',', Mage::getStoreConfig('moogento_retailexpress/order/statuses'));
            if (!count($statusesToProcess)) {
                return;
            }

            $collection->getSelect()->where('status in ("' . implode('","', $statusesToProcess) . '")');

            $collection->setPageSize(10);

            $failsCount = 0;

            $connector = Mage::getModel('moogento_retailexpress/connector');
            foreach ($collection as $one) {
                if ($failsCount >= Mage::getStoreConfig('moogento_retailexpress/general/fail_limit')) {
                    continue;
                }
                $one->setRetailExpressStatus(Moogento_RetailExpress_Model_Retailexpress_Status::PROCESSING);
                $one->save();
                $connector->processOrder($one);

                if ($one->getRetailExpressStatus() == Moogento_RetailExpress_Model_Retailexpress_Status::ERROR || $one->getRetailExpressStatus() == Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY) {
                    $failsCount++;
                }
            }
            $config = new Mage_Core_Model_Config();
            if ($failsCount >= Mage::getStoreConfig('moogento_retailexpress/general/fail_limit')) {
                $config->saveConfig('moogento_retailexpress/general/debug', 1);
                $config->saveConfig('moogento_retailexpress/general/show_fail_message', 1);
            } else {
                $config->saveConfig('moogento_retailexpress/general/show_fail_message', 0);
            }
        }
    }

    public function syncStockLevels() {
        if (Mage::getStoreConfigFlag('moogento_retailexpress/general/enable')) {
            $retailExpress = Mage::getModel('moogento_retailexpress/connector');
            $stocklevelModel = Mage::getModel('nwh_retailexpress/stocklevels');
            $this->stocklevelCollection = $stocklevelModel->getCollection();
            $this->getAllProducts();
            $storeCollection = Mage::app()->getStores();
            $stores = [];
            foreach ($storeCollection as $s => $st) {
                $stores[strtolower($st->getName())] = $st->getData();
            }

            //load all stores so that we can fetch stock updates for each
            foreach ($this->outlets as $outlet => $products) {
                if (!isset($stores[strtolower($outlet)])) {
                    continue; //load stores that we have created in Magento
                }
                $store = $stores[strtolower($outlet)];
                //this may take some time;
                foreach ($products as $pr => $info) {
                    $rexProduct = $retailExpress->ProductsGetDetailsStockPricingByChannel((int) $store['channel_id'], (int) $info['sku'], null, null, true);
                    if ($rexProduct !== null) {
                        if (!$this->exists($store, $rexProduct)) {
                            $rexProduct['storeId'] = $store['store_id'];
                            $this->newItem($rexProduct);
                        }
                        //update Magento stock levels for our default store
                        if ($store['code'] === 'default') {
                            $this->updateStockitems($rexProduct);
                        }
                    }
                }
            }
        }
    }

    protected function exists($store, $rexProduct) {
        $exists = false;
        foreach ($this->stocklevelCollection as $s => $stockLevel) {
            if ((int) $stockLevel->getChannelid() === (int) $store['channel_id'] && (int) $stockLevel->getSku() === (int) $rexProduct['ProductId']) {
                $exists = true;
                if ($stockLevel->getAutoUpdate() === 'Y') {
                    //  $stockLevel = Mage::getModel('nwh_retailexpress/stocklevels')->load($levels->getId());
                    $stockLevel->setDump(json_encode($rexProduct));
                    $stockLevel->setQty($rexProduct['StockAvailable']);
                    $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
                    $stockLevel->setLastUpdated($rexProduct['LastUpdated']);
                    $stockLevel->save();
                }
                // unset($this->stocklevelCollection[$s]);
                break;
            }
        }
        return $exists;
    }

    protected function newItem($rexProduct) {
        $stockLevel = Mage::getModel('nwh_retailexpress/stocklevels');
        $stockLevel->setDump(json_encode($rexProduct));
        $stockLevel->setSku($rexProduct['ProductId']);
        $stockLevel->setQty($rexProduct['StockAvailable']);
        $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
        $stockLevel->setLastUpdated($rexProduct['LastUpdated']);
        $stockLevel->setStoreId($rexProduct['storeId']);
        $stockLevel->setChannelid($rexProduct['ChannelId']);
        $stockLevel->save();
    }

    protected function updateStockitems($stockItem) {
        if ($productId = $this->getProductId($stockItem['ProductId'])) {
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
    }

    protected function getProductId($sku) {
        if (isset($this->mageSimpleProductsCollection[$sku])) {
            return $this->mageSimpleProductsCollection[$sku];
        }
        return false;
    }

    protected function getAllProducts() {
        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                ->addAttributeToSelect('sku'); //or just the attributes you need

        $collection->getSelect()->joinLeft(array('link_table' => 'catalog_product_super_link'), 'link_table.product_id = e.entity_id', array('product_id')
        );
        $collection->getSelect()->where('link_table.product_id IS NOT NULL');

        foreach ($this->outlets as $p => $item) {
            $this->mageSimpleProductsCollection[$item->getSku()] = $item->getId();
        }
    }

    protected function getTableName($tableName) {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

}
