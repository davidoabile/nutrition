<?php 

class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Shipbilladdress
{
    public function toOptionArray()
    {
        $addressForm = Mage::getModel('customer/form')
            ->setFormCode('adminhtml_customer_address')
            ->setStore(Mage::app()->getStore()->getId())
            ->setEntity(Mage::getModel('customer/address'));
        $attributes = $addressForm->getAttributes();
        
        $list = array();
        foreach($attributes as $attribute){
            if ($attribute->getAttributeCode() == 'region' || $attribute->getAttributeCode() == 'region_id') {
                $list['region_field'] = array('value' => 'region_field', 'label' => $attribute->getFrontendLabel());
            } else {
               $list[$attribute->getAttributeCode()] = array('value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel());
            }
        }

        return array_values($list);
    }

}
