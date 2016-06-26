<?php

class Moogento_EasyCoupon_Adminhtml_Easycoupon_AjaxController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function couponAction()
    {
        $searchTerm = $this->getRequest()->getParam('search');

        $collection = Mage::getResourceModel('salesrule/coupon_collection');
        $collection->join(
        array('rule' => 'salesrule/rule'),
            'main_table.rule_id = rule.rule_id',
            array()
        );
        $collection->getSelect()
            ->where('expiration_date IS NULL OR expiration_date < NOW()')
            ->where('code like ?',trim($searchTerm) . '%')
            ->where('rule.from_date IS NULL OR rule.from_date < NOW()')
            ->where('rule.to_date IS NULL OR rule.to_date > NOW()')
            ->where('rule.is_active = 1')
            ->limit(20);

        $data = array();
        foreach ($collection as $coupon) {
            $data[] = array(
                'code' => $coupon->getCode(),
            );
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    public function skuAction()
    {
        $searchTerm = $this->getRequest()->getParam('search');

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToFilter('type_id', array('simple', 'virtual', 'downloadable'));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->getSelect()
            ->where('sku like ?', trim($searchTerm) . '%')
            ->limit(20);
        $collection->addAttributeToSelect('sku');

        $data = array();
        foreach ($collection as $product) {
            $data[] = array('sku' => $product->getSku());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    public function saveShortLinkAction()
    {
        $shortLinkCode = $this->getRequest()->getPost('shortlink');
        $shortLink = Mage::getModel('moogento_easycoupon/shortlink')->load($shortLinkCode, 'shortlink');
        $shortLink->setData('shortlink', $shortLinkCode);
        $website = $this->getRequest()->getPost('website', 'base');
        $coupon = $this->getRequest()->getPost('coupon', '');
        $target = $this->getRequest()->getPost('target', '');
        $skus = $this->getRequest()->getPost('skus', false);
        if ($skus) {
            $skus = Mage::helper('core')->jsonDecode($skus);
        } else {
            $skus = array();
        }
        $shortLink->addData(array(
            'website' => $website,
            'coupon' => $coupon,
            'target' => $target,
            'skus' => $skus,
        ));

        try {
            $shortLink->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => true)));
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => $e->getMessage())));
        }
    }
} 