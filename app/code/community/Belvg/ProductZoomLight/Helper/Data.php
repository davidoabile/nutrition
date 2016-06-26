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
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_ProductZoomLight_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_sizes = array();

    /**
     * Is the extension enabled?
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('productzoomlight/cloudzoom/enabled', Mage::app()->getStore()) || Mage::getStoreConfig('productzoomlight/fancybox/enabled', Mage::app()->getStore());
    }

    public function storeConfig($path)
    {
        return Mage::getStoreConfig('productzoomlight/images/' . $path, Mage::app()->getStore());
    }

    public function getSizes($type = '')
    {
        if (!isset($this->_sizes[$type]) || is_null($this->_sizes[$type])) {
            $this->_sizes[$type] = array(
                'mainWidth'   => (int) $this->storeConfig($type . 'main_width'),
                //'mainHeight'  => (int) $this->storeConfig($type . 'main_height'),
                'thumbWidth'  => (int) $this->storeConfig($type . 'thumbs_width'),
                'thumbHeight' => (int) $this->storeConfig($type . 'thumbs_height'),
            );
        }

        return $this->_sizes[$type];
    }

    /**
     * Create 'Cloud Zoom' settings of displaying pictures
     *
     * @return array
     */
    public function getCloudZoomSettings()
    {
        $modulName    = Mage::app()->getRequest()->getModuleName();
        $store        = Mage::app()->getStore();
        if ($modulName == 'productzoomlight') {
            $pageType = '_quickview';
        } else {
            $pageType = '';
        }

        $settings = array(
            'enabled'      =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/enabled', $store),
            'position'     =>        Mage::getStoreConfig('productzoomlight/cloudzoom/position' . $pageType, $store),
            'zoomWidth'    =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/zoom_width' . $pageType, $store),
            'zoomHeight'   =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/zoom_height' . $pageType, $store),
            'adjustX'      =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/adjust_x' . $pageType, $store),
            'adjustY'      =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/adjust_y' . $pageType, $store),
            'tint'         =>        Mage::getStoreConfig('productzoomlight/cloudzoom/tint', $store),
            'softFocus'    =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/soft_focus', $store),
            'smoothMove'   =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/smooth_move', $store),
            'showTitle'    =>   (int)Mage::getStoreConfig('productzoomlight/cloudzoom/show_title', $store),
            'tintOpacity'  => (float)str_replace(',', '.', Mage::getStoreConfig('productzoomlight/cloudzoom/tint_opacity', $store)),
            'lensOpacity'  => (float)str_replace(',', '.', Mage::getStoreConfig('productzoomlight/cloudzoom/lens_opacity', $store)),
            'titleOpacity' => (float)str_replace(',', '.', Mage::getStoreConfig('productzoomlight/cloudzoom/title_opacity', $store)),
        );

        if (!$settings['position']) {
            $settings['position'] = Mage::getStoreConfig('productzoomlight/cloudzoom' . $pageType . '/position_element', $store);
        }

        $settings = $this->checkDefaultCloudZoomSettings($settings);
        return $settings;
    }

    /**
     * Check default 'Cloud Zoom' settings of displaying pictures
     *
     * @param array 'Cloud Zoom' settings
     * @return array
     */
    private function checkDefaultCloudZoomSettings($settings)
    {
        if (!$settings['zoomWidth']) {
            unset($settings['zoomWidth']);
        }

        if (!$settings['zoomHeight']) {
            unset($settings['zoomHeight']);
        }

        if (!$settings['smoothMove']) {
            unset($settings['smoothMove']);
        }

        if ($settings['position'] == "0") {
            $settings['position'] = Mage::getStoreConfig('productzoomlight/cloudzoom/position_element', $store);
        }

        if ($settings['position'] == "inside") {
            unset($settings['zoomWidth']);
            unset($settings['zoomHeight']);
            unset($settings['adjustX']);
            unset($settings['adjustY']);
        }

        $store    = Mage::app()->getStore();
        $tintShow = (int)Mage::getStoreConfig('productzoomlight/cloudzoom/tint_show', $store);
        if (!$tintShow) {
            unset($settings['tint']);
            unset($settings['tintOpacity']);
        }

        return $settings;
    }

    /**
     * Create 'FancyBox' settings of displaying pictures
     *
     * @return array
     */
    public function getFancyBoxSettings()
    {
        $store    = Mage::app()->getStore();
        $settings = array(
            'enabled'         =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/enabled', $store),
            'padding'         =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/padding', $store),
            'margin'          =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/margin', $store),
            'arrows'          =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/arrows', $store),
            'closeBtn'        =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/close_btn', $store),
            'contentClick'    =>        Mage::getStoreConfig('productzoomlight/fancybox/content_click', $store),
            'mouseWheel'      =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/mouse_wheel', $store),
            'autoPlay'        =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/auto_play', $store),
            'playSpeed'       =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/play_speed', $store),
            'loop'            =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/loop', $store),

            'showEffect'      =>        Mage::getStoreConfig('productzoomlight/fancybox/show_effect', $store),
            'showEasing'      =>        Mage::getStoreConfig('productzoomlight/fancybox/show_easing', $store),
            'showSpeed'       =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/show_speed', $store),
            'navEffect'       =>        Mage::getStoreConfig('productzoomlight/fancybox/nav_effect', $store),
            'navEasing'       =>        Mage::getStoreConfig('productzoomlight/fancybox/nav_easing', $store),
            'navSpeed'        =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/nav_speed', $store),
            'buttonsShow'     =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/buttons_show', $store),
            'buttonsPosition' =>        Mage::getStoreConfig('productzoomlight/fancybox/buttons_position', $store),

            //'openOpacity'     =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/open_opacity', $store),
            //'closeOpacity'    =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/close_opacity', $store),

            'titleShow'       =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/title_show', $store),
            'titlePosition'   =>        Mage::getStoreConfig('productzoomlight/fancybox/title_position', $store),

            'overlayShow'     =>   (int)Mage::getStoreConfig('productzoomlight/fancybox/overlay_show', $store),
            'overlayColor'    =>        Mage::getStoreConfig('productzoomlight/fancybox/overlay_color', $store),
            'overlayOpacity'  => (float)str_replace(',', '.', Mage::getStoreConfig('productzoomlight/fancybox/overlay_opacity', $store)),

            'thumbnailShow'     => (int)Mage::getStoreConfig('productzoomlight/fancybox/thumbnail_show', $store),
            'thumbnailWidth'    => (int)Mage::getStoreConfig('productzoomlight/fancybox/thumbnail_width', $store),
            'thumbnailHeight'   => (int)Mage::getStoreConfig('productzoomlight/fancybox/thumbnail_height', $store),
            'thumbnailPosition' =>      Mage::getStoreConfig('productzoomlight/fancybox/thumbnail_position', $store),
        );

        $settings = $this->checkDefaultFancyBoxSettings($settings);

        return $settings;
    }

    /**
     * Check default 'FancyBox' settings of displaying pictures
     *
     * @param array 'FancyBox' settings
     * @return array
     */
    private function checkDefaultFancyBoxSettings($settings)
    {
        if (!$settings['padding']) {
            unset($settings['padding']);
        }

        if (!$settings['margin']) {
            unset($settings['margin']);
        }

        $settings['openEffect']         = $settings['showEffect'];
        $settings['closeEffect']        = $settings['showEffect'];
        if ($settings['showEffect'] != 'none') {
            $settings['openEasing']     = $settings['showEasing'];
            $settings['closeEasing']    = $settings['showEasing'];
            if ($settings['showSpeed']) {
                $settings['openSpeed']  = $settings['showSpeed'];
                $settings['closeSpeed'] = $settings['showSpeed'];
            }
        }

        unset($settings['showEffect']);
        unset($settings['showEasing']);
        unset($settings['showSpeed']);

        $settings['nextEffect']         = $settings['navEffect'];
        $settings['prevEffect']         = $settings['navEffect'];
        if ($settings['navEffect'] != 'none') {
            $settings['nextEasing']     = $settings['navEasing'];
            $settings['prevEasing']     = $settings['navEasing'];
            if ($settings['navSpeed']) {
                $settings['nextSpeed']  = $settings['navSpeed'];
                $settings['prevSpeed']  = $settings['navSpeed'];
            }
        }

        unset($settings['navEffect']);
        unset($settings['navEasing']);
        unset($settings['navSpeed']);

        if ($settings['contentClick'] == 'close') {
            $settings['closeClick'] = 1;
        } else {
            $settings['nextClick']  = 1;
        }

        unset($settings['contentClick']);

        if (!$settings['autoPlay']) {
            $settings['autoPlay'] = FALSE;
            unset($settings['playSpeed']);
        }

        if ($settings['loop']) {
            unset($settings['loop']);
        }

        if ($settings['buttonsShow']) {
            $settings['helpers']['buttons']['position'] = $settings['buttonsPosition'];
        }

        unset($settings['buttonsShow']);
        unset($settings['buttonsPosition']);

        if ($settings['titleShow']) {
            $settings['helpers']['title']['type'] = $settings['titlePosition'];
        } else {
            $settings['helpers']['title'] = NULL;
        }

        unset($settings['titleShow']);
        unset($settings['titlePosition']);

        if ($settings['overlayShow']) {
            list($r, $g, $b) = sscanf($settings['overlayColor'], "#%02x%02x%02x");
            $settings['helpers']['overlay']['css']['background'] = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $settings['overlayOpacity'] . ')';
        } else {
            $settings['helpers']['overlay'] = NULL;
        }

        unset($settings['overlayShow']);
        unset($settings['overlayOpacity']);
        unset($settings['overlayColor']);

        if ($settings['thumbnailShow']) {
            $settings['helpers']['thumbs']['width']    = $settings['thumbnailWidth'];
            $settings['helpers']['thumbs']['height']   = $settings['thumbnailHeight'];
            $settings['helpers']['thumbs']['position'] = $settings['thumbnailPosition'];
        }

        unset($settings['thumbnailShow']);
        unset($settings['thumbnailWidth']);
        unset($settings['thumbnailHeight']);
        unset($settings['thumbnailPosition']);

        return $settings;
    }
}