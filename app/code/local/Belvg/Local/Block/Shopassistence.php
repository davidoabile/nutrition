<?php

class Belvg_Local_Block_Shopassistence extends Mage_Core_Block_Template
{
    const CACHE_TAG = 'shop_assistence';

    public function __construct()
    {
        parent::__construct();
        $this->addData(array(
            'cache_lifetime' => 3600 * 24,
            'cache_tags'     => array(self::CACHE_TAG),
            'cache_key'      => self::CACHE_TAG . '_store' . Mage::app()->getStore()->getId()  . '_group' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
        ));
    }

    protected function _getHelper()
    {
        return Mage::helper('local/shopassistence');
    }

    public function getBrandCollection()
    {
        //$brand  = $this->_loadPost('brand');
        $helper = $this->_getHelper();

        /*$collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToSelect('brand')
            ->groupByAttribute('brand');

        $optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
        $collection->getSelect()
            ->joinLeft(array("optval" => $optionValueTable), "at_brand.value = optval.option_id", array('brand_name' => 'value'))
            ->order('brand_name ASC');

        $html = '<option value="">' . $this->__('Shop by Brand') . '</option>';
        foreach ($collection as $product) {
            if ($product['brand']) {
                $html .= '<option value="' . $product['brand'] . '"' . (($brand == $product['brand']) ? ' selected' : '') . '>' . $product['brand_name'] . '</option>';
            }
        }*/
        $helper->setCurrentAttributeName('');
        $helper->setNextAttributeName('brand');

        $html = $helper->getOptionsHtml();

        return $html;
    }

    public function getGoalCollection()
    {
        //$brand  = $this->_loadPost('brand');
        //$goal   = $this->_loadPost('goal');
        $helper = $this->_getHelper();

        $helper->setCurrentAttributeName('brand');
        $helper->setNextAttributeName('goal');

        $html = $helper->getOptionsHtml();
        //$html = '<option value="">' . $this->__('Shop by Goal') . '</option>';

        return $html;
    }

    public function getIngredientCollection()
    {
        //$goal       = $this->_loadPost('goal');
        //$ingredient = $this->_loadPost('ingredient');
        $helper     = $this->_getHelper();

        $helper->setCurrentAttributeName('goal');
        $helper->setNextAttributeName('ingredient');

        $html = $helper->getOptionsHtml();
        //$html = '<option value="">' . $this->__('Shop by Ingredient') . '</option>';

        return $html;
    }
}