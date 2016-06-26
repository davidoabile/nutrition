<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Contact.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Helper_Contact extends Mage_Core_Helper_Abstract
{
    protected $_simpleProductTypes = array(
        'simple',
        'virtual',
        'downloadable'
    );

    protected function _isSimpleItem($item)
    {
        $result = false;

        if (
            in_array($item->getProductType(), $this->_simpleProductTypes) &&
            (
                !$item->getParentItem() ||
                ($item->getParentItem()->getProductType() == 'configurable')
            )
        ) {
            $result = true;
        }

        return $result;
    }

    protected function _isBundleProduct($item)
    {
        $result = false;

        if ($item->getProductType() == 'bundle') {
            $result = true;
        }
        return $result;
    }
    

    protected function _generateReplacements($order)
    {
        $replacements = array();

        /**
         * Website Name & URL
         */
        if ($order->getStoreId()) {
            try {
                $store = Mage::app()->getStore($order->getStoreId());
                $website = $store->getWebsite();
            } catch (Exception $e) {
                $store = false;
            }
            if ($store) {
                $replacements['{website-name}'] = $website->getName();
                $replacements['{website-url}']  = $store->getBaseUrl();
            } else {
                $replacements['{website-name}'] = '';
                $replacements['{website-url}']  = '';
            }
        }

        /**
         * Customer Name
         */
        /*
        $replacements['{customer-name}'] = ($order->getData('shipping_name')) ?
                    $order->getData('shipping_name') :
                    $order->getData('billing_name');*/
        if($order->getShippingAddress() && $order->getShippingAddress()->getName()) 
        {   
			$replacements['{customer-name}'] = $order->getShippingAddress()->getName();
		}
		elseif($order->getBillingAddress() && $order->getBillingAddress()->getName()){   
			$replacements['{customer-name}'] = $order->getBillingAddress()->getName();
		}
        /*
         * Order Increment Id
         */
        $replacements['{order-id}'] = $order->getData('increment_id');

        /**
         * Order Skus & Names
         */
        $skus = array();
        $names = array();
        $skuNames = array();
        foreach($order->getItemsCollection() as $item) {
            if ($this->_isSimpleItem($item) || $this->_isBundleProduct($item)) {
                $skus[] = $item->getSku();
                $names[] = $item->getName();
                $skuNames[] = $item->getSku() . ' - ' . $item->getName();
            }
        }
        if (count($skus)) {
            $replacements['{ordered-skus}'] = implode("\r\n", $skus);
            $replacements['{ordered-product-names}'] = implode("\r\n", $names);
            $replacements['{sku-name}'] = implode("\r\n", $skuNames);
        }



        return $replacements;

    }

    protected function _processVars($text, $order)
    {
        $replacements = $this->_generateReplacements($order);
        foreach($replacements as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }

    protected function _encodeEmailData($data)
    {
        return rawurlencode($data);
    }

    public function processMessage($message, $order)
    {
        return $this->_processVars(trim($message), $order);
    }

    public function processEmailSubject($subject, $order)
    {
        $subject = $this->_processVars($subject, $order);
        return $this->_encodeEmailData(trim($subject));
    }

    public function processEmailBody($body, $order)
    {
        $body = $this->_processVars($body, $order);
        return $this->_encodeEmailData(trim($body));
    }
}
