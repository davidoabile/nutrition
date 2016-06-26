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
* File        Colorpicker.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 

class Moogento_ShipEasy_Block_Colorpicker extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:30px;')
            ->setName($element->getName() . '[]');

        $html = '<input id="'.$element->getHtmlId().'" class="color {hash:true}" name="'.$element->getName()
         .'" value="'.$element->getEscapedValue().'" '.$this->serialize($element->getHtmlAttributes()).'/>'."\n";
		$html.= $this->getAfterElementHtml();
		return $html;
    }
}
