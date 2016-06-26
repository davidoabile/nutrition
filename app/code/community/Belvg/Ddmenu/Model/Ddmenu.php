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

class Belvg_Ddmenu_Model_Ddmenu extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('ddmenu/ddmenu');
    }

    /**
     * Get Drop Down menu settings of category
     *
     * @param int Category id
     * @param int Store id
     * @return Belvg_Ddmenu_Model_Ddmenu
     */
    public function loadDdmenu($category_id=0, $store_id=0)
    {
        $category_id = (int)$category_id;
        $store_id    = (int)$store_id;
        return Mage::getModel('ddmenu/ddmenu')->getCollection()
                        ->addFieldToFilter('category_id', $category_id)
                        ->addFieldToFilter('store_id', $store_id)
                        ->getLastItem();
    }

}
