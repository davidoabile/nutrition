<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc. 
 */

class Aitoc_Aitcheckoutfields_Model_Rewrite_EavResourceEntityAttribute extends Mage_Eav_Model_Resource_Entity_Attribute 
{
public function isUsedBySuperProducts(Mage_Core_Model_Abstract $object, $attributeSet = null)
    {
        $adapter      = $this->_getReadAdapter();
        $attrTable    = $this->getTable('catalog/product_super_attribute');
        $productTable = $this->getTable('catalog/product');

        $bind = array('attribute_id' => $object->getAttributeId());
        $select = clone $adapter->select();
        $select->reset()
            ->from(array('main_table' => $attrTable), array('psa_count' => 'COUNT(product_super_attribute_id)'))
            ->join(array('entity' => $productTable), 'main_table.product_id = entity.entity_id')
            ->where('main_table.attribute_id = :attribute_id')
            ->group('main_table.attribute_id')
            ->limit(1);

        if ($attributeSet !== null) {
            $bind['attribute_set_id'] = $attributeSet;
            $select->where('entity.attribute_set_id = :attribute_set_id');
        }

        $helper = Mage::getResourceHelper('core');
        $query  = $helper->getQueryUsingAnalyticFunction($select);
        return $adapter->fetchOne($query, $bind);
    }
}