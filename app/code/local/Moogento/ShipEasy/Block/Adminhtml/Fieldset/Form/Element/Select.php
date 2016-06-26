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
* File        Select.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Fieldset_Form_Element_Select
    extends Varien_Data_Form_Element_Select
{
    protected function _prepareOptions()
    {
        $values = $this->getValues();
        if (empty($values)) {
            $options = $this->getOptions();
            if (is_array($options)) {
                $values = array();
                foreach ($options as  $value => $label) {
                    if (strpos($value, '#') !== false) {
                        list($title, $color) = explode('|', $value);
                        $values[] = array('value' => $value, 'label' => $label, 'style' => "background-color: {$color}");
                    } else {
                        $values[] = array('value' => $value, 'label' => $label);
                    }
                }
            } elseif (is_string($options)) {
                $values = array( array('value' => $options, 'label' => $options) );
            }
            $this->setValues($values);
        }
    }

}
