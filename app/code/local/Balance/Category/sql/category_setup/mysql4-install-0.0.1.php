<?php
$installer = $this;
$installer->startSetup();
$attribute  = array(
    'type' => 'text',
    'label'=> 'Short Description',
    'input' => 'textarea',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'wysiwyg_enabled' => true,
    'default' => "",
    'group' => "General Information"
);
$installer->addAttribute('catalog_category', 'short_description', $attribute);
$installer->updateAttribute('catalog_category', 'short_description', 'is_wysiwyg_enabled', 1);
$installer->updateAttribute('catalog_category', 'short_description', 'visible_on_front', 1);
$installer->endSetup();
?>