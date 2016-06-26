<?php
$installer = $this;
$installer->startSetup();
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'retail_express_id', array(
    'label'     => 'Retail Express ID',
    'type'      => 'int',
    'input'     => 'text',
    'position'  => 1000,
    'visible'   => true,
    'required'  => false
));

$attribute = Mage::getSingleton('eav/config')
                 ->getAttribute('customer', 'retail_express_id');
$attribute->setData('used_in_forms', array(
    'adminhtml_customer'
));
$attribute->save();

$installer->endSetup();