<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Ddmenu_Model_Source_Easing_In
{
    /**
     * Animation effects
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'linear',         'label'=>Mage::helper('adminhtml')->__('Linear')),
            array('value' => 'easeInOutExpo',  'label'=>Mage::helper('adminhtml')->__('Inertia')),
            array('value' => 'easeOutBack',    'label'=>Mage::helper('adminhtml')->__('Out Back')),
            array('value' => 'easeOutElastic', 'label'=>Mage::helper('adminhtml')->__('Elastic')),
            array('value' => 'easeOutBounce',  'label'=>Mage::helper('adminhtml')->__('Bounce')),
        );
    }

}
