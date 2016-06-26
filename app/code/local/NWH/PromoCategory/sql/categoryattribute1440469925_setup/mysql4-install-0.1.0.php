<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "sku_promo",  array(
    "type"     => "varchar",
    "backend"  => "",
    "frontend" => "",
    "label"    => "SKU Product promotion",
    "input"    => "text",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => ""

	));
$installer->endSetup();
	 