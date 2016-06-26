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
 * File        Storeview.php
 * @category   Moogento
 * @package    Shipeasy
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Store_Storeview
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Store
{
    public function getHtml()
    {
        $html = parent::getHtml();
        $html = str_replace('szy_store_id', 'szy_store_id[value]', $html);
        if($this->getValue('value')){
            $html = str_replace('value="'.$this->getValue('value').'"', 'value="'.$this->getValue('value').'" selected="selected"', $html);
        }
        $checked = ($this->getValue('exclude')) ? 'checked="checked"' : '';
        $html.='Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
        return $html;
    }

    public function getCondition()
    {
        $value = $this->getValue('value');
        $condition = 'eq';
        if ($this->getValue('exclude')) {
            $condition = 'neq';
        }
        return array($condition=>$value);
    }
}