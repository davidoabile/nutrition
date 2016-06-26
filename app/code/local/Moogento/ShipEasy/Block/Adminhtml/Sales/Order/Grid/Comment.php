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
 * File        Comment.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Comment extends Mage_Adminhtml_Block_Template
{
    const XML_PATH_TRUNCATE = 'moogento_shipeasy/grid/admin_comments_truncate';
    const XML_PATH_TEMPLATE = 'moogento/shipeasy/sales/order/grid/comment.phtml';
    const XML_PATH_CREATED_AT = 'moogento_shipeasy/grid/szy_created_at_format';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::XML_PATH_TEMPLATE);
    }

    protected function _getComments()
    {
        $maxCount    = Mage::getStoreConfig('moogento_shipeasy/grid/admin_comments_max_count');
        $maxCount    = ($maxCount) ? $maxCount : 1000;
        $displayed   = 0;
        $comments    = array();
        $displayMode = Mage::getStoreConfig('moogento_shipeasy/grid/admin_comments_display');

        foreach ($this->getOrder()->getStatusHistoryCollection() as $comment) {
            if ($displayed >= $maxCount) {
                break;
            }
            if ($displayMode == Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Comment_Display::ADMIN_ONLY
                && $comment->getData('is_visible_on_front')
            ) {
                continue;
            }
            if ($displayMode == Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Comment_Display::FRONTEND_ONLY
                && !$comment->getData('is_visible_on_front')
            ) {
                continue;
            }

            if ($comment->getComment()) {
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/admin_comments_filter')) {
                    $stop  = false;
                    $words = explode(',', Mage::getStoreConfig('moogento_shipeasy/grid/admin_comments_filter_words'));
                    foreach ($words as $word) {
                        if (strpos($comment->getComment(), $word) !== false) {
                            $stop = true;
                        }
                    }
                    if ($stop) {
                        continue;
                    }
                }
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/admin_comments_filter_labels')) {
                    $commentText = trim(strip_tags($comment->getComment()));
                    if (substr($commentText, -1) == ':') {
                        continue;
                    }
                }

                $displayed++;
                $comments[] = array(
                    'datetime'  => $comment->getCreatedAtDate(),
                    'text'      => $this->_getTruncatedComment($comment->getComment(), 'trim'),
                    'text_full' => $this->_getTruncatedComment($comment->getComment(), 'full'),
                );
            }
        }

	    if (Mage::helper('moogento_core')->isInstalled('TM_FireCheckout') && $displayed < $maxCount) {
			$orderOneStep = Mage::getModel('sales/order')->load($this->getOrder()->getId(),'entity_id');
			if ($orderOneStep->getId()) {
				$displayed++;
				$comments[] = array(
					'text'      => $this->_getTruncatedComment($orderOneStep->getFirecheckoutCustomerComment(), 'trim'),
					'text_full' => $this->_getTruncatedComment($orderOneStep->getFirecheckoutCustomerComment(), 'full'),
				);
			}
		}

        if (Mage::helper('moogento_core')->isInstalled('MW_Onestepcheckout') && $displayed < $maxCount) {
            $orderOneStep = Mage::getModel('onestepcheckout/onestepcheckout')->load($this->getOrder()->getId(), 'sales_order_id');
            if ($orderOneStep->getId()) {
                $displayed++;
                $comments[] = array(
                    'text'      => $this->_getTruncatedComment($orderOneStep->getMwCustomercommentInfo(), 'trim'),
                    'text_full' => $this->_getTruncatedComment($orderOneStep->getMwCustomercommentInfo(), 'full'),
                );
            }
        }

        if (Mage::helper('moogento_core')->isInstalled('Idev_OneStepCheckout') && $displayed < $maxCount) {
            $fullOrder = Mage::getResourceModel('sales/order_collection')
                             ->addFieldToSelect('onestepcheckout_customercomment')
                             ->addFieldToSelect('onestepcheckout_customerfeedback')
                             ->addFieldToFilter('entity_id', $this->getOrder()->getId())
                             ->getFirstItem();
            if ($fullOrder->getOnestepcheckoutCustomercomment() && $displayed < $maxCount) {
                $displayed++;
                $comments[] = array(
                    'text'      => $this->_getTruncatedComment($fullOrder->getOnestepcheckoutCustomercomment(), 'trim'),
                    'text_full' => $this->_getTruncatedComment($fullOrder->getOnestepcheckoutCustomercomment(), 'full'),
                );
            }
            if ($fullOrder->getOnestepcheckoutCustomerfeedback() && $displayed < $maxCount) {
                $displayed++;
                $comments[] = array(
                    'text'      => $this->_getTruncatedComment($fullOrder->getOnestepcheckoutCustomerfeedback(),
                        'trim'),
                    'text_full' => $this->_getTruncatedComment($fullOrder->getOnestepcheckoutCustomerfeedback(),
                        'full'),
                );
            }
        }

        return $comments;
    }

    protected function _getFormattedDate($date)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        if (Mage::getStoreConfigFlag(self::XML_PATH_CREATED_AT) != 1) {
            $format = 'dd.MM.yy HH:mm';
        }

        $date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);

        return $date;
    }

    protected function _getTruncatedComment($comment, $length = 'trim')
    {
        $comment = str_replace('<br />', '~', nl2br(trim($comment)));
        // Strip HTML Tags
        $comment = strip_tags($comment);
        // Clean up things like &amp;
        $comment = html_entity_decode($comment);
        // Strip out any url-encoded stuff
        $comment = urldecode($comment);

        $comment = str_ireplace(array('M2E Pro Notes:', '', 'Checkout Message From '), '', $comment);
        $comment = preg_replace('/Because the Order currency is different (.*)$/i', '', $comment);

        // Replace Multiple spaces with single space
        $comment = preg_replace('/ +/', ' ', $comment);
        // Trim the string of leading/trailing space
        $comment = trim($comment);
        $comment = preg_replace('/[ \,@\;~]$/', '', $comment);
        $comment = preg_replace('/ \.$/', '', $comment);
        $comment = preg_replace('/^[~\s\,\.\;~]+/', '', $comment);
        $comment = str_replace(array('~~~', '~~', '~~', '~'), '~', $comment);

        if ($length == 'trim') {
            $truncate_at = Mage::getStoreConfig(self::XML_PATH_TRUNCATE) - 1;
            if ($truncate_at < 5) {
                $truncate_at = 5;
            }
            if ($truncate_at < strlen($comment)) {

                $comment = mb_substr($comment, 0, $truncate_at, 'UTF-8');
                $comment = str_replace('~', '<br />', $comment);

                return $comment;
            }
        }
        $comment = str_replace('~', '&#10;', $comment); //&#13;
        $comment = preg_replace('/Buyer:\s?$/i', '', $comment); //&#13;
        $comment = preg_replace('/&#10;\s?$/i', '', $comment); //&#13;

        return trim($comment);
    }
}
