<?php

class Moogento_Pickpack_Model_Adminhtml_Widget_Block_Observer extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _isAllowed($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
        //TODO check permission after create account
        //return true;
    }

    
    public function beforeHtml($observer)
    {
        $block = $observer->getBlock();
        $lucidpath_installed = false;
        $magik_installed = false;
        $Raveinfosys_Deleteorder = false;
        $MW_Ddate = false;
        $Imedia_SalesOrder = false;
        $Oscp_SalesOrderGridOverride = false;
        if(Mage::helper('pickpack')->isInstalled("LucidPath_SalesRep"))
            $lucidpath_installed = true;
        if(Mage::helper('pickpack')->isInstalled("magik_magikfees"))
            $magik_installed = true;
        if(Mage::helper('pickpack')->isInstalled("Raveinfosys_Deleteorder"))
            $Raveinfosys_Deleteorder = true;
        if(Mage::helper('pickpack')->isInstalled("MW_Ddate"))
            $MW_Ddate = true;
        if(Mage::helper('pickpack')->isInstalled("Imedia_SalesOrder"))
            $Imedia_SalesOrder = true;
            
        if(Mage::helper('pickpack')->isInstalled("Oscp_SalesOrderGridOverride"))
            $Oscp_SalesOrderGridOverride = true;
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid 
            || ((null !== $magik_installed) && $block instanceof Magik_Magikfees_Block_Adminhtml_Sales_Order_Grid) 
            || ((null !== $lucidpath_installed) && $block instanceof LucidPath_SalesRep_Block_Adminhtml_Order_Grid) 
            || ((null !== $Raveinfosys_Deleteorder) && $block instanceof Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $MW_Ddate) && $block instanceof MW_Ddate_Block_Adminhtml_Sales_Order_Grid)
            || ((null !== $Oscp_SalesOrderGridOverride) && $block instanceof Oscp_SalesOrderGridOverride_Block_Adminhtml_Sales_Order_Grid)            
            || ((null !== $Imedia_SalesOrder) && $block instanceof Imedia_SalesOrder_Block_Sales_Order_Grid)) {
            $default_massaction_items = $block->getMassactionBlock()->getItems();
            $massction_items = $block->getMassactionBlock();
            foreach($default_massaction_items as $default_action)
            {
                switch($default_action->getData('id'))
                {
                    case 'cancel_order':
                        if($this->_getConfig('show_default_cancel_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('cancel_order');
                        }
                        break;
                    case 'hold_order':
                        if($this->_getConfig('show_default_hold_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('hold_order');
                        }
                        break;
                    case 'unhold_order':
                        if($this->_getConfig('show_default_unhold_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('unhold_order');
                        }
                        break;
                    case 'pdfinvoices_order':
                        if($this->_getConfig('show_default_pdfinvoices_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('pdfinvoices_order');
                        }
                        break;
                    case 'pdfshipments_order':
                        if($this->_getConfig('show_default_pdfshipments_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('pdfshipments_order');
                        }
                        break;
                    case 'pdfcreditmemos_order':
                        if($this->_getConfig('show_default_pdfcreditmemos_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('pdfcreditmemos_order');
                        }
                        break;
                    case 'pdfdocs_order':
                        if($this->_getConfig('show_default_pdfdocs_order',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('pdfdocs_order');
                        }
                        break;
                    case 'print_shipping_label':
                        if($this->_getConfig('show_default_print_shipping_label',1, false, 'action_menu') == 0)
                        {  
                            $block->getMassactionBlock()->removeItem('print_shipping_label');
                        }
                        break;
                        
                }
                
            }
            if($this->_getConfig('show_seperator1',1, false, 'action_menu') == 1)
            {
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }
            if ($this->_isAllowed('moo_pickpack_pdf_packingsheet')) {
                if($this->_getConfig('show_pdf_packing_sheet',1, false, 'action_menu') == 1)
                {   
                        $block->getMassactionBlock()->addItem('pdfpack_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pack'),
                        ));
                } 
            }      
            
            if ($this->_isAllowed('moo_pickpack_pdf_invoice')) {
                if($this->_getConfig('show_pdf_invoice',1, false, 'action_menu') == 1)
                {    
                    $block->getMassactionBlock()->addItem('pdfinvoice_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/mooinvoice'),
                    ));
                }
            }
            

            
            /*
                $this->getMassactionBlock()->addItem('pdfmooletter_order', array(
                 'label'=> Mage::helper('pickpack')->__('PDF Letter'),
                 'url'  => $this->getUrl('pickpack_sales_order/mooletter'),
                ));
                */

            if ($this->_isAllowed('moo_pickpack_pdf_invoice_and_packingsheet')) {
                if($this->_getConfig('show_pdf_invoice_and_packing_sheet',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfinvoice_pdfpack_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice & Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/mooinvoicepack'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_pdf_label_zebra')) {
                if($this->_getConfig('show_pdf_label_zebra',1, false, 'action_menu') == 1)
                {
                    $block->getMassactionBlock()->addItem('pdflabel_zebra', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/labelzebra'),
                    ));
                }
            }
             if ($this->_isAllowed('moo_pickpack_pdf_address_label')) {    
                if($this->_getConfig('show_pdf_label_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdflabel_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Address Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/label'),
                    ));
                }
            }
            if(Mage::helper('pickpack')->isInstalled('Moogento_Cn22'))
            if ($this->_isAllowed('moo_pickpack_pdf_cn22_label')) {    
                if($this->_getConfig('show_pdf_label_cn22',0, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfcn22_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (CN22 Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/cn22'),
                    ));
                }
            }
            
            
            if ($this->_isAllowed('moo_pickpack_pdf_combined')) {    
                if($this->_getConfig('show_pdf_enpick_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfenpick_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/enpick'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {
                if($this->_getConfig('show_pdf_pick_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfpick_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick'),
                    ));
                }
            }
            
            if(Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox'))
                if($this->_getConfig('show_pdf_trolleybox',0, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdftrolleybox_order', array( 
                        'label' => Mage::helper('pickpack')->__('PDF (Trolleybox Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/trolleybox'),
                    ));
                }
            
            if ($this->_isAllowed('moo_pickpack_pdf_product_separated')) {
                if($this->_getConfig('show_pdf_product_separated',1, false, 'action_menu') == 1)
                {   
                    $block->getMassactionBlock()->addItem('pdfproduct_separated', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Product-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/productSeparated'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_pdf_separated2')) {
                if($this->_getConfig('show_pdf_pick_order_2',1, false, 'action_menu') == 1)
                {
                    $block->getMassactionBlock()->addItem('pdfpick_order2', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick2'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_gift_message')) {
                if($this->_getConfig('show_pdf_gift_message',1, false, 'action_menu') == 1)
                {
                    $block->getMassactionBlock()->addItem('pdfgift_message', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Gift Message)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/giftmessage'),
                    ));
                }
            }
            
            
            // if ($this->_isAllowed('moo_pickpack_pdf_troylleybox')) {    
            if ($this->_isAllowed('moo_pickpack_pdf_csv_out_of_stock')) {        
                if($this->_getConfig('show_pdf_stock_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfstock_order', array(
                        'label' => Mage::helper('pickpack')->__('PDF/CSV (Out-of-stock List)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/stock'),
                    ));
                }
            }
            
            if($this->_getConfig('show_seperator2',1, false, 'action_menu') == 1)
            {  
                $block->getMassactionBlock()->addItem('seperator2', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }
        
            if ($this->_isAllowed('moo_pickpack_csv_orders')) {    
                if($this->_getConfig('show_csv_orders_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('csvorders_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Orders)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/orderscsv'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {
                if($this->_getConfig('show_csv_pick_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('csvpick_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Order-separated Products)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pickcsv'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_csv_pick_combined_order')) {    
                if($this->_getConfig('show_csv_pickcombined_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('csvpickcombined_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV (Order-combined Products)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pickcsvcombined'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_manifest_combined_order')) {    
                if($this->_getConfig('show_manifest_combined_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('manifestcombined_order', array(
                        'label' => Mage::helper('pickpack')->__('CSV/XML (Cargo Manifest)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/manifestcsvcombined'),
                    ));
                }
            }
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Shipment_Grid) {
            if($this->_getConfig('show_seperator1',1, false, 'action_menu') == 1)
            {  
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }

            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {
                if($this->_getConfig('show_pdf_packing_sheet',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfpack_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/pack'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_label_zebra')) {
                if($this->_getConfig('show_pdf_label_zebra',1, false, 'action_menu') == 1)
                {
                    $block->getMassactionBlock()->addItem('pdflabel_zebra_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/labelzebra'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {    
                if($this->_getConfig('show_pdf_pick_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfpick_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/pick'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_separated')) {
                if($this->_getConfig('show_pdf_pick_order_2',1, false, 'action_menu') == 1)
                {
                    $block->getMassactionBlock()->addItem('pdfpick_order2', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)'),
                        'url' => $block->getUrl('*/pickpack_sales_order/pick2'),
                    ));
                }
            }
            if ($this->_isAllowed('moo_pickpack_pdf_combined')) {
                if($this->_getConfig('show_pdf_enpick_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfenpick_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/enpick'),
                    ));
                }
            }

            if ($this->_isAllowed('moo_pickpack_pdf_csv_out_of_stock')) {
                if($this->_getConfig('show_pdf_stock_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfstock_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF/CSV (Out-of-stock List)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/stock'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_pdf_address_label')) {         
                if($this->_getConfig('show_pdf_label_order',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdflabel_shipment', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Address Labels)'),
                        'url' => $block->getUrl('*/pickpack_sales_shipment/label'),
                    ));
                }
            }    
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Invoice_Grid) {
            
            if($this->_getConfig('show_seperator1',1, false, 'action_menu') == 1)
            {  
                $block->getMassactionBlock()->addItem('seperator1', array(
                    'label' => Mage::helper('pickpack')->__('---------------'),
                    'url' => '',
                ));
            }

            if ($this->_isAllowed('moo_pickpack_pdf_invoice')) {
                if($this->_getConfig('show_pdf_invoice',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfinvoice_invoice', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Invoice)'),
                        'url' => $block->getUrl('*/pickpack_sales_invoice/mooinvoice'),
                    ));
                }
            }
            
            if ($this->_isAllowed('moo_pickpack_csv_pick_order')) {    
                if($this->_getConfig('show_pdf_packing_sheet',1, false, 'action_menu') == 1)
                {  
                    $block->getMassactionBlock()->addItem('pdfpack_invoice', array(
                        'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)'),
                        'url' => $block->getUrl('*/pickpack_sales_invoice/pack'),
                    ));
                }
            }
        }
        // //for order view page
/*
        if($block instanceof Mage_Adminhtml_Block_Sales_Order_View
            || ($block instanceof Cmsmart_AdminTheme_Block_Adminhtml_Block_Sales_Order_View)
            ){
            $block->_objectId = 'order_id';
            $block->_controller = 'sales_order';
            $block->_mode = 'view';
            parent::__construct();

            $block->_removeButton('delete');
            $block->_removeButton('reset');
            $block->_removeButton('save');
            $block->setId('sales_order_view');
            $order = $this->getOrder();

            // if ($this->_isAllowedAction('edit') && $order->canEdit()) {
            //     $onclickJs = 'deleteConfirm(\''
            //         . Mage::helper('sales')->__('Are you sure? block order will be canceled and a new one will be created instead')
            //         . '\', \'' . $this->getEditUrl() . '\');';
            //     $block->_addButton('order_edit', array(
            //         'label' => Mage::helper('sales')->__('Edit'),
            //         'onclick' => $onclickJs,
            //     ));
            //     // see if order has non-editable products as items
            //     $nonEditableTypes = array_keys($this->getOrder()->getResource()->aggregateProductsByTypes(
            //         $order->getId(),
            //         array_keys(Mage::getConfig()
            //                 ->getNode('adminhtml/sales/order/create/available_product_types')
            //                 ->asArray()
            //         ),
            //         false
            //     ));
            //     if ($nonEditableTypes) {
            //         $block->_updateButton('order_edit', 'onclick',
            //             'if (!confirm(\'' .
            //             Mage::helper('sales')->__('block order contains (%s) items and therefore cannot be edited through the admin interface at block time, if you wish to continue editing the (%s) items will be removed, the order will be canceled and a new order will be placed.', implode(', ', $nonEditableTypes), implode(', ', $nonEditableTypes)) . '\')) return false;' . $onclickJs
            //         );
            //     }
            // }

            // if ($this->_isAllowedAction('cancel') && $order->canCancel()) {
            //     $message = Mage::helper('sales')->__('Are you sure you want to cancel block order?');
            //     $block->_addButton('order_cancel', array(
            //         'label' => Mage::helper('sales')->__('Cancel'),
            //         'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getCancelUrl() . '\')',
            //     ));
            // }

            // if ($this->_isAllowedAction('emails') && !$order->isCanceled()) {
            //     $message = Mage::helper('sales')->__('Are you sure you want to send order email to customer?');
            //     $block->addButton('send_notification', array(
            //         'label' => Mage::helper('sales')->__('Send Email'),
            //         'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')",
            //     ));
            // }

            // if ($this->_isAllowedAction('creditmemo') && $order->canCreditmemo()) {
            //     $message = Mage::helper('sales')->__('block will create an offline refund. To create an online refund, open an invoice and create credit memo for it. Do you wish to proceed?');
            //     $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            //     if ($order->getPayment()->getMethodInstance()->isGateway()) {
            //         $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            //     }
            //     $block->_addButton('order_creditmemo', array(
            //         'label' => Mage::helper('sales')->__('Credit Memo'),
            //         'onclick' => $onClick,
            //         'class' => 'go'
            //     ));
            // }

            // invoice action intentionally
            // if ($this->_isAllowedAction('invoice') && $order->canVoidPayment()) {
            //     $message = Mage::helper('sales')->__('Are you sure you want to void the payment?');
            //     $block->addButton('void_payment', array(
            //         'label' => Mage::helper('sales')->__('Void'),
            //         'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')",
            //     ));
            // }

            // if ($this->_isAllowedAction('hold') && $order->canHold()) {
            //     $block->_addButton('order_hold', array(
            //         'label' => Mage::helper('sales')->__('Hold'),
            //         'onclick' => 'setLocation(\'' . $this->getHoldUrl() . '\')',
            //     ));
            // }

            // if ($this->_isAllowedAction('unhold') && $order->canUnhold()) {
            //     $block->_addButton('order_unhold', array(
            //         'label' => Mage::helper('sales')->__('Unhold'),
            //         'onclick' => 'setLocation(\'' . $this->getUnholdUrl() . '\')',
            //     ));
            // }

            // if ($this->_isAllowedAction('review_payment')) {
            //     if ($order->canReviewPayment()) {
            //         $message = Mage::helper('sales')->__('Are you sure you want to accept block payment?');
            //         $block->_addButton('accept_payment', array(
            //             'label' => Mage::helper('sales')->__('Accept Payment'),
            //             'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')",
            //         ));
            //         $message = Mage::helper('sales')->__('Are you sure you want to deny block payment?');
            //         $block->_addButton('deny_payment', array(
            //             'label' => Mage::helper('sales')->__('Deny Payment'),
            //             'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')",
            //         ));
            //     }
            //     if ($order->canFetchPaymentReviewUpdate()) {
            //         $block->_addButton('get_review_payment_update', array(
            //             'label' => Mage::helper('sales')->__('Get Payment Update'),
            //             'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')',
            //         ));
            //     }
            // }

            // if ($this->_isAllowedAction('invoice') && $order->canInvoice()) {
            //     $_label = $order->getForcedDoShipmentWithInvoice() ?
            //         Mage::helper('sales')->__('Invoice and Ship') :
            //         Mage::helper('sales')->__('Invoice');
            //     $block->_addButton('order_invoice', array(
            //         'label' => $_label,
            //         'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
            //         'class' => 'go'
            //     ));
            // }

            // if ($this->_isAllowedAction('ship') && $order->canShip()
            //     && !$order->getForcedDoShipmentWithInvoice()
            // ) {
            //     $block->_addButton('order_ship', array(
            //         'label' => Mage::helper('sales')->__('Ship'),
            //         'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
            //         'class' => 'go'
            //     ));
            // }

            // if($this->_checkVersion())
            //     if ($this->_isAllowedAction('reorder')
            //         && $block->helper('sales/reorder')->isAllowed($order->getStore())
            //         && $order->canReorder()
            //     ) {
            //         $block->_addButton('order_reorder', array(
            //             'label' => Mage::helper('sales')->__('Reorder'),
            //             'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
            //             'class' => 'go'
            //         ));
            //     }
            // else
            //     if ($this->_isAllowedAction('reorder')
            //         && $block->helper('sales/reorder')->isAllow($order->getStore())
            //         && $order->canReorder()
            //     ) {
            //         $block->_addButton('order_reorder', array(
            //             'label' => Mage::helper('sales')->__('Reorder'),
            //             'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
            //             'class' => 'go'
            //         ));
            //     }
            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_button'))
                $block->_addButton('PDF Invoice', array(
                        'label' => Mage::helper('sales')->__('PDF Invoice'),
                        'class' => 'pdf_invoice_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfInvoiceUrl() . '\')',
                    )
                );
            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_packing_sheet_button'))
                $block->_addButton('PDF Packing Ship', array(
                        'label' => Mage::helper('sales')->__('PDF Packing Sheet'),
                        'class' => 'pdf_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfShippingUrl() . '\')',
                    )
                );
            
            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_invoice_and_packing_sheet_button'))
                $block->_addButton('PDF Invoice & Packing', array(
                        'label' => Mage::helper('sales')->__('PDF Invoice and Packing Sheet'),
                        'class' => 'pdf_invoice_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfInvoiceShippingUrl() . '\')',
                    )
                );
            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_pdf_zebra_label_button'))
                $block->_addButton('Zebra Label', array(
                        'label' => Mage::helper('sales')->__('PDF Zebra Label'),
                        'class' => 'pdf_invoice_packingsheet_button',
                        'onclick' => 'setLocation(\'' . $this->getPdfZebraLabelUrl() . '\')',
                    )
                );
            if (Mage::getStoreConfig('pickpack_options/button_invoice/order_resend_email_button'))    
                $block->_addButton('Resend email', array(
                            'label' => Mage::helper('sales')->__('Resend Email'),
                            'class' => 'send_notification',
                            'onclick' => 'setLocation(\'' . $this->getResendMailUrl() . '\')',
                        )
                    );

        }*/
        return $this;
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
    protected function getOrder()
    {
        return Mage::registry('sales_order');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    protected function getOrderId()
    {
        $order = $this->getOrder();
        if($order != null)
            return $order->getId();
        else
            return null;
    }

    public function getHeaderText()
    {
        if (($this->getOrder() != null) && $_extOrderId = $this->getOrder()->getExtOrderId()) {
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

    protected function getEditUrl()
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
    // public function getBackUrl()
    // {
    //     if ($this->getOrder()->getBackUrl()) {
    //         return $this->getOrder()->getBackUrl();
    //     }

    //     return $this->getUrl('*/*/');
    // }

    public function getReviewPaymentUrl($action)
    {
        return $this->getUrl('*/*/reviewPayment', array('action' => $action));
    }
    
    public function getResendMailUrl()
    {
        return $this->getUrl('*/pickpack_sales_order/resendmail/');
    }

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'action_menu', $store = null){
        $value = trim(Mage::getStoreConfig('pickpack_options/'.$group.'/'.$field, $store));

        if(strstr($field,'_color') !== FALSE)
        {
            if($value != 0 && $value != 1)
            {
                $value = checkColor($value);
            }
        }

        if($value == '')
        {
            return $default;
        }
        else
        {
            if($field == 'csv_field_separator' && $value == ',') return $value;
            // if(preg_match('~[a-zA-Z0-9]~',$value) === true && (strpos($value, ',') !== false) && (strpos($default, ',') !== false))// && (strpos($value, "\n")))
            if(($value!=='') && (strpos($value, ',') !== false) && (strpos($default, ',') !== false))// && (strpos($value, "\n")))
            {
                $values = explode(",", $value);
                $defaults = explode(",", $default);

                if($add_default===true)
                {
                    $value = '';
                    $count = 0;
                    $default_count = count($defaults);
                    foreach($defaults as $i => $v)
                    {
                        //if($value != '') $value .= ',';
                        if(($count != ($default_count)) && ($count != 0)) $value .= ',';
                        if(isset($values[$i]) && $values[$i] != '') $value .= ($values[$i]+$defaults[$i]);
                        else $value .= $v;
                        $count ++;
                    }
                }
                else
                {
                    $value = '';
                    $count = 0;
                    $default_count = count($defaults);
                    foreach($defaults as $i => $v)
                    {
                        //if($value != '') $value .= ',';
                        if(($count != ($default_count)) && ($count != 0)) $value .= ',';
                        if(isset($values[$i]) && $values[$i] != '') $value .= $values[$i];
                        else $value .= $v;
                        $count ++;
                    }
                }
            }
            else
            {
                $value = ($add_default) ? ($value + $default) : $value;
            }
            return $value;
        }
    }

}