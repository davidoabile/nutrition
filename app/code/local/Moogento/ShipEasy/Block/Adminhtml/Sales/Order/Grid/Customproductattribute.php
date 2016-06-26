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
* File        Contact.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Contact extends Mage_Adminhtml_Block_Template
{
    const XML_PATH_DEFAULT_EMAIL_SUBJECT = 'moogento_shipeasy/email_to/default_subject';
    const XML_PATH_DEFAULT_EMAIL_BODY = 'moogento_shipeasy/email_to/default_body';

    const XML_PATH_ALLOW_COMMENT = 'moogento_shipeasy/grid/contact_allow_comment';
    const XML_PATH_ALLOW_EMAIL = 'moogento_shipeasy/grid/contact_allow_email';
    const XML_PATH_ALLOW_GMAIL = 'moogento_shipeasy/grid/contact_allow_gmail';

    protected function _allowComment()
    {
        return (bool)Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_COMMENT);
    }

    protected function _allowEmail()
    {
        return (bool)Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_EMAIL);
    }

    protected function _allowGmail()
    {
        return (bool)Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_GMAIL);
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/contact.phtml');
    }

    protected function _getCommentUrl()
    {
        return $this->getUrl('*/sales_order_comment/form', array('order_id' => $this->getOrder()->getId()));
    }

    protected function _getEmailBody()
    {
        if ($body = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EMAIL_BODY)) {
            return Mage::helper('moogento_shipeasy/contact')->processEmailBody($body, $this->getOrder());
        }

        return '';
    }

    protected function _getEmailSubject()
    {
        if ($subject = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EMAIL_SUBJECT)) {
            return Mage::helper('moogento_shipeasy/contact')->processEmailSubject($subject, $this->getOrder());
        }

        return '';
    }

    protected function _getMailToUrl()
    {
        $mailTo = $this->_getCustomerEmail();

        $defaultData = array();
        if ($subject = $this->_getEmailSubject()) {
            $defaultData[] = 'subject='.$subject;
        }

        if ($body = $this->_getEmailBody()) {
            $defaultData[] = 'body='.$body;
        }

        if (count($defaultData)) {
            $mailTo .= '?'. implode('&',$defaultData);
        }

        return $mailTo;
    }

    protected function _getGmailLink()
    {
        $link = 'https://mail.google.com/mail/?view=cm&fs=1&tf=1';

        $data = array(
            'to='.$this->_getCustomerEmail()
        );

        if ($subject = $this->_getEmailSubject()) {
            $data[] = 'su='.$subject;
        }

        if ($body = $this->_getEmailBody()) {
            $data[] = 'body='.$body;
        }

        return $link . '&' . implode('&', $data);
    }

    protected function _getCustomerEmail()
    {
        return Mage::getResourceSingleton('moogento_shipeasy/sales_order')
            ->getOrderColumnValue($this->getOrder(), 'customer_email');
    }
}
