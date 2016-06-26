<?php

namespace Shipping;

/**
 * @method bool getDisplaySoapRequests()
 * @method bool getSslForce() Check if we need to force SSL
 * @method string getBasePath() Get base path for this namespace;
 */
class FastWay extends \ShippingAbstract{


    public function load() {
        ///  $eServices = $this->get('LoadData')->load();
        
        $this->getFastway();
        // var_dump(\Mage::getBaseDir('var'));
        exit;
        // $this->testQr();
        $this->testBar();
    }


    public function getConfig() {
        //cn nwh 10130373
        return $this->config;
    }


    public function getCost() {
        
    }

    public function getLabel() {
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
       return \Zebra\Zpl\Builder::start()->fo(50, 50)->gf($image)->fs();
    }

    protected function getPdf(){
        
    }
}
