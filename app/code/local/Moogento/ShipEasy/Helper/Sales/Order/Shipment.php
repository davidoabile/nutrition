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
* File        Shipment.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Helper_Sales_Order_Shipment extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TRACK_EMAIL_TEMPLATE               = 'sales_email/shipment/track_template';
    const XML_PATH_TRACK_EMAIL_GUEST_TEMPLATE         = 'sales_email/shipment/guest_track_template';

    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/shipment/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/shipment/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/shipment/copy_method';


    protected function _getEmails($shipment, $configPath)
    {
        $data = Mage::getStoreConfig($configPath, $shipment->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    public function sendNewTracksEmail($shipment, $notifyCustomer = true, $comment = '')
    {
        if (!$notifyCustomer) {
            return $this;
        }

        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $shipment->getStoreId()),
            'store' => $shipment->getStoreId()
        ));

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $order  = $shipment->getOrder();
        $copyTo = $this->_getEmails($shipment, self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $shipment->getStoreId());

        $paymentBlock   = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($order->getStoreId());

        $mailTemplate = Mage::getModel('core/email_template');
        if ($order->getCustomerIsGuest()) {
            $template = Mage::getStoreConfig(self::XML_PATH_TRACK_EMAIL_GUEST_TEMPLATE, $order->getStoreId());
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $template = Mage::getStoreConfig(self::XML_PATH_TRACK_EMAIL_TEMPLATE, $order->getStoreId());
            $customerName = $order->getCustomerName();
        }

        $sendTo[] = array(
            'name'  => $customerName,
            'email' => $order->getCustomerEmail()
        );
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$order->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $order->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'       => $order,
                        'shipment'    => $shipment,
                        'comment'     => $comment,
                        'billing'     => $order->getBillingAddress(),
                        'payment_html'=> $paymentBlock->toHtml(),
                    )
                );
        }

        $translate->setTranslateInline(true);

        Mage::getDesign()->setAllGetOld($currentDesign);

        return $this;
    }
}
