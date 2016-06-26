<?php

class Moogento_CourierRules_Model_Zone extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('moogento_courierrules/zone');
    }

    /**
     * @param $order Mage_Sales_Model_Order
     * @return bool
     */
    public function validate($order)
    {
        $address = $order->getShippingAddress();
        return $this->validateByData($address->getCountryId(), $address->getPostcode());
    }

    public function validateByData($country, $zip)
    {
        return $this->_validateCountry($country) && $this->_validateZip($zip);
    }
    /**
     * @param string $country
     * @return bool
     */
    protected function _validateCountry($country)
    {
        $countries = $this->getCountries();
        if (!count($countries)) {
            return true;
        }

        return in_array($country, $countries);
    }

    /**
     * @param string $zip
     * @return bool
     */
    protected function _validateZip($zip)
    {
        $codes = array_filter($this->getZipCodes());

       if (!count($codes)) {
            return true;
        }

        foreach ($codes as $pattern) {
            if (strpos($pattern, '*') !== false) {
                $parts = explode('*', $pattern);
                $pattern = $parts[0];
                if (!$pattern) {
                    return true;
                } else if (strpos($zip, $pattern) === 0) {
                    return true;
                }
            } else {
                if (strpos($zip, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
} 