<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "categorybanner",  array(
'group'         => 'Banner & Link',
    "type"     => "varchar",
    "backend"  => "catalog/category_attribute_backend_image",
    "frontend" => "",
    "label"    => "Category Banner ",
    "input"    => "image",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "Image should be 486 x 196 pixels "

	));

$installer->addAttribute("catalog_category", "promobanner",  array(
'group'         => 'Banner & Link',
    "type"     => "varchar",
    "backend"  => "catalog/category_attribute_backend_image",
    "frontend" => "",
    "label"    => "Promo Banner",
    "input"    => "image",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "Image should be 710 x 343 pixels "

	));

$installer->addAttribute("catalog_category", "youtubelink",  array(
'group'         => 'Banner & Link',
    "type"     => "varchar",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Youtube link",
    "input"    => "text",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "e.g http://www.youtube.com/watch?v=Jbw4QQMr34A"

	));

$installer->addAttribute("catalog_category", "youtubedesc",  array(
    'group'         => 'Banner & Link',
    "type"     => "text",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Youtube Description",
    "input"    => "editor",
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
	 
