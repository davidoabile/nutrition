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
 * Adminhtml sales order view
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order';
        $this->_mode = 'view';
        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        if ($this->_isAllowedAction('edit') && $order->canEdit()) {
            $onclickJs = 'deleteConfirm(\''
                . Mage::helper('sales')->__('Are you sure? This order will be canceled and a new one will be created instead')
                . '\', \'' . $this->getEditUrl() . '\');';
            $this->_addButton('order_edit', array(
                'label' => Mage::helper('sales')->__('Edit'),
                'onclick' => $onclickJs,
            ));
            // see if order has non-editable products as items
            $nonEditableTypes = array_keys($this->getOrder()->getResource()->aggregateProductsByTypes(
                $order->getId(),
                array_keys(Mage::getConfig()
                        ->getNode('adminhtml/sales/order/create/available_product_types')
                        ->asArray()
                ),
                false
            ));
            if ($nonEditableTypes) {
                $this->_updateButton('order_edit', 'onclick',
                    'if (!confirm(\'' .
                    Mage::helper('sales')->__('This order contains (%s) items and therefore cannot be edited through the admin interface at this time, if you wish to continue editing the (%s) items will be removed, the order will be canceled and a new order will be placed.', implode(', ', $nonEditableTypes), implode(', ', $nonEditableTypes)) . '\')) return false;' . $onclickJs
                );
            }
        }

        if ($this->_isAllowedAction('cancel') && $order->canCancel()) {
            $message = Mage::helper('sales')->__('Are you sure you want to cancel this order?');
            $this->_addButton('order_cancel', array(
                'label' => Mage::helper('sales')->__('Cancel'),
                'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getCancelUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('emails') && !$order->isCanceled()) {
            $message = Mage::helper('sales')->__('Are you sure you want to send order email to customer?');
            $this->addButton('send_notification', array(
                'label' => Mage::helper('sales')->__('Send Email'),
                'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')",
            ));
        }

        if ($this->_isAllowedAction('creditmemo') && $order->canCreditmemo()) {
            $message = Mage::helper('sales')->__('This will create an offline refund. To create an online refund, open an invoice and create credit memo for it. Do you wish to proceed?');
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            }
            $this->_addButton('order_creditmemo', array(
                'label' => Mage::helper('sales')->__('Credit Memo'),
                'onclick' => $onClick,
                'class' => 'go'
            ));
        }

        // invoice action intentionally
        if ($this->_isAllowedAction('invoice') && $order->canVoidPayment()) {
            $message = Mage::helper('sales')->__('Are you sure you want to void the payment?');
            $this->addButton('void_payment', array(
                'label' => Mage::helper('sales')->__('Void'),
                'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')",
            ));
        }

        if ($this->_isAllowedAction('hold') && $order->canHold()) {
            $this->_addButton('order_hold', array(
                'label' => Mage::helper('sales')->__('Hold'),
                'onclick' => 'setLocation(\'' . $this->getHoldUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('unhold') && $order->canUnhold()) {
            $this->_addButton('order_unhold', array(
                'label' => Mage::helper('sales')->__('Unhold'),
                'onclick' => 'setLocation(\'' . $this->getUnholdUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('review_payment')) {
            if ($order->canReviewPayment()) {
                $message = Mage::helper('sales')->__('Are you sure you want to accept this payment?');
                $this->_addButton('accept_payment', array(
                    'label' => Mage::helper('sales')->__('Accept Payment'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')",
                ));
                $message = Mage::helper('sales')->__('Are you sure you want to deny this payment?');
                $this->_addButton('deny_payment', array(
                    'label' => Mage::helper('sales')->__('Deny Payment'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')",
                ));
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->_addButton('get_review_payment_update', array(
                    'label' => Mage::helper('sales')->__('Get Payment Update'),
                    'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')',
                ));
            }
        }

        if ($this->_isAllowedAction('invoice') && $order->canInvoice()) {
            $_label = $order->getForcedDoShipmentWithInvoice() ?
                Mage::helper('sales')->__('Invoice and Ship') :
                Mage::helper('sales')->__('Invoice');
            $this->_addButton('order_invoice', array(
                'label' => $_label,
                'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                'class' => 'go'
            ));
        }

        if ($this->_isAllowedAction('ship') && $order->canShip()
            && !$order->getForcedDoShipmentWithInvoice()
        ) {
            $this->_addButton('order_ship', array(
                'label' => Mage::helper('sales')->__('Ship'),
                'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                'class' => 'go'
            ));
        }

        if($this->_checkVersion())
            if ($this->_isAllowedAction('reorder')
                && $this->helper('sales/reorder')->isAllowed($order->getStore())
                && $order->canReorder()
            ) {
                $this->_addButton('order_reorder', array(
                    'label' => Mage::helper('sales')->__('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'go'
                ));
            }
        else
            if ($this->_isAllowedAction('reorder')
                && $this->helper('sales/reorder')->isAllow($order->getStore())
                && $order->canReorder()
            ) {
                $this->_addButton('order_reorder', array(
                    'label' => Mage::helper('sales')->__('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'go'
                ));
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
    private function _checkVersion(){
        $isVersionGt15 = true;
        $version_magento = Mage::getVersion();
        $versionArr = explode(".", $version_magento);
        if($versionArr[0] < '1')
            $isVersionGt15 = false;
        elseif($versionArr[0] == '1' && $versionArr[1] <= '5')
            $isVersionGt15 = false;
        return $isVersionGt15;
    }
    /**
     * Retrieve order model object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('sales_order');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    public function getHeaderText()
    {
        if ($_extOrderId = $this->getOrder()->getExtOrderId()) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return Mage::helper('sales')->__('Order # %s %s | %s', $this->getOrder()->getRealOrderId(), $_extOrderId, $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true));
    }

    public function getUrl($params = '', $params2 = array())
    {
        $params2['order_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/sales_order_edit/start');
    }

    public function getPdfInvoiceUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/mooorderinvoice/');
    }

    public function getPdfShippingUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/mooordershipment/');
    }
	
	public function getPdfZebraLabelUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/labelzebradetail/');
    }
	
    public function getPdfInvoiceShippingUrl(){
        return $this->getUrl('*/pickpack_sales_order/mooorderinvoicepack/');
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/*/email');
    }

    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel');
    }

    public function getInvoiceUrl()
    {
        return $this->getUrl('*/sales_order_invoice/start');
    }

    public function getCreditmemoUrl()
    {
        return $this->getUrl('*/sales_order_creditmemo/start');
    }

    public function getHoldUrl()
    {
        return $this->getUrl('*/*/hold');
    }

    public function getUnholdUrl()
    {
        return $this->getUrl('*/*/unhold');
    }

    public function getShipUrl()
    {
        return $this->getUrl('*/sales_order_shipment/start');
    }

    public function getCommentUrl()
    {
        return $this->getUrl('*/*/comment');
    }

    public function getReorderUrl()
    {
        return $this->getUrl('*/sales_order_create/reorder');
    }

    /**
     * Payment void URL getter
     */
    public function getVoidPaymentUrl()
    {
        return $this->getUrl('*/*/voidPayment');
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getOrder()->getBackUrl()) {
            return $this->getOrder()->getBackUrl();
        }

        return $this->getUrl('*/*/');
    }

    public function getReviewPaymentUrl($action)
    {
        return $this->getUrl('*/*/reviewPayment', array('action' => $action));
    }
    
    public function getResendMailUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/resendmail/');
    }
//
//    /**
//     * Return URL for accept payment action
//     *
//     * @return string
//     */
//    public function getAcceptPaymentUrl()
//    {
//        return $this->getUrl('*/*/reviewPayment', array('action' => 'accept'));
//    }
//
//    /**
//     * Return URL for deny payment action
//     *
//     * @return string
//     */
//    public function getDenyPaymentUrl()
//    {
//        return $this->getUrl('*/*/reviewPayment', array('action' => 'deny'));
//    }
//
//    public function getPaymentReviewUpdateUrl()
//    {
//        return $this->getUrl('*/*/reviewPaymentUpdate');
//    }
}
