<?php


class Moogento_RetailExpress_Model_Adminhtml_System_Config_Source_Payment
{
    public function toOptionArray()
    {
        $list = array(
            array('value' => '', 'label'=>''),
        );

        $collection = Mage::getResourceModel('moogento_retailexpress/paymentmethod_collection');
        $collection->addFieldToFilter('status', 1);
        $collection->getSelect()->order('name');

        foreach ($collection as $method) {
            $list[] = array(
                'value' => $method->getRetailExpressId(),
                'label' => $method->getName(),
            );
        }

        return $list;
    }
}