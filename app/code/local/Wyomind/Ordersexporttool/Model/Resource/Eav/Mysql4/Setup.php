<?php

class Wyomind_Ordersexporttool_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup {

    public function getDefaultEntities() {

        return array(
           
            'catalog_product' => array(
                'entity_model' => 'catalog/product',
                'attribute_model' => 'catalog/resource_eav_attribute',
                'table' => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'attributes' => array(
                    'export_to' => array(
                        'group' => "Order export",
                        'label' => 'Export by default to',
                        'type' => 'int',
                        'input' => 'select',
                        'default' => '1',
                        'note' => 'Export by default this product with the above export profile',
                        'backend' => '',
                        'frontend' => '',
                        'source' => 'ordersexporttool/attribute_source_export',
                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'visible_in_advanced_search' => false,
                        'unique' => false
                    )
                )
            )
        );
    }

}
