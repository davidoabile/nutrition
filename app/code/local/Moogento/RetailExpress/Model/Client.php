<?php

/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 17.02.15
 * Time: 18:59
 */
class Moogento_RetailExpress_Model_Client extends SoapClient {

    /**
     *
     * @var array $classmap The defined classes
     * @access private
     */
    private static $classmap = array(
//        'ClientHeader' => 'Moogento_RetailExpress_Model_Client_Request_Clientheader',
    );

    /**
     *
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     * @access public
     */
    public function __construct($wsdl, $options = array()) {
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        try {
            parent::__construct($wsdl, $options);
        } catch (SoapFault $e) {
            parent::__construct($wsdl, $options);
        } catch (Exception $e) {
            parent::__construct($wsdl, $options);
        }
    }

    public function __doRequest($request, $location, $action, $version = SOAP_1_1, $one_way = 0) {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        $header = $this->__getLastResponseHeaders();

        if (strstr($header, "binary/x-gzip") !== false) {
            $response = $this->gzdecode($response);
            $response = trim($response);
            return $response;
        } else {
            return $response;
        }
    }

    public function gzdecode($data) {
        return gzinflate(substr($data, 10, -8));
    }

}
