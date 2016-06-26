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
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Ddmenu_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The main extension settings
     *
     * @var array
     */
    private $_settings  = array();

    /**
     * Load the main extension settings
     */
    public function __construct()
    {
        $store    = Mage::app()->getStore();
        $settings = array(
            'enabled'       => (int) Mage::getStoreConfig('ddmenu/settings/enabled', $store),
            'stretched'     => (int) Mage::getStoreConfig('ddmenu/settings/stretched_width', $store),
            'home'          => (int) Mage::getStoreConfig('ddmenu/settings/home', $store),
            'home_ico'      => (int) Mage::getStoreConfig('ddmenu/settings/home_ico', $store),
            'fly'           => (int) Mage::getStoreConfig('ddmenu/settings/fly', $store),
            'flyBackground' =>       Mage::getStoreConfig('ddmenu/settings/fly_background', $store),
            'flyOpacity'    => (float) str_replace(',', '.', Mage::getStoreConfig('ddmenu/settings/fly_opacity', $store)),
            'overlay'       => (int) Mage::getStoreConfig('ddmenu/settings/overlay_show', $store),
            'overlayColor'  =>       Mage::getStoreConfig('ddmenu/settings/overlay_color', $store),
            'opacity'       => (float) str_replace(',', '.', Mage::getStoreConfig('ddmenu/settings/overlay_opacity', $store)),
            'overlayHeight' => (int) Mage::getStoreConfig('ddmenu/settings/overlay_height', $store),
            'transitionIn'  =>       Mage::getStoreConfig('ddmenu/animate/transition_in', $store),
            'effectIn'      =>       Mage::getStoreConfig('ddmenu/animate/transition_in_easing', $store),
            'speedIn'       => (int) Mage::getStoreConfig('ddmenu/animate/speed_in', $store),
            'transitionOut' =>       Mage::getStoreConfig('ddmenu/animate/transition_out', $store),
            'effectOut'     =>       Mage::getStoreConfig('ddmenu/animate/transition_out_easing', $store),
            'speedOut'      => (int) Mage::getStoreConfig('ddmenu/animate/speed_out', $store),
        );
        if ($settings['transitionIn']=='none') {
            $settings['speedIn']  = 0;
        }

        if ($settings['transitionOut']=='none') {
            $settings['speedOut'] = 0;
        }

        $this->_settings = $settings;
    }

    /**
     * The extension is enabled/disabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('ddmenu/settings/enabled', Mage::app()->getStore());
    }

    /**
     * The extension "BelVG Product Sorting" is enabled/disabled
     *
     * @return boolean
     */
    public function isProductSortingEnabled()
    {
        return Mage::getStoreConfig('productsorting/settings/enabled', Mage::app()->getStore());
    }

    /**
     * Get extension config
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Get json representation of
     *
     * @return string
     */
    public function getJsonSettings()
    {
        return Mage::helper('core')->jsonEncode($this->_settings);
    }
}