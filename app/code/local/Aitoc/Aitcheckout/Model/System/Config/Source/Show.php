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
class Aitoc_Aitcheckout_Model_System_Config_Source_Show
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CHECKOUT_IN_CART, 'label'=>Mage::helper('aitcheckout')->__('Move Checkout to Cart')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CHECKOUT_OUTSIDE_CART, 'label'=>Mage::helper('aitcheckout')->__('Expand Checkout steps')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CART_IN_CHECKOUT, 'label'=>Mage::helper('aitcheckout')->__('Move Cart to Checkout')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::IS_DISABLED, 'label'=>Mage::helper('aitcheckout')->__('Turn Off the extension'))
        );
    }

}