<?php

class Moogento_Clean_Model_System_Config_Source_Theme 
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'default', 'label'=>Mage::helper('moogento_clean')->__('Default Magento')),
            array('value'=>'extended', 'label'=>Mage::helper('moogento_clean')->__('Clean')),
        );
    }
} 