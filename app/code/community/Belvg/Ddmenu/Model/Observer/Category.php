<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Ddmenu_Model_Observer_Category
{
    /**
     * Enter the Tab for a category
     */
    public function injectTabs(Varien_Event_Observer $observer)
    {
        $block  = $observer->getEvent()->getBlock();

        if (Mage::getStoreConfig('ddmenu/settings/enabled', Mage::app()->getStore())) {
            if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Tabs) {
                if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
                    $block->addTab('custom-category-tab-dropdown-menu', array(
                        'label'   => 'Top Navigation (Dropdown Menu)',
                        'content' => $block->getLayout()->createBlock('adminhtml/template', 'custom-tab-content', array('template' => 'belvg/ddmenu/category_tab_form.phtml'))->toHtml(),
                    ));
                }
            }
        }
    }

    /**
     * Save a Tab for the category
     *
     * @param Mage_Catalog_Model_Category
     * @return boolean
     */
    public function saveData($category)
    {
        if ($category->getId()) {
            try {
                $ddmenu = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($category->getId(), $this->getStoreId());

                $ddmenu->setCategoryId($category->getId())
                       ->setUseDefaultStoreView((int)$this->_getRequest()->getParam('use_default_store_view', Mage_Core_Model_App::ADMIN_STORE_ID))
                       ->setStoreId((int)$this->_getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID));

                if (!$ddmenu->getUseDefaultStoreView()) {
                    $categoriesList     = $this->_getRequest()->getParam('categories_list', '');
                    if (is_array($categoriesList)) {
                        $categoriesList = implode(',', $categoriesList);
                    }

                    $ddmenu->setcategoriesList($categoriesList)
                           ->setBlocksLoc($this->_getRequest()->getParam('category_blocks_loc', ''))
                           ->setRewrite($this->_getRequest()->getParam('category_rewrite', ''))
                           ->setTemplate($this->_getRequest()->getParam('category_template', ''))
                           ->setStaticBlockId((int)$this->_getRequest()->getParam('category_static_block_id', 0))
                           ->setLastProduct((int)$this->_getRequest()->getParam('category_last_product', 0))
                           ->setRows((int)$this->_getRequest()->getParam('category_rows', 0));
                }

                /* INSERT / UPDATE */
                $ddmenu->save();

                return TRUE;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return FALSE;
            }
        }
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int) Mage::app()->getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID);
    }

    /**
     * If the module is enabled: saves Tab data
     */
    public function saveTabData(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('ddmenu/settings/enabled', Mage::app()->getStore())) {
            $category = Mage::registry('category');
            $this->saveData($category);
        }
    }

    /**
     * Retrieve request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

}
 
 