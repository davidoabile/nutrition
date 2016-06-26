<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        View.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 



/**
 * Adminhtml invoice create
 *

 */
class Mage_Adminhtml_Block_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'invoice_id';
        $this->_controller = 'sales_order_invoice';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/emails')) {
            $this->_updateButton('save', 'label', Mage::helper('sales')->__('Send Tracking Information'));
            $this->_updateButton('save',
                'onclick', "deleteConfirm('"
                . Mage::helper('sales')->__('Are you sure you want to send invoice email to customer?')
                . "', '" . $this->getEmailUrl() . "')"
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print', array(
                    'label' => Mage::helper('sales')->__('Print'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                )
            );
        }


        if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_button'))
            $this->_addButton('PDF Invoice', array(
                    'label' => Mage::helper('sales')->__('PDF Invoice'),
                    'class' => 'pdf_invoice_button',
                    'onclick' => 'setLocation(\'' . $this->getPdfInvoiceUrl() . '\')',
                )
            );
        if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_packing_sheet_button'))
            $this->_addButton('PDF Packing Ship', array(
                    'label' => Mage::helper('sales')->__('PDF Packing Sheet'),
                    'class' => 'pdf_packingsheet_button',
                    'onclick' => 'setLocation(\'' . $this->getPdfShippingUrl() . '\')',
                )
            );
        
        if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_and_packing_sheet_button'))
            $this->_addButton('PDF Invoice & Packing', array(
                    'label' => Mage::helper('sales')->__('PDF Invoice and Packing Sheet'),
                    'class' => 'pdf_invoice_packingsheet_button',
                    'onclick' => 'setLocation(\'' . $this->getPdfInvoiceShippingUrl() . '\')',
                )
            );
        if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_zebra_label_button'))
            $this->_addButton('Zebra Label', array(
                    'label' => Mage::helper('sales')->__('PDF Zebra Label'),
                    'class' => 'pdf_invoice_packingsheet_button',
                    'onclick' => 'setLocation(\'' . $this->getPdfZebraLabelUrl() . '\')',
                )
            );
        if (Mage::getStoreConfig('pickpack_options/button_invoice/order_resend_email_button'))    
            $this->_addButton('Resend email', array(
                        'label' => Mage::helper('sales')->__('Resend Email'),
                        'class' => 'send_notification',
                        'onclick' => 'setLocation(\'' . $this->getResendMailUrl() . '\')',
                    )
                );
        $orderPayment = $this->getInvoice()->getOrder()->getPayment();
        
        
         if ($this->getInvoice()->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                && $this->getInvoice()->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund())) {
                $this->_addButton('capture', array( // capture?
                    'label'     => Mage::helper('sales')->__('Credit Memo'),
                    'class'     => 'go',
                    'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                    )
                );
            }
        }        
    }
     public function getCreditMemoUrl()
    {
        return $this->getUrl('*/sales_order_creditmemo/start', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
           'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function getPdfInvoiceUrl()
    {
        return $this->getUrl('*/pickpack_sales_invoice/mooinvoice/order_id/invoice_ids', array(
			'order_id' => $this->getInvoice()->getOrderId(),
            'invoice_ids' => $this->getInvoice()->getId()
        ));
    }
    public function getPdfShippingUrl()
    {
        return $this->getUrl('*/pickpack_sales_invoice/pack/order_id/invoice_ids', array(
			'order_id' => $this->getInvoice()->getOrderId(),
            'invoice_ids' => $this->getInvoice()->getId()
        ));
    }

    public function getPdfInvoiceShippingUrl(){
          return $this->getUrl('*/pickpack_sales_invoice/mooinvoicepack/order_id/invoice_ids', array(
            'order_id' => $this->getInvoice()->getOrderId(),
            'invoice_ids' => $this->getInvoice()->getId()
        ));
    }
    public function getPdfZebraLabelUrl()
    {
        return $this->getUrl('*/pickpack_sales_invoice/labelzebradetail/order_id/invoice_ids', array(
            'order_id' => $this->getInvoice()->getOrderId(),
            'invoice_ids' => $this->getInvoice()->getId()
        ));
    }

    /**
     * Retrieve invoice model instance
     *
     * @return Mage_Sales_Model_Order_invoice
     */
    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    public function getHeaderText()
    {
        if ($this->getInvoice()->getEmailSent()) {
            $emailSent = Mage::helper('sales')->__('the invoice email was sent');
        } else {
            $emailSent = Mage::helper('sales')->__('the invoice email is not sent');
        }
        return Mage::helper('sales')->__('invoice #%1$s | %3$s (%2$s)', $this->getInvoice()->getIncrementId(), $emailSent, $this->formatDate($this->getInvoice()->getCreatedAtDate(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array(
                'order_id' => $this->getInvoice()->getOrderId(),
                'active_tab' => 'order_invoices'
            ));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/sales_order_invoice/email', array('invoice_id' => $this->getInvoice()->getId()));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'invoice_id' => $this->getInvoice()->getId()
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getInvoice()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getInvoice()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/sales_invoice/') . '\')');
        }
        return $this;
    }
}
