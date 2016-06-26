<?php

abstract class ShippingAbstract {

    private $basePath;
    private $savePath = '';
    protected $sslForce = true;
    protected $config = [];
    protected $tempFile = 'conv.jpg';
    protected $outputFile = 'output.jpg';
    protected $options = [];

    /**
     *
     * @var Shipping $shipping shipping Object
     */
    protected $shipping = null;

    public function __construct(Shipping $shipping, $options) {
        $this->shipping = $shipping;
        $this->options = $options;
        $this->setup();
    }

    public function getCacheDir() {
        return \Mage::getBaseDir('var') . DIRECTORY_SEPARATOR;
    }

  

    public function __call($name, $arguments) {
        $method = lcfirst(ltrim($name, 'get'));
        if (property_exists($this, $method)) {
            return $this->{$method};
        }
        return null;
    }

    /**
     * Convert any pdf to png
     * @param string $pdf fullpath to pdf
     */
    public function converttoPng($pdf) {
        exec("convert -density 300 -trim " . $pdf . " -quality 100 " . $this->savePath . '/' . $this->tempFile);
    }

    protected function setup() {
        $this->basePath = dirname(__FILE__);
        $this->savePath = \Mage::getBaseDir('var');
        (new \NWH_RetailExpress_Model_Autoloader(false))
                ->setNamespace('Zebra')
                ->controllerFrontInitBefore(null);

    }

     abstract function getLabel();
     
     abstract function getCost();
}
