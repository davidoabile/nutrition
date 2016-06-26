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
* File        Customtext.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Customtext
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getHtml()
    {
        $html = '<div class="field-100"><input type="text" name="'.$this->_getHtmlName().'[value]" id="'.$this->_getHtmlId().'_value" value="'.$this->getEscapedValue('value').'" class="input-text no-changes"/></div>';
        $checked = ($this->getValue('exclude')) ? 'checked="checked"' : '';
        $html = $html . 'Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
        return $html;
    }

    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        return htmlspecialchars($value);
    }

    public function getCondition()
    {
        $value = $this->getValue('value');
        $condition = 'like';
        if ($this->getValue('exclude')) {
            $condition = 'nlike';
        }
        return array($condition=>'%'.$this->_escapeValue($value).'%');
    }

}
