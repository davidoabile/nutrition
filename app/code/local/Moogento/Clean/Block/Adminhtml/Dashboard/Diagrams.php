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
 * Adminhtml dashboard diagram tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Moogento_Clean_Block_Adminhtml_Dashboard_Diagrams extends Mage_Adminhtml_Block_Dashboard_Diagrams
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout()
    {
        if (Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_THEME) == 'default') {
            return parent::_prepareLayout();
        }
        if (Mage::getStoreConfig('moogento_clean/dashboard/show_default_charts')==0){
            $this->addTab('orders', array(
                'label'     => $this->__('Orders'),
                'content'   => $this->getLayout()->createBlock('adminhtml/dashboard_tab_orders')->toHtml(),
                'active'    => true,
                'updatable' => true,
            ));
            if (Mage::getStoreConfig('moogento_clean/dashboard/show_prices')){
                $this->addTab('amounts', array(
                    'label'     => $this->__('Amounts'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/dashboard_tab_amounts')->toHtml(),
                        'updatable' => true,
                ));
            }
        }
        else
        {
        $this->addTab('ordersamounts', array(
            'label'     => $this->__('Orders/Amounts'),
                'content'   =>  $this->getLayout()->createBlock('moogento_clean/adminhtml_dashboard_tab_ordersamounts')->toHtml(),
                'active'    => true,
                'updatable' => true,
        ));
        }
		
        $this->addTab('yearsells', array(
            'label'     => $this->__('Totals'),
            'content'   => $this->getLayout()->createBlock('moogento_clean/adminhtml_dashboard_tab_yearsells')->toHtml(),
        ));
        $this->addTab('dailysells', array(
            'label'     => $this->__('Averages'),
            'content'   => $this->getLayout()->createBlock('moogento_clean/adminhtml_dashboard_tab_dailysells')->toHtml(),
        ));
        $this->addTab('bestsellers', array(
            'label'     => $this->__('Bestsellers'),
            'content'   => $this->getLayout()->createBlock('moogento_clean/adminhtml_dashboard_tab_bestsellers')->toHtml(),
        ));
		
        return $this;
    }

    public function getUpdatableTabIds()
    {
        $result = array();
        foreach ($this->_tabs as $tabId => $tabData) {
            if (isset($tabData['updatable']) && $tabData['updatable']) {
                $result[] = $tabId;
            }
        }

        return $result;
    }
}
