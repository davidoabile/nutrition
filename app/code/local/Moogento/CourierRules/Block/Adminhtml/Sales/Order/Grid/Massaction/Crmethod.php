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
* File        Notify.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ 


class Moogento_CourierRules_Block_Adminhtml_Sales_Order_Grid_Massaction_Crmethod
    extends Mage_Adminhtml_Block_Widget_Form
{
    /*protected function _getDefaultValue($actionCode)
    {
        $szyDefault = Mage::getSingleton('adminhtml/session')->getSzyDefault();
        $defaultValue = 0;
        if (!is_array($szyDefault) || !isset($szyDefault[$actionCode])) {
            $defaultValue = Mage::getStoreConfig('moogento_courierrules/cr_defaults/'.$actionCode);
        } else {
            $defaultValue = $szyDefault[$actionCode];
        }

        return $defaultValue;
    }*/

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('cr_method', 'select', array(
            'label'     => Mage::helper('moogento_courierrules')->__('Method').":",
            'title'     => Mage::helper('moogento_courierrules')->__('Method').":",
            'name'      => 'cr_method',
            //'value'     => $this->_getDefaultValue($this->getActionCode()),
            'class'     => 'cr_method',
            'options'   => Mage::helper('moogento_courierrules')->getRulesArrayForHTML(),
        ));

        $form->addField('cr_method_custom', 'text', array(
            'name'      => 'cr_method_custom',
            'label'     => null,
            'title'     => '',
            'require'   => 'true',
            'style'     => "display: none;"
        ));
        
        $form->addField('cr_method_change_track', 'hidden', array(
            'name'      => 'cr_method_change_track',
            'require'   => 'true',
        ));
        
        $this->setForm($form);
        return $this;
    }
}
