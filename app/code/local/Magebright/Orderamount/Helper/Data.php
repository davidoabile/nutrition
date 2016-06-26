<?php

class Magebright_Orderamount_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getMinimumPrice() {
        $storeId = Mage::app()->getStore()->getStoreId();
        $minOrderActive = Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId);
          $minAmount = -1;
        if ($minOrderActive) {
            $minOrderMulti = Mage::getStoreConfigFlag('sales/minimum_order/multi_address', $storeId);
            $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
           // $role = strtolower(Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code'));

            $amountData = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
            $amountDatauns = unserialize($amountData);
          
            foreach ($amountDatauns as $values) {
                if ($values['customer_group'] == $roleId) {
                    $minAmount = (float) $values['minimum_amount'];
                }
            }
        }
        return $minAmount;
    }

}
