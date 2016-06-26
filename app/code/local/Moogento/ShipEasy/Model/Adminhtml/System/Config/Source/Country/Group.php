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
* File        Group.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Country_Group
{
    public function toOptionArray()
    {
        $options = array();
        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            $zones = Mage::getModel('moogento_courierrules/zone')->getCollection();
            foreach ($zones as $zone) {
                $options[] = array(
                   'value' => $zone->getId(),
                   'label' => $zone->getName()
                );
            }
        }
        return $options;
    }

    public function getOptionsHtml()
    {
        $options = $this->toOptionArray();
        $html = '';
        foreach($options as $option) {
            $html .= '<option value=\''.$option['value'].'\'>'.$option['label'].'</option>';
        }
        return $html;
    }
    
    public function getCountryGroups()
    {
        $result = array();
        $visible_zone = Mage::getStoreConfig('moogento_shipeasy/country_groups/shipping_zone');
        if(!is_null($visible_zone)){
            $zones = Mage::getModel('moogento_courierrules/zone')->getCollection();
            foreach ($zones as $zone){
                $visiblezone = explode ( ',' , $visible_zone );
                if(in_array($zone->getId(), $visiblezone)){
                    $result[$zone->getId()]=$zone->getName();
                }
            }
        }
        return $result;
    }
    
    
}
