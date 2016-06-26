<?php


class Moogento_EasyCoupon_Block_Header_Bar extends Mage_Core_Block_Template
{
    protected $_currentRule = null;

    protected function _getCurrentRule()
    {
        if (is_null($this->_currentRule)) {
            $this->_currentRule = Mage::getModel('salesrule/rule');
            if ($this->_getSession()->getCouponCode()) {
                $coupon = Mage::getModel('salesrule/coupon')->loadByCode($this->_getSession()->getCouponCode());
                if ($coupon->getId()) {
                    $this->_currentRule = $this->_currentRule->load($coupon->getRuleId());
                }
            }
        }

        return $this->_currentRule;
    }

    function trim($string, $method = 'WORDS', $length = 25, $pattern = '...')
    {
        if (!is_numeric($length)) {
            $length = 25;
        }

        if (strlen($string) > $length) {
            switch ($method) {
                case 'CHARS':
                    return substr($string, 0, $length) . $pattern;
                case 'WORDS':
                    if (strstr($string, ' ') == false) {
                        return $this->trim($string, 'CHARS', $length, $pattern);
                    }

                    $count = 0;
                    $truncated = '';
                    $word = explode(" ", $string);

                    foreach ($word AS $single) {
                        if ($count < $length) {
                            if (($count + strlen($single)) <= $length) {
                                $truncated .= $single . ' ';
                                $count = $count + strlen($single);
                                $count++;
                            } else if (($count + strlen($single)) >= $length) {
                                break;
                            }
                        }
                    }

                    return rtrim($truncated) . $pattern;
            }
        }

        return $string;
    }

    protected function _getText()
    {
        $message            = Mage::getStoreConfig('moogento_easycoupon/settings/coupon_bar_text');
        if (trim($this->_getCurrentRule()->getData('easycoupon_bar_message'))) {
            $message = $this->_getCurrentRule()->getData('easycoupon_bar_message');
        }
        $characterLimit = (int)Mage::getStoreConfig('moogento_easycoupon/settings/number_characters');
        $currentRule = $this->_getCurrentRule();
        $message            = str_replace("[coupon code]", $this->_getSession()->getCouponCode(), $message);
        $message            = str_replace("[coupon name]", $currentRule->getName(), $message);
        $message            = str_replace("[coupon description]", $currentRule->getDescription(), $message);

        if (!$message) {
            $message = $this->_getSession()->getCouponStatus();
        }

        if (strlen($message) > $characterLimit) {
            $message = $this->trim($message, 'WORDS', $characterLimit, '...)');
        }

        return $message;
    }

    protected function _canShow()
    {
        return Mage::getStoreConfig('moogento_easycoupon/settings/show_coupon_bar') && $this->_getSession()->getCouponStatus();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _getImage()
    {
        $path = false;
        if ($this->_getCurrentRule()->getData('easycoupon_bar_image')) {
            $path = 'moogento/easycoupon/rules/' . $this->_getCurrentRule()->getData('easycoupon_bar_image');
            if (!file_exists(Mage::getBaseDir('media') . '/' . $path)) {
                $path = false;
            }
        }
        if (!$path && Mage::getStoreConfig('moogento_easycoupon/settings/default_image')) {
            $path = 'moogento/easycoupon/' . Mage::getStoreConfig('moogento_easycoupon/settings/default_image');
            if (!file_exists(Mage::getBaseDir('media') . '/' . $path)) {
                $path = false;
            }
        }
        if (!$path) {
            $path = 'moogento/easycoupon/coupon_banner.png';
        }
		return Mage::getBaseUrl('media') . $path;
    }

    protected function _getBarBackground()
    {
        $bg = Mage::getStoreConfig('moogento_easycoupon/settings/bar_background');
        if ($this->_getCurrentRule()->getData('easycoupon_bar_background')) {
            $bg = $this->_getCurrentRule()->getData('easycoupon_bar_background');
        }

        return '#' . $bg;
    }

    protected function _getBarTextColor()
    {
        $color = Mage::getStoreConfig('moogento_easycoupon/settings/bar_text_color');
        if ($this->_getCurrentRule()->getData('easycoupon_bar_color')) {
            $color = $this->_getCurrentRule()->getData('easycoupon_bar_color');
        }

        return '#' . $color;
    }
} 