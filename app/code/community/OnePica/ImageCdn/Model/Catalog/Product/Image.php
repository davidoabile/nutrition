<?php

/**
 * OnePica_ImageCdn
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_ImageCdn
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Extends various methods to use ImageCDN
 */
class OnePica_ImageCdn_Model_Catalog_Product_Image extends Mage_Catalog_Model_Product_Image {

    /**
     * Sets the images processor to the ImageCDN version of varien_image and calls the parent
     * method to return it.
     *
     * @return OnePica_ImageCdn_Model_Varien_Image
     */
    public function getImageProcessor() {
        if (!$this->_processor) {
            $this->_processor = Mage::getModel('imagecdn/varien_image', $this->getBaseFile());
        }
        return parent::getImageProcessor();
    }

    /**
     * Checks to see if the image has been verified lately by checking in the cache or fails
     * back to the parent method as appropriate.
     *
     * @return bool
     */
    public function isCached() {
        $cds = Mage::Helper('imagecdn')->factory();
        if ($cds->useCdn()) {
            return $cds->fileExists($this->_newFile);
        } else {
            return parent::isCached();
        }
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setBaseFile($file) {

        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
//var_dump($baseDir); exit;
        if ('/no_selection' == $file) {
            $file = null;
        }
        $cds = Mage::Helper('imagecdn')->factory();
        if ($cds->useCdn()) {
            $fileExists = true;
            if ($cds->fileExists($baseDir . $file) !== true) {
                return parent::setBaseFile($file);
            }
        }

        if ($file && $fileExists !== true) {
            if ((!$this->_fileExists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }

        if (!$file) {
            // check if placeholder defined in config
            $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
            $configPlaceholder = '/placeholder/' . $isConfigPlaceholder;
            if ($isConfigPlaceholder && $this->_fileExists($baseDir . $configPlaceholder)) {
                $file = $configPlaceholder;
            } else {
                // replace file with skin or default skin placeholder
                $skinBaseDir = Mage::getDesign()->getSkinBaseDir();
                $skinPlaceholder = "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                $file = $skinPlaceholder;
                if (file_exists($skinBaseDir . $file)) {
                    $baseDir = $skinBaseDir;
                } else {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
                    if (!file_exists($baseDir . $file)) {
                        $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => 'base'));
                    }
                }
            }
            $this->_isBaseFilePlaceholder = true;
        }

        $baseFile = $baseDir . $file;

        if (((!$file) || (!file_exists($baseFile)))) {
            return parent::setBaseFile($file);
        }

        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = array(
            Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(),
            //'media/catalog',
            'cache',
            Mage::app()->getStore()->getId(),
            $path[] = $this->getDestinationSubdir()
        );
        $path2 = array(
            'catalog',
            'cache',
            Mage::app()->getStore()->getId(),
            $path2[] = $this->getDestinationSubdir()
        );

        if ((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
            $path2[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = array(
            ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
            ($this->_keepFrame ? '' : 'no') . 'frame',
            ($this->_keepTransparency ? '' : 'no') . 'transparency',
            ($this->_constrainOnly ? 'do' : 'not') . 'constrainonly',
            $this->_rgbToString($this->_backgroundColor),
            'angle' . $this->_angle,
            'quality' . $this->_quality
        );

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams[] = $this->getWatermarkFile();
            $miscParams[] = $this->getWatermarkImageOpacity();
            $miscParams[] = $this->getWatermarkPosition();
            $miscParams[] = $this->getWatermarkWidth();
            $miscParams[] = $this->getWatermarkHeigth();
        }

        $path[] = sha1(implode('_', $miscParams));
        $path2[] = sha1(implode('_', $miscParams));
        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash
        $cdnFile = implode('/', $path2) . $file;
        //echo $cdnFile; exit;
        if ($cds->fileExists($cdnFile) == false) {
            if(!$this->save($cdnFile, basename($cdnFile))){
                return parent::setBaseFile($file);
            }
        } else {
            return parent::setBaseFile($file);
        }
        return $this;
    }

    /**
     * Provides the URL to the image on the CDN or fails back to the parent method as appropriate.
     *
     * @return string
     */
    public function getUrl() {
        $cds = Mage::Helper('imagecdn')->factory();
        if ($cds->useCdn()) {
            $url = $cds->getUrl($this->_newFile, $this->getBaseFile());
            if ($url) {
                return $url;
            }
        }

        return parent::getUrl();
    }

    /**
     * Clears the images on the CDN and the local cache.
     *
     * @return string
     */
    public function clearCache() {
        parent::clearCache();
        $cds = Mage::Helper('imagecdn')->factory();
        if ($cds->useCdn()) {
            $cds->clearCache();
        }
    }

}
