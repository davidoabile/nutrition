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
 * RetailExpress Payment method admin edit form
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'moogento_retailexpress';
        $this->_controller = 'adminhtml_paymentmethod';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('moogento_retailexpress')->__('Save RetailExpress Payment method')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('moogento_retailexpress')->__('Delete RetailExpress Payment method')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('moogento_retailexpress')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_paymentmethod') && Mage::registry('current_paymentmethod')->getId()) {
            return Mage::helper('moogento_retailexpress')->__(
                "Edit RetailExpress Payment method '%s'",
                $this->escapeHtml(Mage::registry('current_paymentmethod')->getName())
            );
        } else {
            return Mage::helper('moogento_retailexpress')->__('Add RetailExpress Payment method');
        }
    }
}
