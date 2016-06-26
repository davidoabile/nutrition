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
* File        Contact.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Timezone extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/timezone.phtml');
    }

    public function getPhoneClass($order)
    {
        $time_start = explode(":", Mage::getStoreConfig('moogento_shipeasy/grid/timezone_time_start'));

        $time_end = explode(":", Mage::getStoreConfig('moogento_shipeasy/grid/timezone_time_end'));
        
        $timezone_offset = (int)$order->getTimezoneOffset();
        $now_time =  Mage::getModel('core/date')->gmtTimestamp() + $timezone_offset;
        
        $now_time_zend = new Zend_Date($now_time, null, $this->getLocale());
        $time_start_zend = new Zend_Date($now_time, null, $this->getLocale());
        $time_end_zend = new Zend_Date($now_time, null, $this->getLocale());

        $time_start_zend->setHour((int)$time_start[0]);
        $time_start_zend->setMinute((int)$time_start[1]);
        $time_start_zend->setSecond(0);

        $time_end_zend->setHour((int)$time_end[0]);
        $time_end_zend->setMinute((int)$time_end[1]);
        $time_end_zend->setSecond(0);

        if(($time_start_zend < $now_time_zend) && ($now_time_zend < $time_end_zend)){
            return "timezone_available";
        } else {
            return "timezone_forbidden";
        }
            
    }
    
    public function getRemoteTime($order)
    {
        $color_array = array(
            array(
                "hour_start" => 6,
                "minute_start" => 1,
                "hour_end" => 9,
                "minute_end" => 0,
                "color" => "#00CCFF"
            ),
            array(
                "hour_start" => 9,
                "minute_start" => 1,
                "hour_end" => 12,
                "minute_end" => 0,
                "color" => "#0099FF"
            ),
            array(
                "hour_start" => 12,
                "minute_start" => 1,
                "hour_end" => 15,
                "minute_end" => 0,
                "color" => "#00FF99"
            ),
            array(
                "hour_start" => 15,
                "minute_start" => 1,
                "hour_end" => 18,
                "minute_end" => 0,
                "color" => "#00CC66"
            ),
            array(
                "hour_start" => 18,
                "minute_start" => 1,
                "hour_end" => 21,
                "minute_end" => 0,
                "color" => "#FF9966"
            )
        );
        $remote_time = Mage::getModel('core/date')->gmtTimestamp()+(int)$order->getTimezoneOffset();
        $div_style = "";
        foreach ($color_array as $color){
            $time_start = new DateTime();
            $time_start->setTimestamp($remote_time);
            $time_start->setTime($color["hour_start"], $color["minute_start"], 0);
            $time_end = new DateTime();
            $time_end->setTimestamp($remote_time);
            $time_end->setTime($color["hour_end"], $color["minute_end"], 59);
            if (($remote_time > $time_start->getTimestamp()) && ($remote_time < $time_end->getTimestamp())){
                $div_style = 'style="background-color:'.$color["color"].';"';
            }
        }       
        if ($div_style == ""){
            $div_style = 'style="background-color:lightgrey;"';
        }
        
        $result = '<div class="timezone_remote_time" title=""><span '.$div_style.'>'.date("g:i A", $remote_time).'</span></div>';

        return $result;
    }    
}
