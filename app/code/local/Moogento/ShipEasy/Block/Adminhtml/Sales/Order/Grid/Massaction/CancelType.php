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
* File        CancelType.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Massaction_CancelType
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('cancel_type', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Cancel Type'),
            'title'     => Mage::helper('catalogrule')->__('Cancel Type'),
            'name'      => 'cancel_type',
            'options'   => array(
                0 => 'Just Status',
                1 => 'Full Processing'
            ),
        ));

        $this->setForm($form);
        return $this;
    }
}
