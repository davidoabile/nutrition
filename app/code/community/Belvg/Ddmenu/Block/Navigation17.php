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

/**
 *  for Magento 1.7
 */
class Belvg_Ddmenu_Block_Navigation17 extends Mage_Page_Block_Html_Topmenu
{

    /**
     * Category ids for last product search
     *
     * @var array
     */
    public  $categoryIds = array();

    /**
     * Max categories in one column
     *
     * @var int
     */
    public  $maxRows = 8;

    /**
     * Count recursion traversed categories
     *
     * @var int
     */
    private $rows;


    /**
     * Load current active category ids
     */
    public function _construct()
    {
        parent::_construct();
        /*Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu
        ));*/
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('ddmenu')->isEnabled()) {
            $this->setTemplate('belvg/ddmenu/navigation/top17.phtml');
        }

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu'  => $this->_menu,
            'block' => $this,
        ));

        return parent::_toHtml();
    }

    protected function getCategoryId($child)
    {
        $node = explode('-', $child->getId());

        return $node[2];
    }

    public function getAClass($level, $showMenu = FALSE)
    {
        return 'level' . $level
             . (($showMenu) ? ' has-children'  : '');
    }

    public function getLiClass($level, $showMenu = FALSE, $navClass, $isActive = FALSE, $count = 0, $i = 0)
    {
        return 'level' . $level
             . ' ' . $navClass
             . (($i == 1)   ? ' first'   : '')
             . (($showMenu) ? ' parent'  : '')
             . (($isActive) ? ' current' : '')
             . (($count && $i == $count) ? ' last' : '');
    }

    /**
     * Get category menu HTML
     *
     * @param Mage_Catalog_Model_Category
     * @param boolean Show <h4> tag
     * @return string
     */
    public function getSubCategoryHtml($child, $boo = TRUE, $level, $nav)
    {
        $html     = '';
        $i        = 0;
        $moreNum  = 2;
        $more     = 0;
        $moreLink = '<a href="' . $child->getUrl() . '">' . $this->__('More ...') . '</a>';
        $row  = 0;
        if ($this->maxRows) {
            $children = $child->getChildren();
            if ($children->count()) {
                $html .= '<ul class="level' . ($level - 1) . (($level - 1 == 0) ? '-sub' : '') . '">';

                foreach ($children as $child) {
                    $i++;
                    $navClass = $nav . '-' . $i;
                    $this->categoryIds[] = $this->getCategoryId($child);
                    $subHtml             = $this->getSubCategoryHtml($child, FALSE, $level + 1, $navClass);
     
                    if ($subHtml) {
                        $showMenu = TRUE;
                    } else {
                        $showMenu = FALSE;
                    }

                    if ($more <= $moreNum) {
                        $row += 1 + (int) $this->rows;
                    }

                    if ($row > $this->maxRows && $boo && $level == 1) {
                        $row = 1;
                        $html .= '</ul></dd><dd><ul class="level0-sub">';
                    }

                    if ($level > 1) {
                        $more++;
                        if ($more == $moreNum + 1) {
                            $html .= '<li>' . $moreLink . '</li>';
                        }
                    }

                    $html .= /*'['.$row.']'.*/'<li class="' . $this->getLiClass($level, $showMenu, $navClass, $child->getIsActive(), 0, $i) . (($more > $moreNum) ? ' more-cat' : '') . '">' .
                                (($level == 1) ? '<h4>' : '') .
                                '<a class="' . $this->getAClass($level, $showMenu) . '" href="' . $child->getUrl() . '">' .
                                    '<span>' . $this->escapeHtml($child->getName()) . '</span>' .
                                '</a>' .
                                (($level == 1) ? '</h4>' : '') .
                                $subHtml .
                             '</li>';
                }

                $html .= '</ul>';
            }

            $this->rows = $row;
        }

        return $html;
    }

    /**
     * Search last product in all sub categories
     *
     * @param Mage_Catalog_Model_Category
     */
    public function searchCategoriesForLastProduct($child)
    {
        $children = $child->getChildren();
        foreach ($children as $child) {
            $this->categoryIds[] = $this->getCategoryId($child);
        }
    }

    /**
     * Get Drop Down menu settings of category for current store
     *
     * @param int Category id
     * @return Belvg_Ddmenu_Model_Ddmenu
     */
    public function getDdmenuObject($categoryId)
    {
        $store_id       = Mage::app()->getStore()->getId();
        $ddmenu         = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, $store_id);
        if (!$ddmenu->getId() && $store_id!=0) {
            $ddmenu     = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, 0);
        } elseif ($ddmenu->getUseDefaultStoreView() && $store_id!=0) {
            $ddmenu     = Mage::getModel('ddmenu/ddmenu')->loadDdmenu($categoryId, 0);
        }

        return $ddmenu;
    }

}
