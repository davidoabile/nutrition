<?php
class Magebright_Orderamount_Model_Quote extends Mage_Sales_Model_Quote{
	public function validateMinimumAmount($multishipping = false)
    {
        $storeId = $this->getStoreId();
        $minOrderActive = Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId);
        $minOrderMulti  = Mage::getStoreConfigFlag('sales/minimum_order/multi_address', $storeId);
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
        $role = strtolower($role);
        $amountData = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        $amountDatauns = unserialize($amountData);
        foreach ($amountDatauns as $values) {
                if ($values['customer_group'] == $roleId ) {
                   $minAmount = (float)$values['minimum_amount'];
                }
            }
        if (!$minOrderActive) {
            return true;
        }
        $addresses = $this->getAllAddresses();
        if ($multishipping) {
            if ($minOrderMulti) {
                foreach ($addresses as $address) {
                    foreach ($address->getQuote()->getItemsCollection() as $item) {
                        $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
                        if ($amount < $minAmount) {
                            return false;
                        }
                    }
                }
            } else {
                $baseTotal = 0;
                foreach ($addresses as $address) {
                    $baseTotal += $address->getBaseSubtotalWithDiscount();
                }
                if ($baseTotal < $minAmount) {
                    return false;
                }
            }
        } else {
            foreach ($addresses as $address) {
                if (!$address->validateMinimumAmount()) {
                    return false;
                }
            }
        }
        return true;
    }
}