<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

class Moogento_CourierRules_Model_Connector_Carrier_Package extends Mage_Core_Model_Abstract
{
    public function getLabel()
    {
        return Mage::helper('moogento_courierrules')->__($this->getData('label'));
    }
}