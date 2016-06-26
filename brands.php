<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

Mage::app('admin')->setUseSessionInUrl(false);
$resource = Mage::getSingleton('core/resource');
$readAdapter = $resource->getConnection('core_read');
$writeAdapter = $resource->getConnection('core_write');
$brandOptions = array();

$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('manufacturer')->getFirstItem();
$attributeId = $attributeInfo->getAttributeId();
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
if ($attribute->usesSource()) {
    $brandOptions = $attribute->getSource()->getAllOptions(false);
}

$manufacturerCollectionObj = Mage::getResourceModel('attributeSplash/page_collection')
     //   ->addFieldToSelect(array('option_id','display_name','url_key'))
        ->addFieldToFilter("attribute_code", 'manufacturer');
     //    ->addFieldToFilter("is_enabled", '1');

$manufacturerCollection = $savedOptions = array();
foreach ($manufacturerCollectionObj as $k => $v) {
    $data = $v->getData();
    $manufacturerCollection[$data['option_id']] = $data;
}
//add to the pages model
foreach ($brandOptions AS $option) {
    $savedOptions[$option['value']] = $option;
    if (!isset($manufacturerCollection[$option['value']])) {
        $data = array(
            'option_id' => $option['value'],
            'display_name' => $option['label'],
            'other' => 'a:0:{}',
            'url_key' => str_replace(' ', '-', strtolower(trim($option['label']))),
            'display_mode' => 'PRODUCTS',
            'is_enabled' => '1',
            'include_in_menu' => '1',
            'sort_order' => $option['value'],
        );
        $manufacturerCollection[$option['value']] = array(
            'option_id' => $option['value'],
            'display_name' => $option['label'],
            'sort_order' => $option['value'],
            'is_new' => true,
            'is_enabled' => 1,
        );
        $model = Mage::getModel('attributeSplash/page');
        $model->setData($data);
        $model->save();
    }
    //check if it is disabled and later remove it from the manufacturer's attr
    // if ((int) $manufacturerCollection[$option['value']]['is_enabled'] === 0) {
    //    unset($manufacturerCollection[$option['value']]);
    // }
}
//removed disabled brands or add
//Not using models as this is faster

foreach ($manufacturerCollection as $id => $manufacturer) {
    if ((int) $manufacturer['is_enabled'] === 0) {
        $writeAdapter->query(" DELETE FROM eav_attribute_option WHERE option_id= " . (int) $id);
    } elseif (!isset($savedOptions[$id])) { //this should happen often.. but if you have disabled the brand before and you want to bring it back to life
        $writeAdapter->query("INSERT INTO eav_attribute_option (option_id, attribute_id, sort_order ) VALUES(:option_id, :attibute_id, :sort_order)",
                array('option_id' =>(int) $id, 'attribute_id' => (int) $attributeId, 'sort_order' => (int) $manufacturer['sort_order']));
        $writeAdapter->query("INSERT INTO eav_attribute_option_value(option_id,value)", array('option_id' => (int) $id, 'value' => $manufacturer['display_name']));
    } else {
        $writeAdapter->query("UPDATE eav_attribute_option SET sort_order = " . (int) $manufacturer['sort_order'] . " WHERE option_id=" . (int) $id);
        if (!isset($manufacturer['is_new'])) {// don't update newly created attr
            $writeAdapter->query("UPDATE eav_attribute_option_value SET value=:value WHERE option_id=" . (int) $id, array('value' => $manufacturer['display_name']));
        }
    }
}