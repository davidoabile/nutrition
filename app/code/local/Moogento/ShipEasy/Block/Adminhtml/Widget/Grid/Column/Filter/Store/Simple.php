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
* File        Simple.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Store_Simple
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Store
{
    public function getHtml()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */
        $websiteCollection = $storeModel->getWebsiteCollection();

        $allShow = $this->getColumn()->getStoreAll();

        $html  = '<select name="' . $this->_getHtmlName() . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $this->getColumn()->getValue();
        if ($allShow) {
            $html .= '<option value="0"' . ($value == 0 ? ' selected="selected"' : '') . '>' . Mage::helper('adminhtml')->__('All Websites') . '</option>';
        } else {
            $html .= '<option value=""' . (!$value ? ' selected="selected"' : '') . '></option>';
        }

        foreach ($websiteCollection as $website) {
            $value = $this->getValue();
            $html .= '<option value="' . $website->getId() . '"' . ($value == $website->getId() ? ' selected="selected"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;' . $website->getName() . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        if ($this->getValue() == '_deleted_') {
            return array('null' => true);
        }
        else {
            return array('in' => Mage::app()->getWebsite($this->getValue())->getStoreIds());
        }
    }

}
