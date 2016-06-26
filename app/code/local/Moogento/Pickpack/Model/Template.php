<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Template.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

class Moogento_Pickpack_Model_Template
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('pickpack')->__('Default')),
            array('value' => 'tech', 'label' => Mage::helper('pickpack')->__('Tech (Padding, rounded corners, inverted colors)')),
            array('value' => 'mailer', 'label' => Mage::helper('pickpack')->__('Mailer (Padded for window-envelope mailers)')),
            array('value' => 'bringup', 'label' => Mage::helper('pickpack')->__('Bringup (Top address raised up)')),
            array('value' => 1, 'label' => Mage::helper('pickpack')->__('Shifter Theme (to over-print pre-printed forms)')),
        );
    }

}
