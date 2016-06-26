<?php

namespace StarTrack;

/**
 * @method bool getDisplaySoapRequests()
 * @method bool getSslForce() Check if we need to force SSL
 * @method string getBasePath() Get base path for this namespace;
 */
class StarTrack {

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
    private $basePath;
    private $_namespaceSeparator = '\\';
    private $savePath = '';
    protected $env = 'staging';
    protected $displaySoapRequests = false;
    protected $sslForce = true;
    protected $config = [];
    protected $tempFile = 'conv.jpg';
    protected $outputFile = 'output.jpg';

    public function __construct() {
        $this->setup();
    }

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

    public function getCacheDir() {
        return \Mage::getBaseDir('var') . DIRECTORY_SEPARATOR;
    }

    public function getConfig() {
        //cn nwh 10130373
        return $this->config;
    }

    public function testQr() {
        include $this->basePath . '/../Qrcodes/phpqrcode.php';
        $filename = $this->savePath . '/test|5|2.png';
        \QRcode::png('how are your kids?', $filename, 'H', 4, 2);
        echo '<img src="/var/' . basename($filename) . '" /><hr/>';
    }

    public function testBar() {
        include $this->basePath . '/../Barcode/Barcode.php';
        $filename = $this->savePath . '/auth_barcode.png';
        (new \Barcode())->generate(['text' => true, 'filename' => $filename, 'size' => 40]);
        echo '<img src="/var/' . basename($filename) . '" /><hr/>';
    }

    /**
     * Convert a pdf to png so that we can convert it to ZPL format
     * 
     * SELECT DISTINCT created_at,so.customer_id FROM `sales_flat_order` so
INNER JOIN sales_flat_order_address  ON so.entity_id = parent_id 
WHERE created_at > '2016-02-29' AND region_id=492
     * 
     * SELECT DISTINCT COUNT(product_id) AS items, sku,name FROM `sales_flat_order_item` so
INNER JOIN sales_flat_order_address  ON so.order_id= parent_id 
WHERE created_at > '2016-02-29' AND region_id=492 AND sku NOT IN('defence','FreeShippingGift') AND product_type ='simple'
GROUP BY sku
     */
    public function converttoPng($pdf) {
        exec("convert -density 300 -trim " . $pdf .  " -quality 100 " . $this->savePath . '/' . $this->tempFile);
    }

    public function getAuspost($pngFile) {
         $filename = $this->savePath . '/' . $this->outputFile;
        
        $im = new \Imagick($pngFile);
        $im->cropImage(2429, 3508, -1300, -1925);
        $im->resizeImage(700, 1100, \Imagick::COLOR_BLACK, 1);
       // $im->rotateimage(new ImagickPixel('#00000000'), 90);
        $im->setImageFormat('jpg');
        $im->writeImage($filename);
        $image = new \Zebra\Zpl\Image(file_get_contents($filename));
        echo  \Zebra\Zpl\Builder::start()->fo(50, 50)->gf($image)->fs();exit;
    }

    public function getFastway() {
        $this->tempFile = 'fastwayTemp.jpg';
        $this->converttoPng($this->savePath . '/fastway.pdf');
        $filename = $this->savePath . '/' . $this->outputFile;
        
        $im = new \Imagick($this->savePath . '/' . $this->tempFile);
         $im->rotateimage(new \ImagickPixel('#00000000'), 90);
        $im->cropImage(2429, 3508, 20, 20);
         $im->cropImage(2429, 3508, -1250, -1700);
        $im->resizeImage(700, 1000, \Imagick::COLOR_BLACK, 1);
       
        $im->setImageFormat('jpg');
        $im->writeImage($filename);
        $image = new \Zebra\Zpl\Image(file_get_contents($filename));
        echo  \Zebra\Zpl\Builder::start()->fo(50, 50)->gf($image)->fs();exit;
        header('Content-Type: image/jpeg');
        echo $im; exit;
    }
    public function __call($name, $arguments) {
        $method = lcfirst(ltrim($name, 'get'));
        if (property_exists($this, $method)) {
            return $this->{$method};
        }
        return null;
    }

    protected function setup() {
        $this->basePath = dirname(__FILE__);
        $this->savePath = \Mage::getBaseDir('var');
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

}
