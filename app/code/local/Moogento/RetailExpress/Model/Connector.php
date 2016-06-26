<?php

/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 17.02.15
 * Time: 18:59
 */
class Moogento_RetailExpress_Model_Connector extends Mage_Core_Model_Abstract {

    protected $_connectorType = 'soap';

    protected function _initClient() {
        $wsdl = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'retailexpress.wsdl';
        $options = array(
            'exceptions' => true,
            'trace' => true,
            //'proxy_host' => 'localhost',
            //'proxy_port' => 8888,
            'soap_version' => SOAP_1_1,
            'compression' => ( SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP ),
        );

        if (Mage::getStoreConfig('moogento_retailexpress/general/mode') == Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Mode::MODE_LIVE) {
            $connectionData = array(
                'url' => Mage::getStoreConfig('moogento_retailexpress/general/live_url'),
                'client_id' => Mage::getStoreConfig('moogento_retailexpress/general/live_client_id'),
                'user' => Mage::getStoreConfig('moogento_retailexpress/general/live_user'),
                'passwd' => Mage::getStoreConfig('moogento_retailexpress/general/live_passwd'),
            );
        } else {
            $connectionData = array(
                'url' => Mage::getStoreConfig('moogento_retailexpress/general/test_url'),
                'client_id' => Mage::getStoreConfig('moogento_retailexpress/general/test_client_id'),
                'user' => Mage::getStoreConfig('moogento_retailexpress/general/test_user'),
                'passwd' => Mage::getStoreConfig('moogento_retailexpress/general/test_passwd'),
            );
        }

        $this->_client = new Moogento_RetailExpress_Model_Client($wsdl, $options);

        if ($connectionData['url']) {
            $this->_client->__setLocation($connectionData['url']);
        }

        $header = array(
            'ClientID' => $connectionData['client_id'],
            'UserName' => $connectionData['user'],
            'Password' => $connectionData['passwd'],
        );

        $header = new SoapHeader("http://retailexpress.com.au/", 'ClientHeader', $header);
        //$this->_client->addSoapInputHeader($header);
        $this->_client->__setSoapHeaders($header);
    }

    public function getClient() {
        if (is_null($this->_client)) {
            $this->_initClient();
        }

        return $this->_client;
    }

    public function CustomerGetDetails($customerId) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Customergetdetails');
        $request->setCustomerId($customerId);

        $method = 'CustomerGetDetails';
        $response = $this->sendSoapRequest($method, $request, false, false);
    }

    public function OutletsGetByChannel($channelId) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Outletsgetbychannel');
        $request->setChannelId($channelId);

        $method = 'OutletsGetByChannel';
        $outlets = $this->sendSoapRequest($method, $request, false, false);
        return $outlets;
    }

    public function CustomerGetBulkDetails($lastUpdated) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Customergetbulkdetails');
        $request->setLastUpdated($lastUpdated);

        $method = 'CustomerGetBulkDetails';
        $result = $this->sendSoapRequest($method, $request);

        var_dump($result);
    }

    public function ProductsGetDetailsStockPricingByChannel($channelId, $productId, $customerId = null, $priceGroupId = null, $noSave = false ) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_ProductGetDetailsStockPricingByChannel');
        $request->setChannelId($channelId);//
        $request->setProductId($productId);

        $method = 'ProductGetDetailsStockPricingByChannel';
        $stock = $this->sendSoapRequest($method, $request, false, false);

        $xml = simplexml_load_string($stock->ProductGetDetailsStockPricingByChannelResult->any);
        $json_string = json_encode($xml);
        $resultArray = json_decode($json_string, TRUE);

        $product = $resultArray['Product'];
        if($noSave === true ) {
            return $product;
        }
        Mage::helper('moogento_retailexpress/api_product')->addProduct($product);
    }

    public function WebOrderGetBulkFulfillmentByChannel($channelId, $lastUpdate) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Webordergetbulkfulfillmentbychannel');
        $request->setChannelId($channelId);
        $request->setLastUpdated($lastUpdate);

        $method = 'WebOrderGetBulkFulfillmentByChannel';
        $result = $this->sendSoapRequest($method, $request, false, false);

        return $result;
    }

    public function ProductsGetBulkDetailsByChannel($channelId, $lastUpdate, $import = true , $update = false) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Productsgetbulkdetailsbychannel');
        $request->setLastUpdated($lastUpdate);
        $request->setChannelId($channelId);

        $method = 'ProductsGetBulkDetailsByChannel';

        $catalog = $this->sendSoapRequest($method, $request);
        if ($import) {
            $outlets = $catalog['Outlets'];
            $attributes = $catalog['Attributes'];
            $products = isset($catalog['Products']) && isset($catalog['Products']['Product']) ? $catalog['Products']['Product'] : array();
            $disabledProducts = $catalog['DisabledProducts'];

            if (count($attributes)) {
                foreach ($attributes as $code => $attribute) {
                    switch ($code) {
                        case 'ProductTypes':
                            foreach ($attribute['ProductType'] as $type) {
                                Mage::helper('moogento_retailexpress/api_attribute')->addAttributeSet($type);
                            }
                            break;
                        case 'PaymentMethods':
                            foreach ($attribute['PaymentMethod'] as $method) {
                                Mage::helper('moogento_retailexpress/api_payment')->addPayment($method);
                            }
                            break;
                        default:
                            Mage::helper('moogento_retailexpress/api_attribute')->addAttribute($attribute);
                            break;
                    }
                }
            }
            if($update === true ) {
                return $catalog;
            }
            if (count($products)) {
                foreach ($products as $product) {
                    Mage::helper('moogento_retailexpress/api_product')->addProduct($product);
                }
            }
        }

        return $catalog;
    }

    public function importPaymentMethods($channelId, $lastUpdate) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Productsgetbulkdetailsbychannel');
        $request->setLastUpdated($lastUpdate);
        $request->setChannelId($channelId);

        $method = 'ProductsGetBulkDetailsByChannel';

        $catalog = $this->sendSoapRequest($method, $request, false, false);
        $attributes = $catalog['Attributes'];

        if (count($attributes)) {
            foreach ($attributes as $code => $attribute) {
                switch ($code) {
                    case 'PaymentMethods':
                        foreach ($attribute['PaymentMethod'] as $method) {
                            Mage::helper('moogento_retailexpress/api_payment')->addPayment($method);
                        }
                        break;
                }
            }
        }
    }

    public function productGetDetailsStockPricingByChannel($productId , $channelId ) {
        $request = Mage::getModel('Moogento_RetailExpress_Model_Client_ProductGetDetailsStockPricingByChannel');
        $request->setChannelId((int) $channelId);
        $request->setProductId((int) $productId);
        $method = 'ProductGetDetailsStockPricingByChannel';
        $response = $this->sendSoapRequest($method, $request);
        
    }

    public function processOrder($order, $reportToSession = false) {
        try {
            $helper = Mage::helper('moogento_retailexpress');
            $request = Mage::getModel('Moogento_RetailExpress_Model_Client_Ordercreatebychannel');
           
            $request->addOrders(array($order));
            $method = 'OrderCreateByChannel';
            $response = $this->sendSoapRequest($method, $request);

            $xml = simplexml_load_string($response->OrderCreateByChannelResult->any);
            $json_string = json_encode($xml);
            $resultArray = json_decode($json_string, TRUE);
            Mage::log($resultArray, null, 'retailexpress.log', true);
            if (isset($resultArray['Error'])) {
                $this->_failOrderExport($order, $resultArray['Error'], $reportToSession);
                return;
            }
            if (isset($resultArray['OrderCreate']) && count($resultArray['OrderCreate'])) {
                $order = Mage::getModel('sales/order')->load($resultArray['OrderCreate']['Order']['ExternalOrderId']);
                if ($order->getId()) {
                    $retailId = false;
                    if (isset($resultArray['OrderCreate']['Order']['OrderId'])) {
                        $retailId = $resultArray['OrderCreate']['Order']['OrderId'];
                    }
                    if ($retailId) {
                        $order->setRetailExpressId($resultArray['OrderCreate']['Order']['OrderId']);
                        $message = $helper->__('Order %s exported to RetailExpress with ID %s. ', $order->getIncrementId(), $order->getRetailExpressId());
                    } else {
                        $message = $helper->__('Order %s got no RetailExpress ID. ', $order->getIncrementId());
                    }
                    $failMessage = '';
                    if (isset($resultArray['OrderCreate']['Customer']['Result']) && $resultArray['OrderCreate']['Customer']['Result'] == 'Fail') {
                        $failMessage .= 'Failed to export customer. ';
                    }
                    if (isset($resultArray['OrderCreate']['OrderPayments']['OrderPayment']['Result']) && $resultArray['OrderCreate']['OrderPayments']['OrderPayment']['Result'] == 'Fail') {
                        $failMessage .= 'Failed to export payment. ';
                    }
                    if (!$retailId || $failMessage) {
                        $this->_failOrderExport($order, $message . ' ' . $failMessage, $reportToSession);
                    } else {
                        $helper->logRequest($order->getId(), $this->getClient()->__getLastRequest());
                        $helper->logResponse($order->getId(), $this->getClient()->__getLastResponse());

                        $order->setRetailExpressStatus(Moogento_RetailExpress_Model_Retailexpress_Status::SUCCESS);
                        $order->setRetailExpressMessage('');

                        $order->addStatusHistoryComment($message, false);
                        if ($order->getCustomerId()) {
                            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                            $customer->setRetailExpressId(@$resultArray['OrderCreate']['Customer']['CustomerId']);
                            $customer->save();
                        }
                        $order->save();
                        if ($reportToSession) {
                            Mage::getSingleton('adminhtml/session')->addSuccess($message);
                        }
                    }
                }
            } else {
                if ($reportToSession) {
                    Mage::getSingleton('adminhtml/session')->addError($helper->__('Failed to export order %s: No response from RetailExpress', $order->getIncrementId()));
                }
                $this->_failOrderExport($order, 'Failed to export order: No response from RetailExpress', $reportToSession);
            }
        } catch (Exception $ex) {
            $this->_failOrderExport($order, $ex->getMessage(), $reportToSession);
        }
    }

    protected function _failOrderExport($order, $message, $reportToSession = false) {
        $helper = Mage::helper('moogento_retailexpress');
        $helper->logRequest($order->getId(), $this->getClient()->__getLastRequest());
        $helper->logResponse($order->getId(), $this->getClient()->__getLastResponse());

        $order->setRetailAttemplts($order->getRetailAttemplts() + 1);
        if ($order->getRetailAttemplts() > Mage::getStoreConfig('moogento_retailexpress/general/retry')) {
            $order->setRetailExpressStatus(Moogento_RetailExpress_Model_Retailexpress_Status::ERROR);
        } else {
            $order->setRetailExpressStatus(Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY);
        }
        $order->setRetailExpressMessage($message);
        $order->addStatusHistoryComment($message, false);
        $order->save();
        if ($reportToSession) {
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
    }

    public function sendSoapRequest($function, $request, $showRequest = false, $showResponse = false) {
        try {
            $client = $this->getClient();
            $response = $client->__soapCall($function, array($request));
        } catch (Exception $e) {
            Mage::log($this->getClient()->__getLastRequest(), null, 'retailexpress.log', true);
            Mage::log($this->getClient()->__getLastResponse(), null, 'retailexpress.log', true);
            if ($e->getMessage() == 'Wrong Version') {
                $response = $this->getClient()->__getLastResponse();
                $response = trim($response);
                if (strpos($response, '<html>') !== false) {
                    throw $e;
                } else {
                    $xml = simplexml_load_string($response);
                    $json_string = json_encode($xml);
                    $result_array = json_decode($json_string, true);

                    return $result_array;
                }
            } else {
                throw $e;
            }
        }

        if ($showRequest || $showResponse) {
            echo '<pre>';
            if ($showRequest) {
                echo htmlentities($client->__getLastRequest());
            }

            if ($showResponse) {
                echo htmlentities($client->__getLastResponse());
            }
            echo '</pre>';
            die();
        }

        if ($response instanceof SoapFault) {
            throw new Exception('SoapFault: ' . $response->getMessage() . '<br>' . $response->getCode());
        }

        return $response;
    }

}
