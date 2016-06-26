<?php

class Excellence_Fee_Model_Observer {

    public function invoiceSaveAfter(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() + $invoice->getFeeAmount());
            $order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() + $invoice->getBaseFeeAmount());
        }
        return $this;
    }

    public function creditmemoSaveAfter(Varien_Event_Observer $observer) {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getFeeAmount()) {
            $order = $creditmemo->getOrder();
            $order->setFeeAmountRefunded($order->getFeeAmountRefunded() + $creditmemo->getFeeAmount());
            $order->setBaseFeeAmountRefunded($order->getBaseFeeAmountRefunded() + $creditmemo->getBaseFeeAmount());
        }
        return $this;
    }

    public function updatePaypalTotal($evt) {
        $cart = $evt->getPaypalCart();
        if ((float) $cart->getSalesEntity()->getFeeAmount() <= 0) {
            return $this;
        }
        $cart->addItem('Insurance (Freight protection)-', 1, (float) $cart->getSalesEntity()->getFeeAmount());
    }

}
