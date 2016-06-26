<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_RetailExpress_IndexController extends Mage_Core_Controller_Front_Action {

    protected $stocklevelCollection = [];
    protected $sku = null;
    protected $skuArr = [];
    protected $idArr = [];
    protected $categoryName = 'clearance';

    public function indexAction() {
        // $calc = Mage::getSingleton('tax/calculation');
        // $rates = $calc->getRatesForAllProductTaxClasses($calc->getRateRequest());

        $excludedProducts = array();
        if (Mage::getStoreConfig('moogento_retailexpress/order/exclude_products')) {
            $excludedProducts = preg_split("/\r?\n/", Mage::getStoreConfig('moogento_retailexpress/order/exclude_products'));
            array_walk($excludedProducts, array($this, 'cleanSkus'));
        }
        var_dump($excludedProducts);
        exit;
        $cron = new NWH_RetailExpress_Model_Cron();
        $cron->sync();
        die('hit');
        exit;
        $cartItems = Mage::getModel('sales/order')->load(21579);

        foreach ($cartItems->getAllItems() as $orderItem) {
            if ($orderItem->getProduct()->isComposite()) {
                continue;
            }

            $parentItem = $orderItem->getParentItem();
            $parentPrice = ($parentItem->getPriceInclTax() - $parentItem->getDiscountAmount());

            $bundleChildSum = 0;
            foreach ($parentItem->getChildrenItems() as $child) {
                $bundleChildSum += $child->getQtyOrdered();
            }
            var_dump($parentPrice / $bundleChildSum);
            // return $this->_formatPrice($parentPrice * $productPrice / $bundleChildSum);
        }


        exit;
        $this->update();
        $helper = Mage::helper('nwh_retailexpress');
        foreach (self::SYNCOBJECTS as $k => $v) {
            $result = $helper->getCurl($v, $this->lastUpdated);

            if ($result['success'] === true) {
                switch ($k) {
                    case 'products' :
                        (new NWH_RetailExpress_Model_Catalog_Products())->process($result['data']);
                        break;
                    case 'customers' :
                        (new NWH_RetailExpress_Model_Customer_Customer())->process($result['data']);
                        break;
                }
                break;
            }
        }
        $this->lastUpdated['start'] = $this->lastUpdated['stop'];
        $this->saveSyncLastUpdated();

        echo 'done';
        exit;
        //special_to_date
        //max_sales_items

        $storeCollection = Mage::app()->getStores();
        foreach ($storeCollection as $s => $st) {
            $store[$st->getId()] = $st->getData();
        }
        asort($store);

        var_dump($store);
        exit;

        //   (new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/carriers/fastway_api_key', '53ec71366356216ca1e462a7bc2051ac');
        // $helper = Mage::helper('nwh_retailexpress');
        // var_dump($helper->getCurlFastWay('http://api.fastway.org/latest/psc/listrfs?CountryCode=1', ['redisKey' => 'fastWayRFCodes']));
        $stocklevelColletion = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection()
                ->addFieldToFilter('sku', array('in' => ['128275', '128274']))
                ->addFieldToSelect(['sku', 'qty'])
                ->addFieldToFilter('channelid', array('eq' => 5));
        if ($stocklevelColletion->count()) {
            foreach ($stocklevelColletion as $k => $stock) {
                var_dump($stock->getData());
            }
        }


        echo 'done';
        exit;
        return [];
    }

    protected function _formatPrice($price) {
        return number_format($price, 2, '.', '');
    }

    public function levelsAction() {
        $cron = new NWH_RetailExpress_Model_Cron(['reset' => true]);
        $cron->sync();
        die('Finished');
    }

    public function importFromRexAction() {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbWriter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $customerCollection = $db->fetchAll(
                " SELECT CustomerId,BillEmail FROM customers 
                  INNER JOIN  `customer_entity` ON billEmail=email"
        );
        $updater = "INSERT INTO customer_entity_int VALUES( NULL, 1, 837, ?, ?)";
        $siteID = Mage::getModel('core/website')->getCollection()->getFirstItem()->getId();
//
        $dbWriter->query('DELETE FROM customer_entity_int WHERE attribute_id=837');

        foreach ($customerCollection as $k => $c) {
            $customer = Mage::getModel('customer/customer')->setData('website_id', $siteID)->loadByEmail($c['BillEmail']);
            if ($customer->getId() && (int) $customer->getRetailExpressId() === 0) {
                $dbWriter->query($updater, [$customer->getId(), $c['CustomerId']]);
            }
        }
    }

    public function syncCustomerAction() {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT DISTINCT c.entity_id, email from customer_entity c
                INNER JOIN sales_flat_order o On c.entity_id = customer_id
                WHERE c.entity_id NOT IN( select entity_id FROM customer_entity_int WHERE attribute_id=837)";
        $customersQuery = $db->fetchAll($sql);
        $rexCustomerObejct = new NWH_RetailExpress_Model_Customer_Customer();
        try {
            foreach ($customersQuery as $k => $cust) {
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $result = $rexCustomerObejct->putCustomer($customer->load($cust['entity_id']));
                var_dump($result['data']['seqno']);
                exit;
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
        echo 'done ... :)';
    }

    public function startrackAction() {
        new NWH_RetailExpress_Model_Autoloader();
        $starTrack = (new Shipping\Shipping())->getStarTrack()->load();
    }

    public function auspostAction() {
        $auspost = new NWH_RetailExpress_Model_Shipping_AusPost();
        $p = $this->getRequest()->getParam('p');

        switch ($p) {
            case 'track' :
                $auspost->getTrackProgress();
                break;
            case 'ship' :
                $auspost->getShipments();
                break;
            case 'account' :
                $auspost->getAccount();
                break;
            case 'order' :
                $auspost->createOrder();
                break;
            case 'labels' :
                $auspost->printLables();
                break;
            case 'verify' :
                $auspost->addressVerify();
                break;
        }
    }

    protected function saveSyncLastUpdated() {
        $arrayString = "<?php\n"
                . "return " . var_export($this->lastUpdated, true) . ";\n";
        file_put_contents($this->filename, $arrayString);
    }

    public function quickAction() {
        (new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/newsletter/code', '$5OFF');
        (new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/sync/interval', '+5 minutes');
        (new Mage_Core_Model_Config())->saveConfig('nwh_retailexpress/sync/enabled', 'Y');
    }

    public function gridAction() {
        $sku = $this->sku !== null ? $this->sku : $this->getRequest()->getParam('sku', false);
        $jsonData = ['success' => false, 'data' => []];
        if ($sku !== false || sizeof($this->skuArr) > 0 || sizeof($this->idArr) > 0) {
            $stocklevels = [];
            $storeCollection = Mage::app()->getStores();
            //load all stores so that we can fetch stock updates for each
            $stores = [];
            foreach ($storeCollection as $s => $st) {
                $stores[$st->getId()] = $st->getData();
            }

            $collection = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection();
            if (count($this->skuArr) > 0) {
                $collection->addFieldToFilter('sku', array('IN' => $this->skuArr));
            } elseif (count($this->idArr) > 0) {
                $collection->addFieldToFilter('product_id', array('IN' => $this->idArr));
            } else {
                $collection->addFieldToFilter('sku', array('eq' => $sku));
            }
            //  ->load();
            // echo $collection->getSelect()->__toString(); exit;
            $defaultChannel = [];
            foreach ($collection as $k => $v) {
                $data = $v->getData();
                unset($data['dump']);
                $data['name'] = $stores[$data['store_id']]['name'];

                if ((int) $data['channelid'] === (int) Mage::getStoreConfig('moogento_retailexpress/general/channel_id')) {
                    $defaultChannel = $data;
                    // if (count($this->skuArr) > 0 || count($this->idArr) > 0) {
                    $data['defaultChannel'] = $data;
                    // }
                }
                $stocklevels[] = $data;
            }
            $jsonData = [ 'success' => true, 'data' => $stocklevels, 'defaultChannel' => $defaultChannel];
        }

        if ($this->sku !== null || count($this->skuArr) > 0 || count($this->idArr) > 0) {
            $stocklevels['defaultChannel'] = $defaultChannel;
            return $stocklevels;
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
    }

    public function syncAction() {
        $params = $this->getRequest()->getParams();
        $jsonData = ['success' => false, 'reason' => 'No data found'];
        try {
            if ((int) $params['productId'] > 0) {
                $this->sku = $params['sku'];
                //exec('php ' . Mage::getBaseDir() . '/shell/retailExpress.php -sku ' . $params['sku'] . ' -id ' . $params['productId']);
                Mage::helper('nwh_retailexpress')->syncById($params['productId'], $params['sku'], false);
                $stockLevels = $this->gridAction();
                unset($stockLevels['defaultChannel']);
                $jsonData = ['success' => true, 'data' => $stockLevels];
            }
        } catch (Exception $e) {
            $jsonData['reason'] = $e->getMessage();
        } finally {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
        }
    }

    public function syncConfigurableAction() {
        $params = $this->getRequest()->getParams();
        $jsonData = ['success' => false, 'reason' => 'No data found'];
        try {
            if ((int) $params['productId'] > 0 && $params['sku'] > 0) {
                $this->sku = $params['sku'];
                Mage::helper('nwh_retailexpress')->syncById($params['productId'], $params['sku'], false);
                $grid = $this->gridAction();
                $rexProduct = $grid['defaultChannel'];
                $rexProduct['stockQty'] = $rexProduct['qty'];
                $rexProduct['is_in_stock'] = true;
                $rexProduct['stockLabel'] = $this->__('In Stock');
                if ((int) $rexProduct['qty'] < 1) {
                    $item = Mage::getModel('catalog/product')->load($params['productId']);
                    $path = isset($params['path']) ? $params['path'] : Mage::helper('core/http')->getHttpReferer();

                    Mage::register('currentUrl', Mage::getUrl(ltrim($path, '/')));
                    Mage::register('current_product', $item);
                    $rexProduct['stockalert'] = Mage::helper('amxnotif')->getStockAlert($item, Mage::getSingleton('customer/session')->isLoggedIn());
                    $rexProduct['stockLabel'] = $this->__('Out of stock');
                    $rexProduct['is_in_stock'] = false;
                }
                $jsonData = ['success' => true, 'data' => $rexProduct, 'all' => $grid];
            }
        } catch (Exception $e) {
            $jsonData['reason'] = $e->getMessage();
        } finally {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
        }
    }

    public function testAction() {
        $sku = $this->getRequest()->getParam('sku', false);
        $levels = [];
        //load all stores so that we can fetch stock updates for each
        if ($sku !== false) {
            $storeCollection = Mage::app()->getStores();
            $retailExpress = Mage::getModel('moogento_retailexpress/connector');

            foreach ($storeCollection as $s => $st) {
                $levels[$st->getCode()] = $retailExpress->ProductsGetDetailsStockPricingByChannel
                        ((int) $st->getChannelId(), (int) $sku, null, null, true);
            }
        }
        echo '<pre>';
        print_r($levels);
        echo '</pre>';
        exit;
    }

    public function syncBulkLevelsAction() {
        $params = $this->getRequest()->getParams();
        $jsonData = ['success' => false, 'reason' => 'No data found'];
        try {
            if (isset($params['ids'])) {
                $this->idArr = $params['ids'];
                $grid = $this->gridAction();
                $stockLevels = [];
                foreach ($grid as $k => $level) {
                    $level['stockQty'] = $level['qty'];
                    $level['is_in_stock'] = true;
                    $level['stockLabel'] = $this->__('In Stock');
                    if ((int) $level['qty'] < 1) {
                        $item = Mage::getModel('catalog/product')->load($level['product_id']);
                        Mage::register('currentUrl', Mage::helper('core/http')->getHttpReferer());
                        Mage::register('current_product', $item);
                        $level['stockalert'] = Mage::helper('amxnotif')->getStockAlert($item, Mage::getSingleton('customer/session')->isLoggedIn());
                        $level['stockLabel'] = $this->__('Out of stock');
                        $level['is_in_stock'] = false;
                        if (isset($level['defaultChannel'])) {
                            unset($level['defaultChannel']);
                            $level['defaultChannel'] = $level;
                        }
                        Mage::unregister('currentUrl');
                        Mage::unregister('current_product');
                    }
                    if (isset($level['defaultChannel'])) {
                        unset($level['defaultChannel']);
                        $stockLevels['hq'][$level['product_id']] = $level;
                    }
                    unset($level['defaultChannel']);
                    $stockLevels['all'][$level['product_id']][] = $level;
                }
                $jsonData = ['success' => true, 'data' => $stockLevels];
            }
        } catch (Exception $e) {
            $jsonData['reason'] = $e->getMessage();
        } finally {
            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
        }
    }

    public function bulkAction() {
        $retailExpress = Mage::getModel('Moogento_RetailExpress_Model_Connector');
        $product = $retailExpress->ProductsGetDetailsStockPricingByChannel(3, 128508, null, null, true);
        var_dump($product);
        exit;
    }

    public function newsletterAction() {
        $jsonData = ['success' => false, 'reason' => 'Nothing to do'];
        $params = $this->getRequest()->getParams();

        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $code = $params['newsletter'] === 'Y' ? Mage::getStoreConfig('nwh_retailexpress/newsletter/code') : '';
        if ($code !== '') {
            // $quote = Mage::getSingleton('checkout/session')->getQuote();
            $email = $quote->getCustomerEmail();

            if (!empty($email)) {
                $subscriber = Mage::getModel('newsletter/subscriber')->getCollection()
                        ->addFieldToFilter('subscriber_email', array('eq' => $email));
                if ($subscriber->count()) {
                    $jsonData = ['success' => true, 'reason' => 'exists'];
                }
            }
        }
        
        //3#ch@*v*15
        if ($jsonData['reason'] !== 'exists' && Mage::getSingleton('core/session')->getSuccessNewsletter() !== 'success') {
          /**  $sCode = $quote->getCouponCode();
            if (!empty($code) && $sCode !== null) {
                $existingCode = explode(',', rtrim($sCode, ','));
                $codes = [$code];
                foreach ($existingCode as $c) {
                    if (strtolower($c) !== strtolower(Mage::getStoreConfig('nwh_retailexpress/newsletter/code'))) {
                        $codes[] = $c;
                    }
                }

                if (count($codes) > 1) {
                    $code = implode(',', $codes);
                } elseif (count($codes) === 0) {
                    $code = '';
                }
            } **/
            if ($quote->setCouponCode2($code)->collectTotals()->save()) {
                $jsonData = ['success' => true, 'reason' => 'done'];
            }
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
    }

    public function checkemailAction() {
        $jsonData = ['success' => false, 'reason' => 'Nothing to do'];
        $email = $this->getRequest()->getParam('email', false);
        if ($email !== false) {
            $subscriber = Mage::getModel('newsletter/subscriber')->getCollection()
                    ->addFieldToFilter('subscriber_email', array('eq' => $email));
            if ($subscriber->count()) {
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode2('')->collectTotals()->save();
                $jsonData = ['success' => true, 'reason' => 'exists'];
            }
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonData));
    }

    public function cancelOrderAction() {
        $params = $this->getRequest()->getParams();
        $helper = Mage::helper('nwh_retailexpress');
        $url = NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/orderCancel';
        //$url ='http://rex.local/api/addons_retailExpress/orderCancel';
        $paymentUrl = NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/payOrder';
        /// $paymentUrl = 'http://rex.local/api/addons_retailExpress/payOrder';
        if (isset($params['orderid'])) {
            $order = Mage::getModel('sales/order')->load((int) $params['orderid']);
            $rexPaymentId = Mage::helper('moogento_retailexpress')->getRetailPaymentMethod($order);
            $totals = $order->getGrandTotal() * -1;
        } elseif ($params['invoiceid']) {
            $invoice = Mage::getModel('sales/order_invoice')->load((int) $params['invoiceid']);
            $order = $invoice->getOrder();
            $rexPaymentId = Mage::helper('moogento_retailexpress')->getRetailPaymentMethod($order);
            $totals = $invoice->getGrandTotal() * -1;
        } else {
            die('You are kidding me ... Try to be serous');
        }
        //16-00000171
        //?orderId=NW100071528&rexPaymentId=9&grandTotal=-51.11
        if ($order->getId() && !empty($order->getRetailExpressId())) {
            $reverseOrder = $helper->getCurlJson($paymentUrl, ['orderId' => $order->getRetailExpressId(), 'rexPaymentId' => $rexPaymentId, 'grandTotal' => $totals], 'GET');
            if ($reverseOrder['success'] === true && $reverseOrder['reason'] === 'Success') {
                return $helper->getCurlJson($url, ['orderId' => $order->getRetailExpressId()], 'GET');
            }
        }
    }
public function topAction() {
        $sql = "SELECT product_id, SUM(qty_ordered) as toppies
                FROM `sales_flat_order` AS so
                INNER JOIN `sales_flat_order_item` AS si ON si.order_id=so.entity_id
                INNER JOIN catalog_product_entity e ON product_id = e.entity_id
                AND (so.state != 'canceled' )
                WHERE name NOT LIKE 'FREE%'
                group by product_id";
        $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('top_sellers')->getFirstItem();
        $attributeId = (int) $attributeInfo->getAttributeId();
        //hopefully it should be 865
        $dbWriter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $results = Mage::getSingleton('core/resource')->getConnection('core_read')
                ->query($sql);
        $dbWriter->query('DELETE FROM catalog_product_entity_varchar WHERE attribute_id=' . (int) $attributeId);
        $insert = " INSERT INTO catalog_product_entity_varchar  "
                . " (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) "
                . " VALUES( NULL, 4, ?, 1,?, ?)";

        foreach ($results as $k => $toppie) {
            $dbWriter->query($insert, [ $attributeId, $toppie['product_id'], (int) $toppie['toppies']]);
        }
    }
}
