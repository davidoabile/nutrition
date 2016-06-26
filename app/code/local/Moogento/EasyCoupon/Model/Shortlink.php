<?php

class Moogento_EasyCoupon_Model_Shortlink extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_easycoupon/shortlink');
    }

    public function getSkus()
    {
        $skus = $this->getData('skus');
        if ($skus && !is_array($skus)) {
            $skus = @unserialize($skus);
            if (!$skus) {
                $skus = array();
            }
            $this->setData('skus', $skus);
        }

        return $skus;
    }

    public function getSiteUrl()
    {
        $website = $this->getWebsite();
        if (!$website) {
            return Mage::app()->getDefaultStoreView()->getBaseUrl();
        }
        if (strpos($website, 'website_') === 0) {
            $code = str_replace('website_', '', $website);
            return Mage::app()->getWebsite($code)->getDefaultStore()->getBaseUrl();
        }
        $code = str_replace('store_', '', $website);

        return Mage::app()->getStore($code)->getBaseUrl();
    }

    public function getFullUrl()
    {
        $parts = array();
        if ($this->getCoupon() && trim($this->getCoupon())) {
            $coupon = urlencode(trim($this->getCoupon()));
            if ($coupon) {
                $parts[] = 'coupon=' . $coupon;
            }
        }

        if ($this->getTarget()) {
            $parts[] = 'target=' . $this->getTarget();
        }
        $skus = $this->getSkus();
        if (count($skus)) {
            if (count($skus) == 1) {
                $sku = array_shift($skus);
                if (is_array($sku) && isset($sku['sku'])) {
                    $parts[] = 'ezsku=' . urlencode($sku['sku']);
                    if (isset($sku['qty']) && $sku['qty'] > 1) {
                        $parts[] = 'ezqty=' . (int)$sku['qty'];
                    }
                }
            } else {
                foreach ($skus as $index => $sku) {
                    if (is_array($sku) && isset($sku['sku'])) {
                        $parts[] = 'ezsku[' . $index . ']=' . urlencode($sku['sku']);
                        if (isset($sku['qty']) && $sku['qty'] > 1) {
                            $parts[] = 'ezqty[' . $index . ']=' . (int)$sku['qty'];
                        }
                    }
                }
            }
        }

        return $this->getSiteUrl() . '?' . implode('&', $parts);
    }
} 