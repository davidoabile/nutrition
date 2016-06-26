<?php

namespace Shipping;

class Shipping {

    /**
     * Fetch data from remote server using curl
     * @param string $url remote url
     * @param array $options params to pass to the remote server
     * @param string $method Request method POST, DELETE,GET,PUT
     * @param array $headers Extra headers to add to curl
     * @return array
     */
    public function getCurlJson($url, $options = [], $method = 'GET', $headers = []) {
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

    public function testQr() {
        include $this->basePath . '/../Qrcodes/phpqrcode.php';
        $filename = $this->savePath . '/test|5|2.png';
        \QRcode::png('how are your kids?', $filename, 'H', 4, 2);
        echo '<img src="/var/' . basename($filename) . '" /><hr/>';
    }

    public function testBar() {
        include $this->basePath . '/../Barcode/Barcode.php';
        $filename = $this->savePath . '/auth_barcode.png';
        (new \Barcode())->generate(['text' => true, 'filename' => $filename, 'size' => 40]);
        echo '<img src="/var/' . basename($filename) . '" /><hr/>';
    }

    /**
     * Dynamically load classed
     * @param type $name
     * @param type $arguments
     * @return boolean|\Shipping\name
     */
    public function __call($name, $arguments) {
        $name = ucfirst(str_replace('get', '', $name));
        $cls = __NAMESPACE__ . '\\' . $name;
        if (class_exists($cls)) {
            return new $cls($this, $arguments);
        }
        return false;
    }

}
