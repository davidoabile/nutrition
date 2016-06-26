<?php
/**
 * Moogento_RetailExpress extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Moogento
 * @package        Moogento_RetailExpress
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * RetailExpress Payment method admin edit tabs
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('paymentmethod_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_paymentmethod',
            array(
                'label'   => Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method'),
                'title'   => Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method'),
                'content' => $this->getLayout()->createBlock(
                    'moogento_retailexpress/adminhtml_paymentmethod_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve retailexpress payment method entity
     *
     * @access public
     * @return Moogento_RetailExpress_Model_Paymentmethod
     * @author Ultimate Module Creator
     */
    public function getPaymentmethod()
    {
        return Mage::registry('current_paymentmethod');
    }
}
