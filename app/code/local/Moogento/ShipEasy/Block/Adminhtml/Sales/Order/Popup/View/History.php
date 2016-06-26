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
* File        History.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Popup_View_History
    extends Mage_Adminhtml_Block_Widget
{

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_orderInstance = null;

    public function getOrderInstance()
    {
        if (is_null($this->_orderInstance)) {
            $this->_orderInstance = Mage::getModel('sales/order')->load($this->_getOrderId());

            $billingName = $this->_orderInstance->getCustomerFirstname() . ' ' . $this->_orderInstance->getCustomerLastname();

            if ($this->_orderInstance->getBillingAddress()->getId()) {
                $billingName = $this->_orderInstance->getBillingAddress()->getFirstname() . ' ' .
                    $this->_orderInstance->getBillingAddress()->getFirstname();
            }
            $this->_orderInstance->setData('billing_name', $billingName);

            if ($this->_orderInstance->getShippingAddress()->getId()) {
                $shippingName = $this->_orderInstance->getShippingAddress()->getFirstname() . ' ' .
                    $this->_orderInstance->getShippingAddress()->getFirstname();
                $this->_orderInstance->setData('shipping_name', $shippingName);
            }
        }

        return $this->_orderInstance;
    }

    protected function _prepareLayout()
    {
        $onclick = "submitSzyCommentForm()";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('sales')->__('Submit Comment'),
                'class'     => 'save',
                'onclick'   => $onclick,
            ));
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    public function getAdminComment()
    {
        $comment = '';
        if ($message = Mage::getStoreConfig('moogento_shipeasy/email_to/default_admin_message')) {
            $comment = Mage::helper('moogento_shipeasy/contact')->processMessage($message, $this->getOrderInstance());
        }
        return $comment;
    }

    public function getCustomerComment()
    {
        $comment = '';
        if ($message = Mage::getStoreConfig('moogento_shipeasy/email_to/default_customer_message')) {
            $comment = Mage::helper('moogento_shipeasy/contact')->processMessage($message, $this->getOrderInstance());
        }
        return $comment;
    }

    protected function _getOrderId()
    {
        return $this->getRequest()->getParam('order_id', 0);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/post');
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('moogento/shipeasy/sales/order/popup/comment.phtml');
    }

    public function getOrderLogRecord()
    {
        return Mage::helper('moogento_shipeasy/sales')->getOrderLogRecord(1);
    }

    public function renderLogActionArguments(Moogento_ShipEasy_Model_Sales_Order_Log $logRecord, $action)
    {
        $aString = array();
        foreach ($logRecord->parseActionArguments($action) as $key => $val) {
            $aString[] = "{$key}: {$val}";
        }
        return implode('; ', $aString);
    }

}
