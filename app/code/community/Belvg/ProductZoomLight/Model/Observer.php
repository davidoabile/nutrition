<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_ProductZoomLight
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_ProductZoomLight_Model_Observer
{
    public function layoutLoadBefore(Varien_Event_Observer $observer)
    {
        if ($this->_checkProductviewgallery()) {
            Mage::getConfig()->setNode('modules/Belvg_ProductZoomLight/active', 0, false);
            Mage::app()->getStore()->setConfig('productzoomlight', NULL);
        }
    }

    public function systemConfigInitTabSectionsBefore(Varien_Event_Observer $observer)
    {
        $section = $observer->getEvent()->getSection();
        if ($section->getName() == 'productzoomlight') {
            if ($this->_checkProductviewgallery()) {
                $section->setNode('groups', NULL);
            }
        }
    }

    protected function _checkProductviewgallery()
    {
        return Mage::helper('core')->isModuleEnabled('Belvg_Productviewgallery');
        /*$config = (array) Mage::getConfig()->getNode('modules/Belvg_Productviewgallery');
        if (isset($config['active']) && $config['active']) {
            return TRUE;
        } else {
            return FALSE;
        }*/
    }
}