<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Model_System_Config_Source_CheckboxBehavior
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Aitoc_Aitcheckout_Helper_Data::CHECKBOX_AVAILABLE_INSTANTLY,   'label'=>Mage::helper('aitcheckout')->__('Mark the checkbox')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::CHECKBOX_VIEWING_REQUIRED,      'label'=>Mage::helper('aitcheckout')->__('Display a pop-up window')),
        );
    }

}