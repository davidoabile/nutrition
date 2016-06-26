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
class Aitoc_Aitcheckout_Model_System_Config_Source_Design
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_DEFAULT,    'label'=>Mage::helper('aitcheckout')->__('Default Design')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT,    'label'=>Mage::helper('aitcheckout')->__('Compact v1 Design')),
            array('value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT_V2, 'label'=>Mage::helper('aitcheckout')->__('Compact v2 Design')),
        );
    }

}