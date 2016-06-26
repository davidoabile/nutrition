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

$installer->addAttribute(
    'catalog_product',
    'retail_express_id',
    array(
        'backend'           => '',
        'frontend'          => '',
        'class'             => '',
        'default'           => '',
        'label'             => 'Retail Express ID',
        'type'              => 'static',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'is_visible'        => 1,
        'required'          => 0,
        'searchable'        => 1,
        'filterable'        => 0,
        'unique'            => 1,
        'comparable'        => 0,
        'visible_on_front'  => 0,
        'user_defined'      => 0,
        'apply_to'          => 'simple,virtual',
        'group'             => 'Retail Express',
    )
);

$this->endSetup();
