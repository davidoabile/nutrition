<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 17.02.15
 * Time: 18:59
 */

class Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Mode
{
    const MODE_TEST = 0;
    const MODE_LIVE = 1;
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::MODE_TEST, 'label'=>Mage::helper('moogento_retailexpress')->__('Test')),
            array('value' => self::MODE_LIVE, 'label'=>Mage::helper('moogento_retailexpress')->__('Live')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::MODE_TEST => Mage::helper('moogento_retailexpress')->__('Test'),
            self::MODE_LIVE => Mage::helper('moogento_retailexpress')->__('Live'),
        );
    }
}
