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
 * RetailExpress Payment method edit form tab
 *
 * @category    Moogento
 * @package     Moogento_RetailExpress
 * @author      Ultimate Module Creator
 */
class Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Moogento_RetailExpress_Block_Adminhtml_Paymentmethod_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('paymentmethod_');
        $form->setFieldNameSuffix('paymentmethod');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'paymentmethod_form',
            array('legend' => Mage::helper('moogento_retailexpress')->__('RetailExpress Payment method'))
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('Name'),
                'name'  => 'name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'retail_express_id',
            'text',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('Retail express ID'),
                'name'  => 'retail_express_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $paymentOptions = Mage::helper('moogento_retailexpress')->getPaymentOptions();
        array_unshift($paymentOptions, array('value' => '', 'label' => ''));
        $fieldset->addField(
            'magento_payment',
            'multiselect',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('Magento payment mehtod'),
                'name'  => 'magento_payment',
                'required'  => false,
                'class' => '',
                'values'=> $paymentOptions,
            )
        );

        $fieldset->addField(
            'loyalty_enabled',
            'select',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('Loyalty Enabled'),
                'name'  => 'loyalty_enabled',
            'required'  => true,
            'class' => 'required-entry',

            'values'=> array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('moogento_retailexpress')->__('Yes'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('moogento_retailexpress')->__('No'),
                ),
            ),
           )
        );

        $fieldset->addField(
            'pos_enabled',
            'select',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('POS enabled'),
                'name'  => 'pos_enabled',
            'required'  => true,
            'class' => 'required-entry',

            'values'=> array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('moogento_retailexpress')->__('Yes'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('moogento_retailexpress')->__('No'),
                ),
            ),
           )
        );

        $fieldset->addField(
            'loyalty_ratio',
            'text',
            array(
                'label' => Mage::helper('moogento_retailexpress')->__('Loyalty Ratio'),
                'name'  => 'loyalty_ratio',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('moogento_retailexpress')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('moogento_retailexpress')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('moogento_retailexpress')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_paymentmethod')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getPaymentmethodData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getPaymentmethodData());
            Mage::getSingleton('adminhtml/session')->setPaymentmethodData(null);
        } elseif (Mage::registry('current_paymentmethod')) {
            $formValues = array_merge($formValues, Mage::registry('current_paymentmethod')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
