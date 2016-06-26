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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard bottom tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Moogento_Clean_Block_Adminhtml_Dashboard_Grids extends Mage_Adminhtml_Block_Dashboard_Grids
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('moogento/clean/widget/tabshoriz.phtml');
    }

    protected function _prepareLayout()
    {
        if (Mage::getStoreConfig('moogento_clean/dashboard/show_bestsellers')){
            $this->addTab('ordered_products', array(
                'label'         => $this->__('Bestsellers'),
                'content'       => $this->getLayout()->createBlock('adminhtml/dashboard_tab_products_ordered')->toHtml(),
                'url_update'    => $this->getUrl('*/*/productsOrdered', array('_current'=>true)),
                'active'        => true
            ));
        }
            
        if (Mage::getStoreConfig('moogento_clean/dashboard/show_most_viewed_products')){
            $this->addTab('reviewed_products', array(
                'label'         => $this->__('Most Viewed Products'),
                'content'       => $this->getLayout()->createBlock('adminhtml/dashboard_tab_products_viewed')->toHtml(),
                'url_update'    => $this->getUrl('*/*/productsViewed', array('_current'=>true)),
            ));
        }

        if (Mage::getStoreConfig('moogento_clean/dashboard/show_new_customers')){
            $this->addTab('new_customers', array(
                'label'         => $this->__('New Customers'),
                'content'       => $this->getLayout()->createBlock('adminhtml/dashboard_tab_customers_newest')->toHtml(),
                'url_update'    => $this->getUrl('*/*/customersNewest', array('_current'=>true)),
            ));
        }
        
        if (Mage::getStoreConfig('moogento_clean/dashboard/show_vip_customers')){
            $this->addTab('customers', array(
                'label'         => $this->__('VIP Customers'),
                'content'       => $this->getLayout()->createBlock('adminhtml/dashboard_tab_customers_most')->toHtml(),
                'url_update'    => $this->getUrl('*/*/customersMost', array('_current'=>true)),
            ));
        }
    }
    
    public function tabsArray($tabs)
    {
        $result = array();
        foreach($tabs as $index=>$tab){
            $result[$index]["el_id"] = "grid_tab_".$tab->getTabId()."_content";
            $result[$index]["el_url"] = $tab->getUrlUpdate();
        }
        return json_encode($result);
    }
}
