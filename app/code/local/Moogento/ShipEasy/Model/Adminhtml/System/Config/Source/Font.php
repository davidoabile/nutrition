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
* File        Font.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Font
{
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label'=>Mage::helper('moogento_shipeasy')->__('Default')),
            array('value' => 'trebuchet MS', 'label'=>Mage::helper('moogento_shipeasy')->__('Trebuchet MS')),
            array('value' => 'Arial', 'label'=>Mage::helper('moogento_shipeasy')->__('Arial')),
            array('value' => 'Helvetica', 'label'=>Mage::helper('moogento_shipeasy')->__('Helvetica')),
            array('value' => 'Times New Roman', 'label'=>Mage::helper('moogento_shipeasy')->__('Times')),
            array('value' => 'Georgia', 'label'=>Mage::helper('moogento_shipeasy')->__('Georgia')),
            array('value' => 'Tahoma', 'label'=>Mage::helper('moogento_shipeasy')->__('Tahoma')),
        );
    }
}
