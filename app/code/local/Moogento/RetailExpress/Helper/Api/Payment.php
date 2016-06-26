<?php


class Moogento_RetailExpress_Helper_Api_Payment extends Mage_Core_Helper_Abstract
{
    public function addPayment($paymentData)
    {
        $payment = Mage::getModel('moogento_retailexpress/paymentmethod')->load($paymentData['ID'], 'retail_express_id');
        $payment->addData(array(
            'retail_express_id' => $paymentData['ID'],
            'name' => $paymentData['Name'],
            'status' => $paymentData['Enabled'] == 'true' ? 1 : 0,
            'loyalty_enabled' => $paymentData['LoyaltyEnabled'] == 'true' ? 1 : 0,
            'pos_enabled' => $paymentData['POSEnabled'] == 'true' ? 1 : 0,
            'loyalty_ratio' => (float) $paymentData['LoyaltyRatio'],
        ));
        $payment->save();
    }
} 