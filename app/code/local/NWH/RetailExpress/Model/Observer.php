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
class NWH_RetailExpress_Model_Observer {

    protected $dbWrite = null;
    protected $dbRead = null;
    private $syncInProgress = [];

    public function __construct() {
        $this->dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    public function finalPrice(Varien_Event_Observer $event) {
        return;
        $product = $event->getEvent()->getProduct();
        $productData = $product->getStockItem()->getData();
//only load simple products
        if ($productData['type_id'] === 'simple') {
//load stock levels
            $stockLevel = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection()
                    ->addFieldToFilter('channelid', array('eq' => Mage::getStoreConfig('moogento_retailexpress/general/channel_id')))
                    ->addFieldToFilter('sku', array('eq' => $product->getSku()))
                    ->getFirstItem();
//stocklevels have lastupdated which we can use to do the next update.. currently it is 5 minutes
            $shouldWeUpdate = -1;
//get the last update    
            if ($stockLevel->getSku() !== null) {
                $updated = (new DateTime($stockLevel->getLastUpdated(), new DateTimeZone(NWH_RetailExpress_Helper_Data::TIMEZONE)))->modify(Mage::getStoreConfig('nwh_retailexpress/sync/interval'));
                $lastUpdated = $updated->getTimestamp();
                $shouldWeUpdate = $lastUpdated - time();
            }

//update if necessary
            if ($shouldWeUpdate < 1) {
                $p = $product->getSku();
                if (!in_array($product->getId(), $this->syncInProgress)) {
//Process all stores in a non blocking manner
                    exec('php ' . Mage::getBaseDir() . '/shell/retailExpress.php -sku ' . $product->getSku() . ' -id ' . $product->getId() . ' > /dev/null  2>&1 &');
                    $this->syncInProgress[] = $product->getId();
                }
            }
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

    protected function getTableName($tableName) {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    protected function updateQty($rexProduct, $product = array()) {
        if ($rexProduct !== null) {// this will only update the main store
            $dbToUpdate = (new DateTime(null, new DateTimeZone(NWH_RetailExpress_Helper_Data::TIMEZONE)))->format('Y-m-d H:i:s');
            $stockLevel = $product['stockLevel'];
            exec('php ' . Mage::getBaseDir() . '/shell/retailExpress.php -sku ' . $product['sku'] . ' -id ' . $product['id'] . ' > /dev/null  2>&1 &');
            $this->updateStockitems($rexProduct, $product['id']);
            if ($stockLevel->getAutoUpdate() === 'Y') {//only update if not overriden "Rob"
                $stockLevel->setDump(json_encode($rexProduct));
                $stockLevel->setQty($rexProduct['StockAvailable']);
                $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
                $stockLevel->setLastUpdated($dbToUpdate);
                $stockLevel->save();
            } elseif ($stockLevel->getSku() === null) {// create an entry if not exitsts
                $stockLevel->setSku($product['sku']);
                $stockLevel->setShowWeb('Y');
//this only applied to the main store
                $stockLevel->setChannelid(Mage::getStoreConfig('moogento_retailexpress/general/channel_id'));
                $stockLevel->setStoreId(Mage::app()->getStore()->getId());
                $stockLevel->setDump(json_encode($rexProduct));
                $stockLevel->setQty($rexProduct['StockAvailable']);
                $stockLevel->setStockOnOrder($rexProduct['StockOnOrder']);
                $stockLevel->setLastUpdated($dbToUpdate);
                $stockLevel->save();
            }
        }
    }

    public function setOrderView(Varien_Event_Observer $event) {
        $collection = $event->getCollection();
        $grid = $event->getGrid();
        $store = $grid->getParam('store', false);
        if ($store !== false) {
            $collection->addFilter('store_id', $store);
        }

        $adminUser = Mage::getSingleton('admin/session');
        $adminuserId = $adminUser->getUser()->getUserId();
        $role = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        if ($role['role_name'] !== 'Administrators') {
            $storeCollection = Mage::app()->getStores();
            $storeName = strtolower($role['role_name']);
            foreach ($storeCollection as $s => $st) {
                if (strtolower($st->getName()) === $storeName) {
                    $collection->addFilter('store_id', $st->getId());
                    break;
                }
            }
        }
    }

    public function hideStoreSwitcher(Varien_Event_Observer $event) {
//this doesn't work so i have used a js on the widget/grid/container.phtml 
        $grid = $event->getGrid();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        if ($role['role_name'] === 'Administrators') {
            $grid->getLayout()->unsetBlock('store_switcher');
        }
    }

    /**
     * psudo code
     * 
     * Get customer order
     * Check their state and match it to out outlets with channels
     * If state belongs to one of the outlets, 
     * Check if the outlet can fullfil all items else warehouseHQ
     * if yes - check with fastway label colour if they are within the allowed colours
     * otherwise send the order to warehouseHQ
     * If all good assign to that outlet and send the order to REX
     * 
     * default to our warehouseHQ
     * 
     * @param Varien_Event_Observer $event
     */
    public function distributeOrder(Varien_Event_Observer $event) {
        $order = $event->getEvent()->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if ($order->getDistributed() !== null) {
            return;
        }
        $order->setData('distributed', true);
        $storeCollection = Mage::app()->getStores();
        $stores = [];
        $defaultStore = [];
        foreach ($storeCollection as $s => $st) {
            $stores[strtolower($st->getState())] = $st->getData();
            if ($st->getCode() === 'default') {
                $defaultStore = $st->getData();
            }
        }
        $region = Mage::getModel('directory/region')->load($shippingAddress->getRegionId());
//check customer's shipping address if it matches one of our outlets
        $shippingState = strtolower($region->getCode());
        if (isset($stores[$shippingState])) {
            $store = $stores[$shippingState];

// get ordered products and get items and weight
            $weight = 0;
            $orderedItems = [];
            $orderItems = $order->getAllVisibleItems();
            $excludedProducts = array();
            if (Mage::getStoreConfig('moogento_retailexpress/order/exclude_products')) {
                $excludedProducts = preg_split("/\r?\n/", Mage::getStoreConfig('moogento_retailexpress/order/exclude_products'));
                array_walk($excludedProducts, array($this, 'cleanSkus'));
            }

            foreach ($orderItems as $item) {
                if ($item->getProductType() === 'simple' && !in_array($item->getSku(), $excludedProducts)) {
                    $weight += (float) $item->getWeight();
                    if (isset($orderedItems[$item->getProductId()])) {
                        $orderedItems[$item->getProductId()] = (float) $orderedItems[$item->getProductId()] + (float) $item->getQtyOrdered();
                    } else {
                        $orderedItems[$item->getProductId()] = $item->getQtyOrdered();
                    }
                }
            }
            $stockLevelsOk = true;
            if ($store['code'] !== 'default') { //don't do warehouseHQ
//our stock locations
                $stocklevelColletion = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection()
                        ->addFieldToSelect(['sku', 'qty', 'product_id', 'channelid'])
                        ->addFieldToFilter('product_id', array('in' => array_keys($orderedItems)))
                        ->addFieldToFilter('channelid', array('eq' => (int) $store['channel_id']));
                $stocklevelColletion->getSelect()->group('channelid');

//if items from stocklocation is not the same as from the customer ordered then don't do anything
                if ((int) $stocklevelColletion->count() === (int) count($orderedItems)) {
                    foreach ($stocklevelColletion as $k => $stock) {
//check if we have proper qty to send to an outlet
                        if ((float) $orderedItems[$stock->getProductId()] > (float) $stock->getQty()) {
                            $stockLevelsOk = false;
                            break; // don't bother doing the rest
                        }
                    }
                } else {// we don't have all stock
                    $stockLevelsOk = false;
                }
//Default to our warehouseHQ
                if ($stockLevelsOk === false) {
                    $store = $defaultStore;
                }
            }
//change of plans get fastway colours for all outlets
            if ($colour = $this->checkFastway($weight, $store, $shippingAddress)) {
//FINALLY!!!! change the order to new outlet
//I think Rob wants to get all the orders so many rules
                $order->setChannelid($store['channel_id']);
                $order->setFastwayColour($colour);
                if ($store['code'] !== 'default') {
// update store_id
                    foreach ($order->getAllItems() as &$item) {
                        $item->setStoreId($store['store_id']);
                    }
                    $order->setStoreId($store['store_id']);
                }
            }
        } //default to warehouseHQ
    }

    public function checkSpecialItems($observer) {
        $order = $observer->getOrder();
        if ($order) {
            $category = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('name', NWH_RetailExpress_Model_Cron::CATEGORYNAME)
                    ->getFirstItem();
            $items = [];
            foreach ($order->getAllItems() as $i => $item) {
                $items[$item->getProductId()] = $item->getQtyInvoiced();
            }
            $productCollection = $category->getProductCollection()->addAttributeToSelect(array('special_to_date', 'max_sales_items'));

            foreach ($productCollection as $k => $product) {
                if ($product->getTypeId() === 'configurable' || $product->getTypeId() === 'bundle') {
                    if ($product->getTypeId() === 'configurable') {
                        $allProducts = $product->getTypeInstance(true)
                                ->getUsedProducts(null, $product);
                    } else {
                        $allProducts = $product->getTypeInstance(true)->getSelectionsCollection(
                                $product->getTypeInstance(true)->getOptionsIds($product), $product);
                    }

                    $productsCount = count($allProducts);
                    foreach ($allProducts as $salesProduct) {
                        if (isset($items[$salesProduct->getId()])) {
                            $max = $salesProduct->getMaxSalesItems();
                            if ($max !== null) {
                                $salesProduct->setMaxSalesItems((float) $max - (float) $items[$salesProduct->getId()]);
                                $salesProduct->save();
                            }
                        }
                    }
                } elseif (isset($items[$product->getId()])) {
                    $max = $product->getMaxSalesItems();
                    if ($max !== null) {
                        $product->setMaxSalesItems((float) $max - (float) $items[$product->getId()]);
                        $product->save();
                    }
                }
            }
        }
    }

    public function catalogProductView($observer) {

        if (Mage::helper('nwh_retailexpress')->enableDirectSync() === true) {
            $productId = $observer->getControllerAction()->getRequest()->getParam('id');
            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product->getTypeId() === 'configurable' || $product->getTypeId() === 'bundle') {
                if ($product->getTypeId() === 'configurable') {
                    $allProducts = $product->getTypeInstance(true)
                            ->getUsedProducts(null, $product);
                } else {
                    $allProducts = $product->getTypeInstance(true)->getSelectionsCollection(
                            $product->getTypeInstance(true)->getOptionsIds($product), $product);
                }


                foreach ($allProducts as $config) {
                    Mage::helper('nwh_retailexpress')->sync($config);
                }
            } else {
                Mage::helper('nwh_retailexpress')->sync($product);
            }
            $stockStatus = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
            if ($stockStatus->isLocked() === false) {
                $stockStatus->reindexAll();
            }
        }
    }

    /**
     *
     * update stock status of quote products before cart load
     *
     */
    public function checkoutCartIndex($observer) {
        if (Mage::helper('nwh_retailexpress')->enableDirectSync() === true) {
            if (!$productIds = $this->_getQuoteProducts()) {
                return;
            }
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $email = $quote->getCustomerEmail();
            $quote->setHideNewsletterSubscription('N');

            if (Mage::getSingleton('core/session')->getSuccessNewsletter() === 'success') {
                $quote->setHideNewsletterSubscription('Y');
            } elseif (!empty($email)) {
                $subscriber = Mage::getModel('newsletter/subscriber')->getCollection()
                        ->addFieldToFilter('subscriber_email', array('eq' => $email));
                if ($subscriber->count()) {
                    $quote->setHideNewsletterSubscription('Y');
                }
            }
//all items in the cart
            foreach ($productIds as $productId) {
                Mage::helper('nwh_retailexpress')->syncById($productId);
            }
        }
    }

    /**
     * looks like strange way to get quote items, right?
     * doing this because $quote->getVisibleItems() loads product stock items and cache data
     * we not able to change stock of products after getVisibleItems is called
     * returns false or products array
     *
     * @return mixed
     */
    protected function _getQuoteProducts() {
// get session
        $session = Mage::getSingleton('checkout/session');
// get quote id
        $quoteId = $session->getQuoteId();
        if (!$quoteId) {
// no quote
            return false;
        }
        if ($quoteId) {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('read');
            $tPrefix = (string) Mage::getConfig()->getTablePrefix();
            $quoteTable = $tPrefix . 'sales_flat_quote_item';
            $query = "SELECT `product_id` FROM {$quoteTable} WHERE `product_type` = 'simple' AND `quote_id` = {$quoteId}";
//fetch products
            $products = $read->fetchAll($query);
            if (!count($products)) {
//no products
                return false;
            }
            $productIds = array();
            foreach ($products as $product) {
                $productIds[] = $product['product_id'];
            }
        }

        return $productIds;
    }

    public function setDiscountCouponCode2(Varien_Event_Observer $observer) {
        
    }

    protected function checkFastway($w, $store, $shippingAddress) {
        $weight = (int) ($w / 1000);
//http://au.api.fastway.org/latest/psc/lookup/GC/city/code/?api_key=53ec71366356216ca1e462a7bc2051ac
        $url = '/psc/lookup/' . $store['fastway_rfcode'] . '/' . rawurlencode($shippingAddress->getCity()) . '/' . $shippingAddress->getPostcode() . '/' . $weight . '?';
        $fastWayResult = Mage::helper('nwh_retailexpress')->getCurlFastWay($url);
        if (isset($fastWayResult['result']['services'])) {
            $colour = $fastWayResult['result']['services'][0]['labelcolour'];
            $allowedColours = [];
            foreach (explode(',', $store['fastway_colours']) as $v) {
                $allowedColours[] = trim($v);
            }
            if (in_array($colour, $allowedColours)) {
                return $colour;
            }
        }
        return false;
    }

    public function addShipping($observer) {
        $event = $observer->getEvent();
        $quote = $event->getCart()->getQuote();
        $rates = $quote->getShippingAddress()->getAllShippingRates();
        $allowed_rates = array();
        foreach ($rates as $rate) {
            array_push($allowed_rates, $rate->getCode());
        }

        if (!in_array($this->_shippingCode, $allowed_rates) && count($allowed_rates) > 0) {
            $shippingCode = $allowed_rates[0];
            $quote->getShippingAddress()->setShippingMethod($shippingCode)->save();
        }
        return;
    }

}
