<?php
/**
 * Location extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright 2013 Andrew Kett. (http://www.andrewkett.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://andrewkett.github.io/Ak_Locator/
 */

/**
 * @category   Ak
 * @package    Ak_Locator
 * @author     Andrew Kett
 */
class Ak_Locator_Model_Search_Handler_Point_String extends Ak_Locator_Model_Search_Handler_Point_Abstract
{
    const XML_SEARCH_APIKEY_PATH = "locator_settings/google_maps/api_key";
    const XML_SEARCH_SHOULDAPPEND_PATH = "locator_settings/search/append_string_to_search";
    const XML_SEARCH_APPENDTEXT_PATH = "locator_settings/search/append_string";

    const XML_SEARCH_LOG_GEO = "locator_settings/search/log_geocoding";

    const TYPE = 'string';

    const CACHE_ID = 'locator_geo';
    const CACHE_TAG = 'LOCATOR_SEARCH_GEO';

    protected $_cache;
    protected $_isCacheEnabled;


    /**
     * Perform search
     *
     * @param array $params Array of search params
     *
     * @return Ak_Locator_Model_Resource_Location_Collection
     * @throws Exception
     */
    public function search(array $params)
    {
        if (!$this->isValidParams($params)) {
            throw new InvalidArgumentException('A search string must be passed to perform a string search');
        }

        $point = $this->stringToPoint($this->createSearchString($params));

        $collection = $this->pointToLocations($point, @$params['distance']);
        $collection->setSearch($this);

        return $collection;

    }


    protected function createSearchString (array $params)
    {

        $query = $params['s'];

        if (isset($params['a'])) {
            $query .= ' '.$params['a'];
        }

        if (isset($params['country'])) {
            $query .= ' '.$params['country'];
        }

        $appendText = (Mage::getStoreConfig(self::XML_SEARCH_SHOULDAPPEND_PATH))?Mage::getStoreConfig(self::XML_SEARCH_APPENDTEXT_PATH):'';
        $query .= ' '.$appendText;

        return $query;
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    public function isValidParams(array $params)
    {
        if (isset($params['s']) && $params['s'] != '') {
            return true;
        }

        return false;
    }


    /**
     * Geocode a search string into a Lat/Long Point
     *
     * @param $query
     *
     * @return Point
     * @throws Ak_Locator_Model_Exception_Geocode
     * @throws Exception
     */
    protected function stringToPoint($query)
    {
        $cache = $this->getCache();

        $this->log('geocoding '.$query);

        if (!$this->_isCacheEnabled() || !$result = unserialize($cache->load(self::CACHE_TAG.'_'.$query))) {
            $key = Mage::getStoreConfig(self::XML_SEARCH_APIKEY_PATH);

            try {
                $geocoder = new GoogleGeocode($key);
                $result = $geocoder->read($query);

                $this->log('result '.$this->formatLogOutput($result));

                $cache->save(serialize($result), self::CACHE_TAG.'_'.$query, array(self::CACHE_TAG));

            } catch (Exception $e) {

                if (strpos($e->getMessage(), 'ZERO_RESULTS')) {
                    throw new Ak_Locator_Model_Exception_Geocode($e->getMessage());
                }

                throw $e;
            }
        } else {
            $this->log('result from cache '.$this->formatLogOutput($result));
        }

        return $result;
    }

    /**
     * Format result for logging
     *
     * @param Geometry $result
     * @return string
     */
    protected function formatLogOutput(Geometry $result)
    {
        switch (get_class($result)) {
            case "Point":
                return 'lat: '.$result->coords[1].', long:'.$result->coords[0];
            default:
                return 'unknown result format';
        }

    }

    /**
     *
     * @return mixed
     */
    protected function getCache()
    {
        if (!$this->_cache) {
            $this->_cache = Mage::app()->getCache();
        }

        return $this->_cache;
    }


    /**
     * Check cache availability
     *
     * @return bool
     */
    protected function _isCacheEnabled()
    {
        if ($this->_isCacheEnabled === null) {
            $this->_isCacheEnabled = Mage::app()->useCache(self::CACHE_ID);
        }
        return $this->_isCacheEnabled;
    }

    protected function log($message, $level = Zend_Log::DEBUG, $file = 'locator_geocoding.log')
    {
        if (Mage::getStoreConfig(self::XML_SEARCH_LOG_GEO)) {
            Mage::log($message, $level, $file);
        }

    }
}
