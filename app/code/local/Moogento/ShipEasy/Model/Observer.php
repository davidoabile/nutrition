<?php

class Moogento_Shipeasy_Model_Observer
{
    public function catalog_product_save_after($observer)
    {

        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_custom_product_attribute_show')
            || Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_custom_product_attribute2_show')) {
            $product = $observer->getEvent()->getProduct();
            $productAttribute = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside');

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');
            $orderItemTable = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
            if ($product->dataHasChangedFor($productAttribute)) {
                $sql = <<<HEREDOC
UPDATE {$orderGridTable} SET szy_custom_product_attribute = NULL where entity_id in (SELECT order_id FROM {$orderItemTable} where product_id = {$product->getId()});
HEREDOC;
                $write->query($sql);
            }
            $productAttribute = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside');
            if ($product->dataHasChangedFor($productAttribute)) {
                $sql = <<<HEREDOC
UPDATE {$orderGridTable} SET szy_custom_product_attribute2 = NULL where entity_id in (SELECT order_id FROM {$orderItemTable} where product_id = {$product->getId()});
HEREDOC;
                $write->query($sql);
            }
        }


    }
} 