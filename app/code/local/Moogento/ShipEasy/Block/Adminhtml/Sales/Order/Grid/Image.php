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
 * File        Image.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Image extends Mage_Adminhtml_Block_Template
{
    protected $_simpleProductTypes
        = array(
            'simple',
            'virtual',
            'downloadable'
        );

    protected $_productCache = array();
    protected $_productImagesCache = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/image.phtml');
    }


    public function getItemsCollection()
    {
        return $this->getOrder()->getAllItems();
    }


    protected function _isBundleProduct($item)
    {
        $result = false;

        if ($item->getProductType() == 'bundle') {
            $result = true;
        }

        return $result;
    }

    protected function _isGroupedProduct($item)
    {
        $result = false;

        if ($item->getProductType() == 'grouped') {
            $result = true;
        }

        return $result;
    }

    protected function _isSimpleWeDisplay($item)
    {
        $result = false;

        if (
            in_array($item->getProductType(), $this->_simpleProductTypes)
            && (
                !$item->getParentItemId()
                || ($item->getParentItem()->getProductType() == 'configurable')
            )
        ) {
            $result = true;
        }

        return $result;
    }

    protected function truncate($str, $pos, $flag)
    {
        if (($flag == 1) && (strlen($str) > ($pos))) {
            return (substr($str, 0, $pos) . '&hellip;');
        }

        return $str;
    }

    protected function _getProduct($id)
    {
        if (!isset($this->_productCache[ $id ])) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->addAttributeToSelect('name')
                       ->addAttributeToSelect('image');
            $collection->addFieldToFilter('entity_id', $id);

            $this->_productCache[ $id ] = $collection->getFirstItem();
        }

        return $this->_productCache[ $id ];
    }

    protected function _getProductImageSize80($product)
    {
        if (!isset($this->_productImagesCache[ $product->getId() ])) {
            $noImage = $this->getSkinUrl('moogento/general/images/default_image.png');
            try {
                $imageSrc = (string) Mage::helper('catalog/image')->init($product, 'image')->resize(80);
            } catch (Exception $e) {
                $imageSrc = $noImage;
            }
            $this->_productImagesCache[ $product->getId() ] = $imageSrc;
        }

        return $this->_productImagesCache[ $product->getId() ];
    }

    protected function _getProductImageSizeFull($product)
    {

        $noImage = $this->getSkinUrl('moogento/general/images/default_image.png');
        try {
            $imageSrc = (string) Mage::helper('catalog/image')->init($product, 'image');
        } catch (Exception $e) {
            $imageSrc = $noImage;
        }



        return $imageSrc;
    }

    protected function _getImages()
    {

        $productImages = array();

        foreach ($this->getItemsCollection() as $_item) {
            if (Mage::getStoreConfig('moogento_shipeasy/grid/product_image_show_product_image_type')) {
                if ($this->_isSimpleWeDisplay($_item)) {
                    $product = $this->_getProduct($_item->getProductId());
                    if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                        $imageSrc                                = $this->_getProductImageSize80($product);
                        $imageSrcFull                            = $this->_getProductImageSizeFull($product);
                        $productImages[ $_item->getProductId() ] = array(
                            'image' => $imageSrc,
                            'image_full' => $imageSrcFull,
                            'name'  => $product->getName(),
                        );
                    } else {
                        $item_parent = Mage::getModel('sales/order_item')->load($_item->getParentItemId());
                        $product     = $this->_getProduct($item_parent->getProductId());
                        if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                            $imageSrc                           = $this->_getProductImageSize80($product);
                            $imageSrcFull                       = $this->_getProductImageSizeFull($product);
                            $productImages[ $product->getId() ] = array(
                                'image' => $imageSrc,
                                'image_full' => $imageSrcFull,
                                'name'  => $product->getName(),
                            );
                        }
                    }
                }
            } else {
                if ($this->_isSimpleWeDisplay($_item)) {
                    if ($_item->getParentItemId()) {
                        $item_parent = Mage::getModel('sales/order_item')->load($_item->getParentItemId());
                        $product     = $this->_getProduct($item_parent->getProductId());
                        if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                            $imageSrc                           = $this->_getProductImageSize80($product);
                            $imageSrcFull                       = $this->_getProductImageSizeFull($product);
                            $productImages[ $product->getId() ] = array(
                                'image' => $imageSrc,
                                'image_full' => $imageSrcFull,
                                'name'  => $product->getName(),
                            );
                        } else {
                            $product = $this->_getProduct($_item->getProductId());
                            if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                                $imageSrc                                = $this->_getProductImageSize80($product);
                                $imageSrcFull                            = $this->_getProductImageSizeFull($product);
                                $productImages[ $_item->getProductId() ] = array(
                                    'image' => $imageSrc,
                                    'image_full' => $imageSrcFull,
                                    'name'  => $product->getName(),
                                );
                            }
                        }
                    } else {
                        $product = $this->_getProduct($_item->getProductId());
                        if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                            $imageSrc                                = $this->_getProductImageSize80($product);
                            $imageSrcFull                            = $this->_getProductImageSizeFull($product);
                            $productImages[ $_item->getProductId() ] = array(
                                'image' => $imageSrc,
                                'image_full' => $imageSrcFull,
                                'name'  => $product->getName(),
                            );
                        }
                    }
                }
            }

            if ($this->_isBundleProduct($_item)) {
                $product = $this->_getProduct($_item->getProductId());
                if ($product->hasImage() && $product->getImage() != 'no_selection') {
                    $imageSrc                           = $this->_getProductImageSize80($product);
                    $imageSrcFull                            = $this->_getProductImageSizeFull($product);
                    $productImages[ $product->getId() ] = array(
                        'image' => $imageSrc,
                        'image_full' => $imageSrcFull,
                        'name'  => $product->getName(),
                    );

                }

                foreach ($_item->getChildrenItems() as $_childItem) {
                    $product = $this->_getProduct($_childItem->getProductId());
                    if ($product && $product->hasImage() && $product->getImage() != 'no_selection') {
                        $imageSrc                           = $this->_getProductImageSize80($product);
                        $imageSrcFull                       = $this->_getProductImageSizeFull($product);
                        $productImages[ $product->getId() ] = array(
                            'image' => $imageSrc,
                            'image_full' => $imageSrcFull,
                            'name'  => $product->getName(),
                        );
                    }
                }
            }
        }

        return $productImages;
    }
}
