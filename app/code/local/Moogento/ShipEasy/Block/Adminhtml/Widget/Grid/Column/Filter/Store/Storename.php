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

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Store_Storename
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Store
{
    public function getHtml()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');

        $websiteCollection = $storeModel->getWebsiteCollection();
        $groupCollection = $storeModel->getGroupCollection();

        $html  = '<select name="' . $this->escapeHtml($this->_getHtmlName()) . '[value]" '
               . $this->getColumn()->getValidateClass() . '>';
        
        $html .= '<option value=""></option>';
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                if (!$websiteShow) {
                    $html .= '<optgroup label="' . $this->escapeHtml($website->getName()) . '">';
                }
                $html .= '<option value="' . $group->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;'
                      . $this->escapeHtml($group->getName()) . '</option>';
                if (!$websiteShow) {
                    $websiteShow = true;
                    $html .= '</optgroup>';
                }
            }
        }        
        $html .= '</select>';
        
        if($this->getValue('value')){
            $html = str_replace('value="'.$this->getValue('value').'"', 'value="'.$this->getValue('value').'" selected="selected"', $html);
        }
        
        $checked = ($this->getValue('exclude')) ? 'checked="checked"' : '';
        $html.='Excl <input title="Does not contain" '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'[exclude]" id="'.$this->_getHtmlId().'_exclude" value="1" class="input-checkbox"/>';
        return $html;
    }

    public function getCondition()
    {
        $group_id = $this->getValue('value');
        if(is_null($group_id)){
            return;
        } else {
            $values = Mage::app()->getGroup($group_id)->getStoreIds();
            $condition = 'in';
            if ($this->getValue('exclude')) {
                $condition = 'nin';
            }
            return array($condition => $values);
        }

    }
}