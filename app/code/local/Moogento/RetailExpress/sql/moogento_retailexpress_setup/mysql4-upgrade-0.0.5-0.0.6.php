<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        mysql4-upgrade-0.1.3-0.1.4.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/

$this->startSetup();
$installer = $this;

$prefix = Moogento_RetailExpress_Helper_Api_Attribute::ATTRIBUTE_PREFIX;

$installer->addAttribute(
    'catalog_product',
    $prefix . 'length',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Length',
        'type'              => 'decimal',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'breadth',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Breadth',
        'type'              => 'decimal',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'depth',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Depth',
        'type'              => 'decimal',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'custom1',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'User Field 1',
        'type'              => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'custom2',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'User Field 2',
        'type'              => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'custom3',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'User Field 3',
        'type'              => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'custom3',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'User Field 3',
        'type'              => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'shipping_cubic',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Shipping Cubic',
        'type'              => 'decimal',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'rrp',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'RRP',
        'type'              => 'decimal',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$installer->addAttribute(
    'catalog_product',
    $prefix . 'freight',
    array(
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Freight Cost',
        'type'              => 'decimal',
        'input'             => 'price',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 0,
        'filterable'        => 0,
        'unique'            => 0,
        'comparable'        => 0,
        'visible_on_front'  => 1,
        'user_defined'      => 1,
        'used_in_product_listing' => 1,
        'apply_to'          => 'simple,virtual',
    )
);

$this->endSetup();
