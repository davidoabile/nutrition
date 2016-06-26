<?php

class Balance_Category_Helper_Data extends Mage_Core_Helper_Abstract {

    function is_child_of_goal_category($category = null) {
        if (empty($category))
            return false;
        $parent = Mage::getModel('catalog/category')->load($category->getParentId());
        if ($parent->getUrlKey() == 'goals')
            return true;
        return false;
    }

    public function isClearance($category) {
        if (empty($category)) {
            return false;
        }
        return $category->getUrlKey() === 'clearance' ? true : false;
    }

}
