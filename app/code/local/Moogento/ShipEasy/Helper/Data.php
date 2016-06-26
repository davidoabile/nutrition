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
* File        Data.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/

if (!function_exists('mb_substr')) {
    function mb_substr($string, $offset, $length, $encoding = null) {
        $arr = preg_split("//u", $string);
        $slice = array_slice($arr, $offset + 1, $length);
        return implode("", $slice);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str) {
        return strtolower($str);
    }
}
if ( !function_exists('mb_detect_encoding') ) {

    function mb_detect_encoding ($string, $enc=null, $ret=null) {

        static $enclist = array(
            'UTF-8', 'ASCII',
            'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
            'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
            'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
            'Windows-1251', 'Windows-1252', 'Windows-1254',
        );

        $result = false;

        foreach ($enclist as $item) {
            $sample = iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                if ($ret === NULL) { $result = $item; } else { $result = true; }
                break;
            }
        }

        return $result;
    }
}

class Moogento_ShipEasy_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getDefaultTrackingLink($trackingNo)
    {
        $baseLink = Mage::getStoreConfig('moogento_shipeasy/grid/szy_tracking_number_base_link');
        return $baseLink . $trackingNo;
    }

    public function getOrderFilterStyle()
    {
        $style = '';
        if (Mage::getStoreConfig('moogento_shipeasy/fonts/font')) {
            $style .= 'font-family:'.Mage::getStoreConfig('moogento_shipeasy/fonts/font').';';
        }

        if (Mage::getStoreConfig('moogento_shipeasy/fonts/size')) {
            $style .= 'font-size:'.Mage::getStoreConfig('moogento_shipeasy/fonts/size').'px;';
        }
        return $style;
    }

    public function getOrderRowStyle($order)
    {
        $style = '';
        $color = Mage::getStoreConfig('moogento_shipeasy/colors/'.$order->getStatus());
        if ($color) {
            $style='background-color: ' . $color . ';';
        }
        
        if (Mage::getStoreConfig('moogento_shipeasy/fonts/font')) {
            $style .= 'font-family:'.Mage::getStoreConfig('moogento_shipeasy/fonts/font').';';
        }

        if (Mage::getStoreConfig('moogento_shipeasy/fonts/size')) {
            $style .= 'font-size:'.Mage::getStoreConfig('moogento_shipeasy/fonts/size').'px;';
        }
        return $style;
    }
    
    public function getOrderRowClassesStyle()
    {
        $css = "";
        $hover_percent = Mage::getStoreConfig('moogento_shipeasy/colors/hover_highlight')/100;

        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();

        foreach($statuses as $status => $label){
            $color = trim(Mage::getStoreConfig('moogento_shipeasy/colors/'.$status));
			if($color == '') $color = '#C80DCF'; // set to pink if there's a missing color.
            $css .= ".".$status."{";
            $css .= "background-color: " . $color . "!important;";
            $css .= "font-family: " . Mage::getStoreConfig('moogento_shipeasy/fonts/font') . ";";
            $css .= "font-size: " . Mage::getStoreConfig('moogento_shipeasy/fonts/size') . "px;";
            $css .= "}";
            $css .= ".".$status.":hover{";
            $hsl_color = Mage::helper('moogento_shipeasy/color')->hexToHsl( $color );
            $hsl_color["L"] = 
                    (($hsl_color["L"] + $hover_percent*$hsl_color["L"])<1) ? 
                    ($hsl_color["L"] + $hover_percent*$hsl_color["L"]) :
                    ($hsl_color["L"] - $hover_percent*$hsl_color["L"]);
            $color_hover = '#'.Mage::helper('moogento_shipeasy/color')->hslToHex( $hsl_color );
            $css .= "background-color: " . $color_hover . "!important;";
            $css .= "}";
        }
        return $css;
    }
}
