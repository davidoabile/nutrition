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
 * RetailExpress Payment method admin block
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Block_Adminhtml_Paymentmethod extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_paymentmethod';
        $this->_blockGroup         = 'moogento_retailexpress';
        $this->_addButton('import', array(
            'label'     => Mage::helper('moogento_retailexpress')->__('Import from RetailExpress'),
            'onclick'   => 'setLocation(\'' . $this->getImportUrl() .'\')',
            'class'     => 'add',
        ));

        parent::__construct();
        $this->_headerText         = Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method');
        $this->_updateButton('add', 'label', Mage::helper('moogento_retailexpress')->__('Add RetailExpress Payment method'));

    }

    public function getImportUrl()
    {
        return $this->getUrl('*/*/import');
    }
}
