<?php
class Magebright_Orderamount_Block_Adminhtml_Config_Groupoption
    extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $group = Mage::getModel('customer/group')->getCollection();

        foreach ($group as $eachGroup) {
            $this->addOption($eachGroup->getCustomerGroupId(), $eachGroup->getCustomerGroupCode());
        }
        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}