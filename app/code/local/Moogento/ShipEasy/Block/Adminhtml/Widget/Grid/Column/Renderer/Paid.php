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
 * File        Paid.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Paid
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{

    public function renderCss()
    {
        return str_replace("a-right", "", parent::renderCss());
    }

    public function render(Varien_Object $row)
    {
        $baseCurrencyCode  = $row->getData('base_currency_code');
        $orderCurrencyCode = $row->getData('order_currency_code');

        $basePaid   = $row->getData('base_total_paid');;
        $paid       = $row->getData($this->getColumn()->getIndex());

        if ($baseCurrencyCode != $orderCurrencyCode) {
            $content = Mage::app()->getLocale()->currency($baseCurrencyCode)
                           ->toCurrency($basePaid, array('precision' => 2));
            $content .= '</br>[' . Mage::app()->getLocale()->currency($orderCurrencyCode)
                                       ->toCurrency($paid, array('precision' => 2)) . ']';
        } else {
            $content = Mage::app()->getLocale()->currency($orderCurrencyCode)
                           ->toCurrency($paid, array('precision' => 2));
        }
        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/paid_non_invoiced_amounts')) {
            $grandTotal = $row->getData('grand_total');;
            if ($grandTotal > $basePaid && !is_null($paid)) {
                $content = '<span style="color:orange;">' . $content . '</span>';
            }
            if (is_null($paid)) {
                $content = Mage::app()->getLocale()->currency($baseCurrencyCode)
                               ->toCurrency($grandTotal, array('precision' => 2));
                $content = '<span style="color:red;  background:lightgrey">' . $content . '</span>';
            }
        }

        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/paid_show_paypal_logo')) {
            $payment = $row->getPayment();
            //TODO use these line if error when render paid column
            // $content=Mage::app()->getLocale()->currency($order_currency_code)->toCurrency($paid,array('precision'=>2));  
//          $order = Mage::getModel('sales/order')->load($row->getData('increment_id'), 'increment_id');
//          $payment = $order->getPayment();
//          $paymentMethod = $payment->getMethod();
            if ($payment) {
                $paymentMethod = $payment->getMethod();
            } else {
                $paymentMethod = '';
            }
            if (stripos($paymentMethod, 'paypal') !== false) {
                if (!$payment->getData('last_trans_id')) {
                    return $content;
                }
                $paymentData = $payment->getData();

                $paypalProtectionEligibility = isset($paymentData['additional_information']['paypal_protection_eligibility']) ? strtolower($paymentData['additional_information']['paypal_protection_eligibility']) : '';
                $paypalPayerStatus           = isset($paymentData['additional_information']['paypal_payer_status']) ? strtolower($paymentData['additional_information']['paypal_payer_status']) : '';
                $labelTick                   = '&#x2714;';
                $labelLinebreak              = '&#10;';
                $labelCross                  = '&#x2718;';

                // seller protection : YES
                if ($paypalProtectionEligibility == 'eligible') {
                    if ($paypalPayerStatus == 'verified') {
                        // payer status : YES
                        $paypalLogo = 'szy-PaypalSmallSellerProtectionBuyerVerified.png';
                        $paypalPayerStatusLabel
                                     = $labelTick . ' Yes : Seller Protection' . $labelLinebreak . $labelTick
                                       . ' Yes : Buyer Verified';
                    } else {
                        $paypalLogo = 'szy-PaypalSmallSellerProtectionBuyerNotVerified.png';
                        $paypalPayerStatusLabel
                                    = $labelTick . ' Yes : Seller Protection' . $labelLinebreak . $labelCross
                                      . ' No : Buyer *Not* Verified';
                    }
                } else {
                    // seller protection : NO
                    if ($paypalPayerStatus == 'verified') {
                        $paypalLogo = 'szy-PaypalSmallNoSellerProtectionBuyerVerified.png';
                        $paypalPayerStatusLabel
                                     = $labelCross . ' No : Seller Protection' . $labelLinebreak . $labelTick
                                       . ' Yes : Buyer Verified';
                    } else {
                        $paypalLogo = 'szy-PaypalSmallNoSellerProtectionBuyerNotVerified.png';
                        $paypalPayerStatusLabel
                                     = $labelCross . ' No : Seller Protection' . $labelLinebreak . $labelCross
                                       . ' No : Buyer *Not* Verified';
                    }
                }

                $link = '<a target="_blank" href="https://www.paypal.com/hk/cgi-bin/webscr?cmd=_view-a-trans&id='
                        . $payment->getLastTransId() . '" title="' . $paypalPayerStatusLabel
                        . '" class="szy_paypal_status"><img src="' . $this->getSkinUrl('moogento/shipeasy/images/' . $paypalLogo)
                        . '" height="16" /></a>';
                $content .= $link;
            }
        }

        return $content;
    }

    public function renderExport(Varien_Object $row)
    {
        $base_currency_code = $row->getData('base_currency_code');

        $order_currency_code = $row->getData('order_currency_code');
        $base_paid           = $row->getData('base_total_paid');;
        $paid = $row->getData($this->getColumn()->getIndex());

        if ($base_currency_code != $order_currency_code) {
            $content = Mage::app()->getLocale()->currency($base_currency_code)
                           ->toCurrency($base_paid, array('precision' => 2));
            $content .= '[' . Mage::app()->getLocale()->currency($order_currency_code)
                                  ->toCurrency($paid, array('precision' => 2)) . ']';
        } else {
            $content = Mage::app()->getLocale()->currency($order_currency_code)
                           ->toCurrency($paid, array('precision' => 2));
        }

        return $content;
    }
}