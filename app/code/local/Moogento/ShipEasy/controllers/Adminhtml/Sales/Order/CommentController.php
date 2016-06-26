<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        CommentController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Adminhtml_Sales_Order_CommentController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function deleteAction()
    {
        $response = null;
        if ($order = $this->_initOrder()) {
            try {
                if ($comment = $this->getRequest()->getParam('id', 0)) {
                    $comment = Mage::getModel('sales/order_status_history')->load($comment);
                    if ($comment->getId()) {
                        $comment->delete();
                    }
                }
                $this->loadLayout('empty');
                $this->renderLayout();
            } catch (Exception $e) {
                Mage::logException($e);
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot remove comment.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }


    public function formAction()
    {
        $this->_initLayoutMessages('adminhtml/session');
        $block = $this->getLayout()->createBlock('moogento_shipeasy/adminhtml_sales_order_popup_view_history');
        $this->getResponse()->appendBody(
            $block->toHtml()
        );
    }

    public function postAction()
    {
        $history = $this->getRequest()->getPost('history', array());
        $orderId = 0;
        if (count($history)) {
            $orderId = $history['order_id'];
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order && $order->getId()) {
                try {
                    $adminComment = $history['admin_comment'];
                    if ($adminComment) {
                        $order->addStatusHistoryComment($adminComment)
                            ->setIsVisibleOnFront(false)
                            ->setIsCustomerNotified(false);
                        $order->save();
                    }

                    $comment = $history['history_comment'];
                    if(isset($history['checkbox_comment'])){
                        if ($comment) {						
                            $comment = trim(str_replace(array("\r\n","\r","\n"),'<br>',strip_tags($comment)));
                            $comment = str_replace(' ,',',',$comment);
                            $order->sendOrderUpdateEmail(true, $comment);
                            $order->addStatusHistoryComment($comment)
                                ->setIsVisibleOnFront(true)
                                ->setIsCustomerNotified(true);
                            $order->save();
                        }
                    }
                   
                    $this->_getSession()->addSuccess(Mage::helper('moogento_shipeasy')->__('Comment has been added to order'));
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError(Mage::helper('moogento_shipeasy')->__('Can not add order comment'));
                }
            } else {
                $this->_getSession()->addError(Mage::helper('moogento_shipeasy')->__('Can not load specific order'));
            }
        } else {
            $this->_getSession()->addError(Mage::helper('moogento_shipeasy')->__('Can not find POST data to save order comment'));
        }
        $this->_redirect('*/*/form', array('order_id' => $orderId));
    }
}
