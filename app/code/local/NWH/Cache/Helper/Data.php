<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_Cache_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $db = null;
    const PAGE_TTL = 18000;

    public function connect() {
        $this->db = new Redis();
        $this->db->connect('127.0.0.1', 6379);
        $this->db->select(1);
        return $this;
    }

    /**
     * Fetch data from redis and decompress if needed
     * @param string $key key to fetch
     * @return string
     */
    public function get($key) {
        $content = $this->db->get($key);
        if (strlen($content) > 70) {// copressing 200kb will result in a small data from the server
            $content = gzuncompress($content);
        }
        return $content;
    }

    /**
     * Set data to redis. Compress it if it is more that 200 bytes
     * @param string $key key for the content
     * @param string $value
     * @param number $ttl in seconds
     */
    public function set($key, $value, $ttl = 0) {
        if (strlen($value) > 300) {
            $this->db->set($key, gzcompress($value, 9));
        } else {
            $this->db->set($key, $value);
        }
        if ($ttl > 0) {
            $this->db->setTimeout($key, $ttl);
        }
    }
    
     public function getKey($key ) {
        return md5($key . '_group_' . Mage::getSingleton('customer/session')->getCustomerGroupId());
    }

    /*
     * Allows you to use the redis API as you wish
     */
    public function getResource() {
        return $this->db;
    }
}
