<?php

class NWH_RetailExpress_Model_Cron {

    const CATEGORYNAME = 'clearance';

    protected $syncObjects = [
        // 'customers' => 'http://rex.local/api/addons_retailExpress/customers',
        'stocklevels' => 'http://rex.local/api/addons_retailExpress/stocklevels',
            //  'outlets' => 'http://rex.local/api/addons_retailExpress/outlets',
    ];
    protected $filename = '';
    protected $lastUpdated = ['start' => 0, 'stop' => 0];
    protected $lastupdated = 0;

    public function __construct($options = []) {
        $this->filename = Mage::getBaseDir('var') . DS . 'retailExpressSync.php';
        $lastupdated = $this->lastUpdated;
        if (file_exists($this->filename)) {
            $this->lastUpdated = include $this->filename;
        }
        if (isset($options['reset'])) {
            $this->lastUpdated = $lastupdated;
        }
        $this->lastUpdated['stop'] = time();
        $this->setup();
    }

    public function checkSpecialItems() {
        $category = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name', self::CATEGORYNAME)
                ->getFirstItem();
        $productCollection = $category->getProductCollection()->addAttributeToSelect(array('special_to_date', 'max_sales_items'));

        foreach ($productCollection as $k => $product) {
            $productsCount = 1;
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
                    $productsCount--;
                    if ($salesProduct->getSpecialToDate() !== null && (int) $salesProduct->getMaxSalesItems() <= 0) {
                        $this->disableProductFromCategory($salesProduct);
                    } elseif ($salesProduct->getSpecialToDate() !== null) {
                        $timestamp = Mage::app()->getLocale()->date(null, null, null, true)->get(Zend_Date::TIMESTAMP);
                        $endDate = Mage::app()->getLocale()->date($salesProduct->getSpecialToDate(), null, null, true)->get(Zend_Date::TIMESTAMP);
                        if ($endDate < $timestamp) {
                            $this->disableProductFromCategory($salesProduct);
                        } else {
                            $productsCount++;
                        }
                    } elseif ((float) $salesProduct->getSpecialPrice() > 0) {
                        $productsCount++;
                    }
                }

                if ($product->getSpecialToDate() !== null && $productsCount > 0) {
                    $timestamp = Mage::app()->getLocale()->date(null, null, null, true)->get(Zend_Date::TIMESTAMP);
                    $endDate = Mage::app()->getLocale()->date($product->getSpecialToDate(), null, null, true)->get(Zend_Date::TIMESTAMP);
                    if ($endDate < $timestamp) {
                        Mage::getSingleton('catalog/category_api')->removeProduct($category->getId(), $product->getId());
                    }
                } elseif ($productsCount <= 0) {//remove item from category
                    Mage::getSingleton('catalog/category_api')->removeProduct($category->getId(), $product->getId());
                }
            } else {
                if ($product->getSpecialToDate() !== null && (int) $product->getMaxSalesItems() <= 0) {
                    $this->disableProductFromCategory($product);
                    Mage::getSingleton('catalog/category_api')->removeProduct($category->getId(), $product->getId());
                } elseif ($product->getSpecialToDate() !== null) {
                    $timestamp = Mage::app()->getLocale()->date(null, null, null, true)->get(Zend_Date::TIMESTAMP);
                    $endDate = Mage::app()->getLocale()->date($product->getSpecialToDate(), null, null, true)->get(Zend_Date::TIMESTAMP);
                    if ($endDate < $timestamp) {
                        $this->disableProductFromCategory($product);
                        Mage::getSingleton('catalog/category_api')->removeProduct($category->getId(), $product->getId());
                    }
                }
            }
        }
    }

    protected function setup() {
        $this->syncObjects = [
            'stocklevels' => str_replace([':start', ':stop'], $this->lastUpdated, NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/products?timestart=:start&timestop=:stop'),
                // 'customers' => str_replace([':start', ':stop'],$this->lastUpdated, NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/customers?start=:start&stop=:stop'),
                // 'outlets' => NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/outlets',
        ];
    }

    protected function disableProductFromCategory($product) {
        $date = date("Y-m-d");
        $product->setSpecialPrice(null);
        $product->setSpecialToDate($date);
        $product->setSpecialToDateIsFormated(true);
        $product->save();
    }

    public function sync($options = []) {
        //don't sync in case we are fetching huge data
        if ($this->lastUpdated['syncInProgress'] === true) {
            return false;
        }
        $helper = Mage::helper('nwh_retailexpress');
        $this->lastUpdated['syncInProgress'] = true;
        $this->saveSyncLastUpdated();
        foreach ($this->syncObjects as $k => $v) {
            $result = $helper->getCurlJson($v, $this->lastUpdated);
            if ($result['success'] === true) {
                switch ($k) {
                    case 'stocklevels' :
                        if (isset($options['reset'])) {
                            Mage::getSingleton('core/resource')->getConnection('core_write')
                                    ->query("TRUNCATE nwh_stock_levels");
                        }
                        (new NWH_RetailExpress_Model_Catalog_Products())->process($result['data']);
                        break;
                    case 'customers' :
                        (new NWH_RetailExpress_Model_Customer_Customer())->process($result);
                        break;
                }
            }
        }
        $this->lastUpdated['syncInProgress'] = false;
        $this->lastUpdated['start'] = $this->lastUpdated['stop'] - (2 * 60);
        $this->saveSyncLastUpdated();
    }

    public function resetSync() {
        if ($this->lastUpdated['syncInProgress'] === true) {
            return false;
        }
        $this->lastUpdated['start'] = 0;
        $this->lastUpdated['stop'] = time();
        $this->saveSyncLastUpdated();
        $this->setup();
        $this->sync(['reset' => true]);
    }

    protected function saveSyncLastUpdated() {
        $arrayString = "<?php\n"
                . "return " . var_export($this->lastUpdated, true) . ";\n";
        file_put_contents($this->filename, $arrayString);
    }

    public function topSellers() {
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
        $dbWriter->query('DELETE FROM catalog_product_entity_text WHERE attribute_id=' . (int) $attributeId);
        $insert = " INSERT INTO catalog_product_entity_text  "
                . " (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) "
                . " VALUES( NULL, 4, ?, 0,?, ?)";

        foreach ($results as $k => $toppie) {
            $dbWriter->query($insert, [ $attributeId, $toppie['product_id'], (int) $toppie['toppies']]);
        }
    }

}
