<?php


class Moogento_RetailExpress_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ATTRIBUTE_PREFIX = 're_';

    public function prepareAttributeCode($name)
    {
        $code = Mage::helper('catalog/product_url')->format($name);
        $code = preg_replace('/[^0-9a-z]/i', '', $code);
        return strtolower(substr(self::ATTRIBUTE_PREFIX . $code, 0, 30));
    }

    public function getPaymentOptions()
    {
        $payments = Mage::helper('payment')->getStoreMethods();
        $options = array();
        foreach ($payments as $payment) {
            $options[] = array(
                'value' => $payment->getCode(),
                'label' => $payment->getTitle(),
            );
        }

        return $options;
    }

    public function getPaymentArray()
    {
        $payments = Mage::helper('payment')->getStoreMethods();
        $options = array();
        foreach ($payments as $payment) {
            $options[$payment->getCode()] = $payment->getTitle();
        }

        return $options;
    }

    public function getRetailPaymentMethod($order)
    {
        $payment = $order->getPayment();
        if ($payment) {
            $retailPayments = Mage::getResourceModel('moogento_retailexpress/paymentmethod_collection');
            $retailPayments->getSelect()->where('magento_payment IS NOT NULL');
            foreach ($retailPayments as $retailPayment) {
                if (in_array($payment->getMethod(), $retailPayment->getMagentoPayment())) {
                    return $retailPayment->getRetailExpressId();
                }
            }
        }

        return Mage::getStoreConfig('moogento_retailexpress/order/payment_method');
    }

    public function getRetailViewUrl($order)
    {
        if (Mage::getStoreConfig('moogento_retailexpress/general/view_url')) {
            return str_replace('#REXIDHERE#', $order->getData('retail_express_id'), Mage::getStoreConfig('moogento_retailexpress/general/view_url'));
        } else {
            if (Mage::getStoreConfig('moogento_retailexpress/general/mode') == Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Mode::MODE_LIVE) {
                $url = Mage::getStoreConfig('moogento_retailexpress/general/live_url');
            } else {
                $url = Mage::getStoreConfig('moogento_retailexpress/general/test_url');
            }

            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                return 'http://' . $host . '/Admin/pos/main.asp?orderid=' . $order->getData('retail_express_id') . '&int=1';
            }
        }

        return '';
    }

    public function getLogUrl($orderId)
    {
        $logFile = $this->_getLogDirectory() . DS . $orderId . '.log';
        if (file_exists($logFile)) {
            return Mage::getStoreConfig('web/unsecure/base_url') . 'var/log/retailexpress/' . $orderId . '.log';
        }
        return false;
    }

    public function logRequest($orderId, $request)
    {
        $text = '============================ Request (' . date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())) . ') ==============================' . "\r\n";
        if ($request) {
            $domXml                     = new DOMDocument('1.0');
            $domXml->preserveWhiteSpace = false;
            $domXml->formatOutput       = true;
            $domXml->loadXML($request);
            $text .= $domXml->saveXML();
        }
        $this->_log($orderId, $text);
    }

    public function logResponse($orderId, $response)
    {
        $text = '============================ Response (' . date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())) . ') ==============================' . "\r\n";
        if ($response) {
            $domXml                     = new DOMDocument('1.0');
            $domXml->preserveWhiteSpace = false;
            $domXml->formatOutput       = true;
            $domXml->loadXML($response);
            $text .= $domXml->saveXML();
        }
        $this->_log($orderId, $text);
    }

    protected function _log($orderId, $text)
    {
        $logFile = $this->_getLogDirectory() . DS . $orderId . '.log';
        file_put_contents($logFile, $text, FILE_APPEND);
    }

    protected function _getLogDirectory()
    {
        $path = Mage::getBaseDir('var') . DS . 'log' . DS . 'retailexpress';
        if (!file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }
} 