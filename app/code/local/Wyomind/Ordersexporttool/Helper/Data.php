<?php

class Wyomind_Ordersexporttool_Helper_Data extends Mage_Core_Helper_Data {

    static public function getExportTo($_item) {

        if ($_item->getExportTo())
            return $_item->getExportTo();
        else {
            return Mage::getModel("catalog/product")->load($_item->getProductId())->getData('export_to');
        }
    }

}
