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
 * Adminhtml shipment create
 *

 */
class Mage_Adminhtml_Block_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'shipment_id';
        $this->_controller = 'sales_order_shipment';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/emails')) {
            $this->_updateButton('save', 'label', Mage::helper('sales')->__('Send Tracking Information'));
            $this->_updateButton('save',
                'onclick', "deleteConfirm('"
                . Mage::helper('sales')->__('Are you sure you want to send Shipment email to customer?')
                . "', '" . $this->getEmailUrl() . "')"
            );
        }

        if ($this->getShipment()->getId()) {
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
    }

    public function getPdfInvoiceUrl()
    {
        return $this->getUrl('*/pickpack_sales_shipment/mooorderinvoice/order_id/shipment_ids', array(
			'order_id' => $this->getShipment()->getOrderId(),
            'shipment_ids' => $this->getShipment()->getId()
        ));
    }
    public function getPdfShippingUrl()
    {
        return $this->getUrl('*/pickpack_sales_shipment/moopack/order_id/shipment_ids', array(
			'order_id' => $this->getShipment()->getOrderId(),
            'shipment_ids' => $this->getShipment()->getId()
        ));
    }

    public function getPdfInvoiceShippingUrl(){
          return $this->getUrl('*/pickpack_sales_shipment/mooinvoicepack/order_id/shipment_ids', array(
            'order_id' => $this->getShipment()->getOrderId(),
            'shipment_ids' => $this->getShipment()->getId()
        ));
    }
    public function getPdfZebraLabelUrl()
    {
        return $this->getUrl('*/pickpack_sales_shipment/labelzebradetail/order_id/shipment_ids', array(
            'order_id' => $this->getShipment()->getOrderId(),
            'shipment_ids' => $this->getShipment()->getId()
        ));
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    public function getHeaderText()
    {
        if ($this->getShipment()->getEmailSent()) {
            $emailSent = Mage::helper('sales')->__('the shipment email was sent');
        } else {
            $emailSent = Mage::helper('sales')->__('the shipment email is not sent');
        }
        return Mage::helper('sales')->__('Shipment #%1$s | %3$s (%2$s)', $this->getShipment()->getIncrementId(), $emailSent, $this->formatDate($this->getShipment()->getCreatedAtDate(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array(
                'order_id' => $this->getShipment()->getOrderId(),
                'active_tab' => 'order_shipments'
            ));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/sales_order_shipment/email', array('shipment_id' => $this->getShipment()->getId()));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'invoice_id' => $this->getShipment()->getId()
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getShipment()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getShipment()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/sales_shipment/') . '\')');
        }
        return $this;
    }
}
