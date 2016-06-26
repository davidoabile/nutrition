<?php

$installer = $this;

$installer->startSetup();


$block = Mage::getModel('cms/block');
$stores = array(0);

$dataBlock = array(
    'title' => 'Easy Coupon Catalog Right Banner',
    'identifier' => 'moogento_easycoupon_catalog_right_banner',
    'stores' => $stores,
    'is_active' => 1,
    'content'	=> <<<EOB
		<div class="block block-right-banner"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="" /></div>
EOB
);

$page = Mage::getModel('cms/page')->load('coupons', 'identifier');

if ($page->getId()) {
    $page->delete();
}
$page = Mage::getModel('cms/page');

$dataPage = array(
    'title'				=> 'Coupons',
    'identifier' 		=> 'coupons',
    'stores'			=> $stores,
    'content_heading'	=> '',
    'content'			=> <<<EOB
	<h1>Coupons</h1>
	<p>Got a coupon ? Enter it here, then when you go to checkout it will be applied to your order automatically.</p>
	<p>{{block type="core/template" template="moogento/easycoupon/easycoupon.phtml"}}</p>
EOB
,
    'root_template'		=> 'one_column',
);
$page->setData($dataPage)->save();

$installer->endSetup(); 