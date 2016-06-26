<?php

class Belvg_Local_Block_Home_Category extends Mage_Catalog_Block_Navigation
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCatsidebarnav()
    {
        if (!$this->hasData('catsidebarnav')) {
            $this->setData('catsidebarnav', Mage::registry('catsidebarnav'));
        }
        return $this->getData('catsidebarnav');

    }
    public function leftSidebarBlock() {
        $block = $this->getParentBlock();
        if($block) {

            if(Mage::helper('catsidebarnav')->displayOnSideBar() == 'left') {
                $sidebarBlock = $this->getLayout()->createBlock('catsidebarnav/sidebar');
                $block->insert($sidebarBlock,'', true, 'cat-sidebar');
            }
        }
    }
    public function rightSidebarBlock() {
        $block = $this->getParentBlock();
        if($block) {
            if(Mage::helper('catsidebarnav')->displayOnSideBar() == 'right') {
                $sidebarBlock = $this->getLayout()->createBlock('catsidebarnav/sidebar');

                $block->insert($sidebarBlock, '', true, 'cat-sidebar');
            }
        }
    }
    /**
     * Retrieve child categories of current category
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCurrentChildCategories()
    {
        if (null === $this->_currentChildCategories) {
            $category = Mage::getModel('catalog/category')->load(Belvg_Local_Helper_Data::ATTRIBUTE_CATEGORIES);
            $this->_currentChildCategories = $category->getChildrenCategories();
        }
        return $this->_currentChildCategories;
    }
}