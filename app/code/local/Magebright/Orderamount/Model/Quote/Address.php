<?php 
class Magebright_Orderamount_Model_Quote_Address extends Mage_Sales_Model_Quote_Address
{
	public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }
        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != self::TYPE_SHIPPING) {
            return true;
        }
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
        $role = strtolower($role);
        $amountData = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        $amountDatauns = unserialize($amountData);
        foreach ($amountDatauns as $values) {
            if ($values['customer_group'] == $roleId ) {
               $amount = (float)$values['minimum_amount'];
            }
        }
        if ($this->getBaseSubtotalWithDiscount() < $amount) {
            return false;
        }
        return true;
    }
}