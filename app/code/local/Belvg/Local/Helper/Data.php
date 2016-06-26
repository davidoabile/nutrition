<?php
class Belvg_Local_Helper_Data extends Mage_Core_Helper_Data
{
    const CATEGORY_CATEGORY    = 1192;
    const CATEGORY_ACCESSORIES = 1190;
    const ATTRIBUTE_BRAND_ID   = 81;
    const ATTRIBUTE_GOALS_ID   = 190;
    const ATTRIBUTE_CATEGORIES   = 2647;

    protected $_categoryCategory;

    public function getStaticBlockTitle($identifier)
    {
        return Mage::getModel('cms/block')->load($identifier)->getTitle();
    }

    public function getStaticBlockHtml($identifier)
    {
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($identifier)->toHtml();
    }

    public function getCategoryCategory()
    {
        if (!$this->_categoryCategory) {
            $this->_categoryCategory = Mage::getModel('catalog/category')->load(self::CATEGORY_CATEGORY);
        }

        return $this->_categoryCategory;
    }
}