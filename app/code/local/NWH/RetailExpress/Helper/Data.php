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
class NWH_RetailExpress_Helper_Data extends Mage_Core_Helper_Abstract {

    const TIMEZONE = 'Australia/Queensland';
    const SYNCINTERVAL = '+5 minutes'; // every 5 minutes
    const FASTWAY_API_URL = 'http://au.api.fastway.org/latest';
    const NWH_API = 'http://api.nutritionwarehouse.com.au/api';

    public function getCurlFastWay($url, $options = []) {

        if (!$response = $this->cache([], $options)) {
            $apiUrl = self::FASTWAY_API_URL . $url . 'api_key=' . Mage::getStoreConfig('nwh_retailexpress/carriers/fastway_api_key');
            //  Initiate curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            $result = curl_exec($ch);
            curl_close($ch);
            if ($response = json_decode($result, true)) {
                if (!isset($result['error'])) {
                    if (count($options) && isset($options['redisKey'])) {
                        $this->cache($response, $options);
                    }
                }
            }
        }
        return $response;
    }

    public function enableDirectSync() {
        return false;
    }

    public function getAusPost($options = []) {
        $result = $this->getCurlJson(
                $options['url'], $options['data'] ? $options['data'] : [], $options['method'] ? $options['method'] : 'GET', isset($options['headers']) ? $options['headers'] : []
        );
        var_dump($result);
        exit;
    }

    public function getCurl($url, $options = []) {
        $results = ['success' => false, 'data' => []];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($options));
        $result = curl_exec($ch);
        curl_close($ch);
        if ($response = json_decode($result, true)) {
            $results = $response;
        }
        return $results;
    }

    public function getCurlJson($url, $options = [], $method = 'POST', $headers = []) {
        $results = ['success' => false, 'data' => []];

        if (count($options) && $method === 'GET') {
            $params = http_build_query($options);
            $url = strpos($url, '?') ? $url . '&' . $params : $url . '?' . $params;
        }
        $ch = curl_init($url);
        if (isset($options['username'])) {
            curl_setopt($ch, CURLOPT_USERPWD, $options['username'] . ":" . $options['password']);
            unset($options['username'], $options['password']);
        }
        try {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($method !== 'GET') {
                $data = json_encode($options);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            if ($headers !== false) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, sizeof($headers) > 0 ? $headers : array('Content-Type: application/json'));
            }
            $result = curl_exec($ch);
            curl_close($ch);
            if ($response = json_decode($result, true)) {
                $results = $response;
            } else {
                $results = ['success' => false, 'reason' => 'no results returned by the api'];
            }
        } catch (Exception $e) {
            $results = ['success' => false, 'reason' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    protected function cache($result = [], $options = []) {
        if (isset($options['redisKey'])) {
            $redisKey = $options['redisKey'];
            $redis = Mage::helper('nwhcache')->connect();
            if ($response = $redis->get($redisKey)) {
                return json_decode($response, true);
            }
            if (count($result) > 0) {
                //default cache to 24 hours
                $redis->set($redisKey, json_encode($result), isset($options['expiry']) ? $options['expiry'] : 8640);
            }
        }

        return false;
    }

    public function needSyncQty($productId, $productSku = 0) {
        if (!Mage::getStoreConfigFlag('moogento_retailexpress/general/enable') || Mage::getStoreConfig('nwh_retailexpress/sync/enabled') === 'N') {
            return array('sku' => false, 'id' => $productId, 'shouldWeUpdate' => 10);
        }
        $sku = (int) $productSku > 0 ? $productSku : $this->getProductSku($productId);
        $stockLevel = null;

        if ($sku !== false) {
            $stockLevel = Mage::getModel('nwh_retailexpress/stocklevels')->getCollection()
                    ->addFieldToFilter('channelid', array('eq' => Mage::getStoreConfig('moogento_retailexpress/general/channel_id')))
                    ->addFieldToFilter('sku', array('eq' => $sku))
                    ->getFirstItem();
            $shouldWeUpdate = -1;
            //get the last update    
            if ($stockLevel->getSku() !== null) {
                $updated = (new DateTime($stockLevel->getLastUpdated(), new DateTimeZone(NWH_RetailExpress_Helper_Data::TIMEZONE)))->modify(Mage::getStoreConfig('nwh_retailexpress/sync/interval'));
                $lastUpdated = $updated->getTimestamp();
                $shouldWeUpdate = $lastUpdated - time();
            }
        }
        return array('stockLevel' => $stockLevel, 'sku' => $sku, 'id' => $productId, 'shouldWeUpdate' => $shouldWeUpdate,);
    }

    public function getProductSku($id) {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        $query = "SELECT `sku` FROM $table WHERE `type_id` = 'simple' AND `entity_id` = {$id}";
        //fetch products
        return $read->fetchRow($query)['sku'];
    }

    public function sync(Mage_Catalog_Model_Product $product, $background = true) {
        $toUpdate = $this->needSyncQty($product->getId(), $product->getSku());
        if (((int) $toUpdate['shouldWeUpdate'] < 1 && $toUpdate['sku'] !== false) || $background === false) {
            //get sync in background
            $onBackground = $background === true ? ' > /dev/null  2>&1 &' : ' -direct Y';
            exec('php ' . Mage::getBaseDir() . '/shell/retailExpress.php -sku ' . $product->getSku() . ' -id ' . $product->getId() . $onBackground);
        }
    }

    /**
     * Sync a product in the background
     * @param int $productId Magento product id
     */
    public function syncById($productId, $sku = 0, $background = true) {
        $toUpdate = $this->needSyncQty($productId, $sku);
        if (((int) $toUpdate['shouldWeUpdate'] < 1 && $toUpdate['sku'] !== false) || $background === false) {
            //get sync in background
            $onBackground = $background === true ? ' > /dev/null  2>&1 &' : ' -direct Y ';
            exec('php ' . Mage::getBaseDir() . '/shell/retailExpress.php -sku ' . $toUpdate['sku'] . ' -id ' . $toUpdate['id'] . $onBackground);
        }
    }

}
