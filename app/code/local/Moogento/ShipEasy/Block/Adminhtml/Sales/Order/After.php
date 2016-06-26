<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        After.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_After extends Mage_Adminhtml_Block_Template
{
    protected function _toHtml()
    {
        if (Mage::getStoreConfigFlag('moogento_shipeasy/weight/enabled')) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    protected function _getMeasureUnit()
    {
        return Mage::getStoreConfig('moogento_shipeasy/weight/measure_unit');
    }

    protected function _getCountryGroups()
    {
        $zones = Mage::helper('moogento_shipeasy/grid')->getCourierRulesZones();

        $data = array();

        $weight = Mage::getResourceModel('moogento_shipeasy/sales_order')->getWeightPerRegionGroup();

        foreach($zones as $zone) {
            if (!count($zone->getCountries())) {
                continue;
            }
            $name = $zone->getName();

            $data[$name] = array(
                'weight' => round(isset($weight[$name]) ? $weight[$name]['weight'] : 0, 2),
                'orders' => isset($weight[$name]) ? $weight[$name]['order_count'] : 0,
            );
        }

        $data['Others'] = array(
            'weight' => round(isset($weight[' ']) ? $weight[' ']['weight'] : 0, 2),
            'orders' => isset($weight[' ']) ? $weight[' ']['order_count'] : 0,
        );

        return $data;
    }

    protected function _getWeightData()
    {
        return Mage::getResourceModel('moogento_shipeasy/sales_order')->getWeightTotal();
    }
}
