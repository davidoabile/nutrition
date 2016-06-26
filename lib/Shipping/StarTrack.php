<?php

namespace Shipping;

/**
 * @method bool getDisplaySoapRequests()
 * @method bool getSslForce() Check if we need to force SSL
 * @method string getBasePath() Get base path for this namespace;
 */
class StarTrack extends \ShippingAbstract {

    // The directory that will hold WSDL, JSON and properties files
    // For security reasons, must be inaccessible to web users
    const USER_INACCESSIBLE_PATH = '';
    // Version of SSL to be forced
    // Set value to 0 for customer default SSL version (preferred)
    // Set value to 3 if unable to connect to server because defaulting to
    // version of SSL earlier than 3 or version of TLS later than 1.1
    const FORCED_SSL_VERSION = 0;
    // Set value to true for debug display of SOAP request XML prior to SOAP operations
    // Useful if fatal error obviates use of __getLastRequest()
    const DISPLAY_SOAP_REQUEST = false;

    private $_namespace = 'EServices';
    private $_namespaceSeparator = '\\';
    protected $env = 'staging';

    public function load() {
        ///  $eServices = $this->get('LoadData')->load();
        (new \NWH_RetailExpress_Model_Autoloader(false))
                ->setNamespace('Zebra')
                ->controllerFrontInitBefore(null);

        $this->getFastway();
        // var_dump(\Mage::getBaseDir('var'));
        exit;
        // $this->testQr();
        $this->testBar();
    }

    public function get($module) {
        if (file_exists($this->basePath . DIRECTORY_SEPARATOR . $this->_namespace . DIRECTORY_SEPARATOR . $module . '.php')) {
            $class = __NAMESPACE__ . $this->_namespaceSeparator . $this->_namespace . $this->_namespaceSeparator . $module;
            return new $class($this);
        }
        return false;
    }

    public function getConfig() {
        //cn nwh 10130373
        return $this->config;
    }

    protected function setup() {
        parent::setup();
        $this->config = array(
            'username' => 'TAY00002',
            'password' => 'Tay12345',
            'userAccessKey' => '30405060708090',
            'wsdl' => $this->basePath . '/wsdl/eServicesProductionWSDL.xml'
        );
        if ($this->env === 'staging') {
            $this->config['wsdl'] = $this->basePath . '/wsdl/eServicesStagingWSDL.xml';
            //$this->displaySoapRequests = true;
        }
    }

    public function getCost() {
        
    }

    public function getLabel() {
        
    }

}
