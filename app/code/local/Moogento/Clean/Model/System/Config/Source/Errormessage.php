<?php

class Moogento_Clean_Model_System_Config_Source_Errormessage 
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'not_slide', 'label'=>Mage::helper('moogento_clean')->__('Don\'t slide')),
            array('value'=>'slide_all_except_error_messages', 'label'=>Mage::helper('moogento_clean')->__('Slide all - except warnings')),
            array('value'=>'slide_all', 'label'=>Mage::helper('moogento_clean')->__('Slide all'))
        );
    }
} 