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
* File        Notify.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Massaction_Assigntracking
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _getDefaultValue($actionCode)
    {
        $szyDefault = Mage::getSingleton('adminhtml/session')->getSzyDefault();
        if (!is_array($szyDefault) || !isset($szyDefault[$actionCode])) {
            $defaultValue = Mage::getStoreConfig('moogento_shipeasy/notify_defaults/'.$actionCode);
        } else {
            $defaultValue = $szyDefault[$actionCode];
        }

        return $defaultValue;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('custom_text', 'text', array(
            'name'      => 'custom_text',
            'label'     => null,
            'title'     => '',
            'require'   => 'true'
        ));

        $form->addField('customer_yes_no', 'select', array(
            'label'     => Mage::helper('moogento_shipeasy')->__('Contact customer?'),
            'title'     => Mage::helper('moogento_shipeasy')->__('Contact customer?'),
            'name'      => 'customer_yes_no',
            'value'     => $this->_getDefaultValue($this->getActionCode()),
            'class'     => 'assign_tracking;',
            'options'   => array(
                '1' => Mage::helper('moogento_shipeasy')->__('Yes'),
                '0' => Mage::helper('moogento_shipeasy')->__('No'),
            ),
        ));
        
        $form->addField('orders_for_new_shipment', 'hidden', array(
            'name'      => 'orders_for_new_shipment',
            'label'     => null,
            'title'     => '',
        ));
        
        $form->addField('step', 'hidden', array(
            'name'      => 'step',
            'label'     => null,
            'title'     => '',
            'value'     => '1',
        ));
        
        $this->setForm($form);
        return $this;
    }
}
