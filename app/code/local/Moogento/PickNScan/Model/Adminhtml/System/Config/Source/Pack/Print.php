<?php


class Moogento_PickNScan_Model_Adminhtml_System_Config_Source_Pack_Print
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('moogento_pickscan')->__('No Print')
            ),
            array(
                'value' => 'email',
                'label' => Mage::helper('moogento_pickscan')->__('Send as E-Mail attachment')
            ),
            array(
                'value' => 'ftp',
                'label' => Mage::helper('moogento_pickscan')->__('Upload to FTP')
            ),
        );

        return $options;
    }
} 