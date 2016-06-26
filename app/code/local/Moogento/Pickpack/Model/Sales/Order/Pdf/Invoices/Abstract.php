<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    const COMPAT_MODE = false;

    public function getOrder ($orderObject)
    {
        if ($orderObject instanceof Mage_Sales_Model_Order) {
            return $orderObject;
        } else {
            return $orderObject->getOrder();
        }
    }
    
     public function getPdf($input = array(), $orderIds = null)
    {
        if (self::COMPAT_MODE) {
            try {
                $newPdf = new Zend_Pdf();
                $extractor = new Zend_Pdf_Resource_Extractor();

                if (!empty($orderIds)) {
                    $origPdf = $this->renderPdf(null, $orderIds, null, true);
                } else {
                    $origPdf = $this->renderPdf($input, $orderIds, null, true);
                }
                if ($origPdf->getPdfAnyOutput()) {
                    $pdfString = $origPdf->Output('output.pdf', 'S');

                    $tcpdf = Zend_Pdf::parse($pdfString);

                    foreach ($tcpdf->pages as $p) {
                        $newPdf->pages[] = $extractor->clonePage($p);
                    }
                }
                return $newPdf;

            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $this->pages[] = array(
                'instance'    => $this,
                'objectArray' => $input,
                'orderIds'    => $orderIds
            );
            return $this;
        }
    }

    /**
     *
     *
     * @param bool $newSegmentOnly
     * @param null $outputStream
     * @param bool $toFileName
     * @param bool $toBrowser
     *
     * @return bool | string | void
     */
    public function render($newSegmentOnly = false, $outputStream = null, $toFileName = false, $toBrowser = false)
    {
        if (self::COMPAT_MODE) {
            return parent::render($newSegmentOnly, $outputStream);
        } else {
            $pdf = null;
            foreach ($this->pages as $printObjects) {
                if (!empty($printObjects['orderIds'])) {
                    $pdf = $printObjects['instance']->renderPdf(array(), $printObjects['orderIds'], $pdf, true);
                } else {
                    $pdf = $printObjects['instance']->renderPdf($printObjects['objectArray'], null, $pdf, true);
                }
            }

            if ($pdf->getPdfAnyOutput()) {
                if ($toFileName) {
                    if ($toBrowser) {
                        return $pdf->Output($toFileName, Mage::getStoreConfigFlag('sales_pdf/all/allnewwindow') ? 'D' : 'I');
                    } else {
                        return $pdf->Output($toFileName, 'F');
                    }
                } else {
                    return $pdf->Output('output.pdf', 'S');
                }
            } else {
                return false;
            }
        }
    }


    
    /**
     * prepare totals for display
     *
     * @param                                 $order Model
     *
     * @return array
     */
    public function PrepareTotals($orderObject,$storeId,$displaySalesruleTitle=true,$displayTaxAmountWithGrandTotals=true)
    {
        $totals = array();
        $order = $orderObject;//$order;
        $pdfTotals = $this->_getTotalsList($orderObject);
        foreach ($pdfTotals as $pdfTotal) {
            $pdfTotal->setOrder($order)->setSource($orderObject);
            $sortOrder = $pdfTotal->getSortOrder();
            switch ($pdfTotal->getSourceField()){
                case 'subtotal':
                    //Prepare Subtotal
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/subtotal', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $orderObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'key'      => $pdfTotal->getSourceField(),
                                    'text'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'value'    => $orderObject->getSubtotalInclTax(),
                                    'baseAmount'=> $orderObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'key'      => $pdfTotal->getSourceField(),
                                    'text'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'value'    => $orderObject->getSubtotal()
                                        + $orderObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
//                                         - $orderObject->getFoomanSurchargeTaxAmount()
                                        - $orderObject->getShippingTaxAmount()
                                        - $orderObject->getCodTaxAmount(),
                                    'baseAmount'=> $orderObject->getBaseSubtotal()
                                        + $orderObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
//                                         - $orderObject->getBaseFoomanSurchargeTaxAmount()
                                        - $orderObject->getBaseShippingTaxAmount()
                                        - $orderObject->getBaseCodTaxAmount()
                                );
                            }
                        } elseif (Mage::getStoreConfig('tax/sales_display/subtotal', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $orderObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'key'      => $pdfTotal->getSourceField(),
                                    'text'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'value'    => $orderObject->getSubtotalInclTax(),
                                    'baseAmount'=> $orderObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'key'      => $pdfTotal->getSourceField(),
                                    'text'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'value'    => $orderObject->getSubtotal()
                                        + $orderObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
//                                         - $orderObject->getFoomanSurchargeTaxAmount()
                                        - $orderObject->getShippingTaxAmount()
                                        - $orderObject->getCodTaxAmount(),
                                    'baseAmount'=> $orderObject->getBaseSubtotal()
                                        + $orderObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
//                                         - $orderObject->getBaseFoomanSurchargeTaxAmount()
                                        - $orderObject->getBaseShippingTaxAmount()
                                        - $orderObject->getBaseCodTaxAmount()
                                );
                            }
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $orderObject->getSubtotal(),
                                'baseAmount'=> $orderObject->getBaseSubtotal()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                'value'    => $orderObject->getSubtotal(),
                                'baseAmount'=> $orderObject->getBaseSubtotal()
                            );
                        }
                    }
                    break;
                case 'discount_amount':
                    //Prepare Discount
                    //Prepare positive or negative Discount to display with minus sign
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$orderObject->getDiscountAmount()>0)?-1:1;
                        if ($orderObject->getDiscountDescription()) {
                            $label = trim(Mage::helper('sales')->__('Discount').' ('. $orderObject->getDiscountDescription()). '):';
                        } else {
                            $label = trim(Mage::helper('sales')->__('Discount') . ' ' . $order->getCouponCode()) . ':';
                        }
                        if ($displaySalesruleTitle) {
                            if ($order->getCouponCode()) {
                                $salesruleTitles = array();
                                foreach (explode(',', $order->getCouponCode()) as $couponCode) {
                                    $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                                    if ($coupon) {
                                        $salesrule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
                                        if ($salesrule->getStoreLabel($storeId)) {
                                            $salesruleTitles[] = $salesrule->getStoreLabel($storeId);
                                        } elseif ($salesrule->getName()) {
                                            $salesruleTitles[] = $salesrule->getName();
                                        }
                                    }
                                }
                                if (!empty($salesruleTitles)) {
                                    $label = implode(' ', $salesruleTitles) . ':';
                                }
                            }
                        }
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                    'key' => $pdfTotal->getSourceField(),
                                    'text'=> $label,
                                    'value'=>$sign*$orderObject->getDiscountAmount(),
                                    'baseAmount'=>$sign*$orderObject->getBaseDiscountAmount(),
                                    'discount_code'=>$order->getCouponCode()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $sign * $orderObject->getDiscountAmount(),
                                'baseAmount'=> $sign * $orderObject->getBaseDiscountAmount(),
                                'discount_code'=>$order->getCouponCode()
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $sign * $orderObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $orderObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount,
                                'discount_code'=>$order->getCouponCode()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     => $label,
                                'value'    => $sign * $orderObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $orderObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount,
                                'discount_code'=>$order->getCouponCode()
                            );
                        }
                    }
                    break;
                case 'tax_amount':
                    //Prepare Tax
					$filteredTaxrates = '';
                    if (!$displayTaxAmountWithGrandTotals
                        && strtoupper($pdfTotal->getSortOrder()) != 'NO'
                    ) {
                        if ($orderObject->getTaxAmount() > 0) {
							if(Mage::helper('pickpack')->isInstalled('Fooman_PdfCustomiser')){
	                            $filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($orderObject);
	                            if (Mage::getStoreConfig('tax/sales_display/full_summary', $storeId)
	                                && $filteredTaxrates
	                            ) {
	                                foreach ($filteredTaxrates as $filteredTaxrate) {
	                                    if ((strpos($filteredTaxrate['title'], "%") === false)
	                                        && !is_null($filteredTaxrate['percent'])
	                                    ) {
	                                        $label = $filteredTaxrate['title'] . ' [' . sprintf(
	                                            "%01.2f%%", $filteredTaxrate['percent']
	                                        ) . ']';
	                                    } else {
	                                        $label = $filteredTaxrate['title'];
	                                    }
	                                    if (!is_null($filteredTaxrate['value'])) {
	                                        $label .= ':';
	                                    } else {
	                                        $label .= '&nbsp;';
	                                    }
	                                    $totals[$sortOrder][] = array(
	                                        'text'      => $label,
	                                        'value'     => $filteredTaxrate['value'],
	                                        'baseAmount' => $filteredTaxrate['baseAmount']
	                                    );
	                                }
	                            } else {
	                                $totals[$sortOrder][] = array(
	                                    'key'      => $pdfTotal->getSourceField(),
	                                    'text'     => 'Tax: ',
	                                    'value'    => (float)$orderObject->getTaxAmount(),
	                                    'baseAmount'=> (float)$orderObject->getBaseTaxAmount()
	                                );
	                            }
							}
                        } elseif (
                            Mage::getStoreConfig(
                                'tax/sales_display/zero_tax', $storeId
                            )
                            && (float)$orderObject->getTaxAmount() == 0
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'       => $pdfTotal->getSourceField(),
                                'text'      => 'Tax: ',
                                'value'     => (float)0,
                                'baseAmount' => (float)0
                            );
                        }
                    }
                    break;
                case 'shipping_amount':
                    //Prepare Shipping
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if ($orderObject->getShippingInclTax()) {
                            $shippingAmount = $orderObject->getShippingInclTax() - $orderObject->getShippingTaxAmount();
                            $baseShippingAmount
                                = $orderObject->getBaseShippingInclTax() - $orderObject->getBaseShippingTaxAmount();
                        } else {
                            $shippingAmount = $orderObject->getShippingAmount();
                            $baseShippingAmount = $orderObject->getBaseShippingAmount();
                        }
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'value'    => $shippingAmount
                                    + $orderObject->getShippingTaxAmount(),
                                'baseAmount'=> $baseShippingAmount
                                    + $orderObject->getBaseShippingTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $shippingAmount
                                    + $orderObject->getShippingTaxAmount(),
                                'baseAmount'=> $baseShippingAmount
                                    + $orderObject->getBaseShippingTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $shippingAmount,
                                'baseAmount'=> $baseShippingAmount
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'value'    => $shippingAmount,
                                'baseAmount'=> $baseShippingAmount
                            );
                        }
                    }
                    break;
                case 'adjustment_positive':
                    //Prepare AdjustmentPositive
                    if (
                        $orderObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'      => Mage::helper('sales')->__('Adjustment Refund') . ':',
                            'value'     => $orderObject->getAdjustmentPositive(),
                            'baseAmount' => $orderObject->getBaseAdjustmentPositive()
                        );
                    }                    
                    break;
                case 'adjustment_negative':
                    //Prepare AdjustmentNegative
                    if (
                        $orderObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'      => Mage::helper('sales')->__('Adjustment Fee') . ':',
                            'value'     => $orderObject->getAdjustmentNegative(),
                            'baseAmount' => $orderObject->getBaseAdjustmentNegative()
                        );
                    }                    
                    break;
                case 'surcharge_amount':
                    $amount = $pdfTotal->getAmount();
                    if ($amount != 0) {
                        $totals[$sortOrder][] = array(
                            'key'       => $pdfTotal->getSourceField(),
                            'text'      => Mage::helper('sagepaysuite')->__($pdfTotal->getTitle()) . ':',
                            'value'     => $amount,
                            'baseAmount' => $amount //no base amount available
                        );
                    }

                    break;
               //  case 'fooman_surcharge_amount':
//                     //Prepare Fooman Surcharge
//                     if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
//                         if (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
//                             == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
//                         ) {
//                             $totals[$sortOrder][] = array(
//                                     'key' => $pdfTotal->getSourceField(),
//                                     'text'=>$order->getFoomanSurchargeDescription().':',
//                                     'value'=>$orderObject->getFoomanSurchargeAmount()
//                                         + $orderObject->getFoomanSurchargeTaxAmount(),
//                                     'baseAmount'=>$orderObject->getBaseFoomanSurchargeAmount()
//                                         + $orderObject->getBaseFoomanSurchargeTaxAmount()
//                             );
//                         } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
//                             == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
//                         ) {
//                             $totals[$sortOrder][] = array(
//                                     'key' => $pdfTotal->getSourceField(),
//                                     'text'=>$order->getFoomanSurchargeDescription().':',
//                                     'value'=>$orderObject->getFoomanSurchargeAmount()
//                                         + $orderObject->getFoomanSurchargeTaxAmount(),
//                                     'baseAmount'=>$orderObject->getBaseFoomanSurchargeAmount()
//                                         + $orderObject->getBaseFoomanSurchargeTaxAmount()
//                             );
//                             $totals[$sortOrder][] = array(
//                                     'key' => $pdfTotal->getSourceField(),
//                                     'text'=>$order->getFoomanSurchargeDescription().':',
//                                     'value'=>$orderObject->getFoomanSurchargeAmount(),
//                                     'baseAmount'=>$orderObject->getBaseFoomanSurchargeAmount()
//                             );
//                         } else {
//                             $totals[$sortOrder][] = array(
//                                     'key' => $pdfTotal->getSourceField(),
//                                     'text'=>$order->getFoomanSurchargeDescription().':',
//                                     'value'=>$orderObject->getFoomanSurchargeAmount(),
//                                     'baseAmount'=>$orderObject->getBaseFoomanSurchargeAmount()
//                             );
//                         }
//                     }                    
//                     break;
                case 'customer_credit_amount':
                    //Prepare MageWorx Customer Credit
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = $pdfTotal->getAmountPrefix() == '-'?-1:1;
                        $sortOrder = $pdfTotal->getSortOrder();
                        $totals[$sortOrder][] = array(
                            'key' => $pdfTotal->getSourceField(),
                            'text'=>Mage::helper('customercredit')->__('Internal Credit').':',
                            'value'=>$sign*$orderObject->getCustomerCreditAmount(),
                            'baseAmount'=>$sign*$orderObject->getBaseCustomerCreditAmount()
                        );
                    }
                    break;
                case 'shipping_and_handling_tax':
                    if(strtoupper($pdfTotal->getSortOrder())!='NO'){
                        $taxHelper = Mage::helper('tax');
                        if (method_exists($taxHelper, 'getShippingTax')) {
                            $shippingTaxes = $taxHelper->getShippingTax($orderObject);
                            if ($shippingTaxes) {
                                foreach ($shippingTaxes as $shippingTax) {
                                    $totals[$sortOrder][] = array(
                                        'key'      => $shippingTax['title'],
                                        'text'     => str_replace(
                                            ' &amp; ', ' & ', $shippingTax['title']
                                        ) . ':',
                                        'value'    =>  $shippingTax['tax_amount'],
                                        'baseAmount'=> $shippingTax['base_tax_amount']
                                    );
                                }
                            }
                        }
                    }
                    break;

                case 'customer_balance_amount':
                    //Prepare Enterprise Store Credit
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && (float)$orderObject->getCustomerBalanceAmount() !=0) {
                        $sign = ((float)$orderObject->getCustomerBalanceAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'     => str_replace(
                                ' &amp; ', ' & ', Mage::helper('enterprise_giftcardaccount')->__('Store Credit')
                            ) . ':',
                            'value'    => $sign * $orderObject->getCustomerBalanceAmount(),
                            'baseAmount'=> $sign * $orderObject->getBaseCustomerBalanceAmount()
                        );
                    }
                    break;
                case 'gift_cards_amount':
                    //Prepare Enterprise Gift Cards
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $orderObject->getGiftCardsAmount()!=0) {
                        $sign = ((float)$orderObject->getGiftCardsAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'     =>
                            str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftcardaccount')->__('Gift Cards'))
                                . ':',
                            'value'    => $sign * $orderObject->getGiftCardsAmount(),
                            'baseAmount'=> $sign * $orderObject->getBaseGiftCardsAmount()
                        );
                    }
                    break;
                case 'gw_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $orderObject->getGwPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order')) . ':',
                                'value'    => $orderObject->getGwPrice()
                                    + $order->getGwTaxAmount(),
                                'baseAmount'=> $orderObject->getGwBasePrice()
                                    + $order->getGwBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $orderObject->getGwPrice()
                                    + $order->getGwTaxAmount(),
                                'baseAmount'=> $orderObject->getGwBasePrice()
                                    + $order->getGwBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $orderObject->getGwPrice(),
                                'baseAmount'=> $orderObject->getGwBasePrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Order')) . ':',
                                'value'    => $orderObject->getGwPrice(),
                                'baseAmount'=> $orderObject->getGwBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_items_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $orderObject->getGwItemsPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items')) . ':',
                                'value'    => $orderObject->getGwItemsPrice()
                                    + $order->getGwItemsTaxAmount(),
                                'baseAmount'=> $orderObject->getGwItemsBasePrice()
                                    + $order->getGwItemsBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $orderObject->getGwItemsPrice()
                                    + $order->getGwItemsTaxAmount(),
                                'baseAmount'=> $orderObject->getGwItemsBasePrice()
                                    + $order->getGwItemsBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $orderObject->getGwItemsPrice(),
                                'baseAmount'=> $orderObject->getBaseItemsGwPrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping for Items')) . ':',
                                'value'    => $orderObject->getGwItemsPrice(),
                                'baseAmount'=> $orderObject->getGwItemsBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_card_price':
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $orderObject->getGwCardPrice() !=0) {
                        if (Mage::getStoreConfig('tax/sales_display/printed_card', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card')) . ':',
                                'value'    => $orderObject->getGwCardPrice()
                                    + $order->getGwCardTaxAmount(),
                                'baseAmount'=> $orderObject->getGwCardBasePrice()
                                    + $order->getGwCardBaseTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/printed_card', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $orderObject->getGwCardPrice()
                                    + $order->getGwCardTaxAmount(),
                                'baseAmount'=> $orderObject->getGwCardBasePrice()
                                    + $order->getGwCardBaseTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $orderObject->getGwCardPrice(),
                                'baseAmount'=> $orderObject->getGwCardBasePrice()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Printed Card')) . ':',
                                'value'    => $orderObject->getGwCardPrice(),
                                'baseAmount'=> $orderObject->getGwCardBasePrice()
                            );
                        }
                    }
                    break;
                case 'gw_combined':
                    $gwCombined= $orderObject->getGwPrice() + $orderObject->getGwItemsPrice() + $orderObject->getGwCardPrice();
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && $gwCombined != 0) {
                        $baseGwCombined= $orderObject->getGwBasePrice() + $orderObject->getGwItemsBasePrice() + $orderObject->getGwCardBasePrice();

                        $GwTaxCombined = $orderObject->getGwTaxAmount() + $orderObject->getGwItemsTaxAmount() + $orderObject->getGwCardTaxAmount();
                        $baseTaxGwCombined = $orderObject->getGwBaseTaxAmount() + $orderObject->getGwItemsBaseTaxAmount() + $orderObject->getGwCardBaseTaxAmount();
                        if (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping')) . ':',
                                'value'    => $gwCombined + $GwTaxCombined,
                                'baseAmount'=> $baseGwCombined + $baseTaxGwCombined
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/gift_wrapping', $storeId)
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'value'    => $gwCombined + $GwTaxCombined,
                                'baseAmount'=> $baseGwCombined + $baseTaxGwCombined
                            );
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'value'    => $gwCombined,
                                'baseAmount'=> $baseGwCombined
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'key'      => $pdfTotal->getSourceField(),
                                'text'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping')) . ':',
                                'value'    => $gwCombined,
                                'baseAmount'=> $baseGwCombined
                            );
                        }
                    }
                    break;
                case 'reward_currency_amount':
                    //Prepare Enterprise paid from reward points
                    if (strtoupper($pdfTotal->getSortOrder())!='NO' && (float)$orderObject->getRewardCurrencyAmount() !=0) {
                        $sign = ((float)$orderObject->getRewardCurrencyAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('enterprise_reward')->formatReward($orderObject->getRewardPointsBalance())
                            ) . ':',
                            'value'    => $sign * $orderObject->getRewardCurrencyAmount(),
                            'baseAmount'=> $sign * $orderObject->getBaseRewardCurrencyAmount()
                        );
                    }
                    break;
                case 'money_for_points':
                    //Aheadworks Points extension
                    if ($orderObject->getMoneyForPoints() != 0) {
                        $sign = ((float)$orderObject->getMoneyForPoints() > 0) ? -1 : 1;
                        $totals[$sortOrder][] = array(
                            'key'       => $pdfTotal->getSourceField(),
                            'text'      => str_replace(
                                    ' &amp; ',
                                    ' & ',
                                    Mage::helper('points')->__('%s', Mage::helper('points/config')->getPointUnitName())
                                ) . ':',
                            'value'     => $sign * $order->getMoneyForPoints(),
                            'baseAmount' => $sign * $order->getBaseMoneyForPoints()
                        );
                    }
                    break;
                case 'giftcert_amount':
                    //Unirgy Giftcert Extension
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$orderObject->getGiftcertAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'key'      => $pdfTotal->getSourceField(),
                            'text'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('ugiftcert')->__('Gift Certificates (%s)', $order->getGiftcertCode())
                            ) . ':',
                            'value'    => $sign * $order->getGiftcertAmount(),
                            'baseAmount'=> $sign * $order->getBaseGiftcertAmount()
                        );
                    }
                    break;
                case 'klarnaPaymentModule':
                    //Prepare Klarna-Faktura Invoice fee(separate extension by trollweb_kreditor)
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $klarnaAmounts = $pdfTotal->getAmount();
                        $totals[$sortOrder][] = array(
                            'key'       => $pdfTotal->getSourceField(),
                            'text'      => $pdfTotal->getTitle() . ':',
                            'value'     => $klarnaAmounts['incl'],
                            'baseAmount' => Mage::app()->getStore()->roundPrice(
                                $klarnaAmounts['incl'] * $order->getBaseToOrderRate()
                            )
                        );
                    }
                    break;
                case 'msp_cashondelivery':
                    //Prepare MSP_CashOnDelivery
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $amount = $order->getMspCashondelivery();
                        $totals[$sortOrder][] = array(
                            'key'       => $pdfTotal->getSourceField(),
                            'text'      => Mage::helper('msp_cashondelivery')->__('Cash On Delivery') . ':',
                            'value'     => $amount,
                            'baseAmount' => Mage::app()->getStore()->roundPrice(
                                $amount * $order->getBaseToOrderRate()
                            )
                        );
                    }
                    break;
                case 'cod_fee':
                    //Prepare Phoenix_CashOnDelivery
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder()) != 'NO') {
                        $amount = $order->getCodFee();
                        $totals[$sortOrder][] = array(
                            'key'       => $pdfTotal->getSourceField(),
                            'text'      => Mage::helper('phoenix_cashondelivery')->__('Cash On Delivery') . ':',
                            'value'     => $amount,
                            'baseAmount' => Mage::app()->getStore()->roundPrice(
                                $amount * $order->getBaseToOrderRate()
                            )
                        );
                    }                    
                    break;                    
                case 'customer_balance_total_refunded':
                case 'customer_bal_total_refunded':
                    //dealt with separately
                    break;
                case 'grand_total':
                    //dealt with separately
                    break;                    
                default:
                    //unknown total
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $tmpPdfTotalAmounts = $pdfTotal->getTotalsForDisplay();
                        if (isset($tmpPdfTotalAmounts['value'])) {
                            $tmpPdfTotalAmount = $tmpPdfTotalAmounts['value'];
                            $sign = ($tmpPdfTotalAmount > 0) ? 1 : -1;
                            $totals[$sortOrder][] = array(
                                'key'       => $pdfTotal->getSourceField(),
                                'text'      => $pdfTotal->getTitle(),
                                'value'     => $sign * $tmpPdfTotalAmount,
                                'baseAmount' => Mage::app()->getStore()->roundPrice(
                                    $sign * $tmpPdfTotalAmount * $order->getBaseToOrderRate()
                                )
                            );
                        } elseif (is_array($tmpPdfTotalAmounts)) {
                            foreach ($tmpPdfTotalAmounts as $tmpPdfTotalAmount) {
                                if (Mage::helper('core')->isModuleEnabled('TBT_Rewards')
                                    && $tmpPdfTotalAmount['text'] == Mage::helper('rewards')->__("Item Discounts")
                                ) {
                                    $tmpTotalAmount = $orderObject->getRewardsDiscountAmount();
                                    $tmpBaseTotalAmount = Mage::app()->getStore()->roundPrice(
                                        $tmpTotalAmount * $order->getBaseToOrderRate()
                                    );
                                    $tmpPdfTotalAmount['text'] = $tmpPdfTotalAmount['text'] . ': ';
                                } elseif (method_exists(get_class($pdfTotal), 'getAmount') && !is_array($pdfTotal->getAmount()) && !is_object($pdfTotal->getAmount())) {
                                    $tmpTotalAmount = $pdfTotal->getAmount();
                                    $tmpBaseTotalAmount = Mage::app()->getStore()->roundPrice(
                                        $tmpTotalAmount * $order->getBaseToOrderRate()
                                    );
                                    $label = trim($tmpPdfTotalAmount['label']);
                                    if (substr ( $label , strlen($label)-1 ) === ":"){
                                        $tmpPdfTotalAmount['text'] = $label;
                                    }else{
                                        $tmpPdfTotalAmount['text'] = $label.':';
                                    }
                                } else {
                                    $tmpTotalAmount = $tmpPdfTotalAmount['value'];
                                    if (!isset($tmpPdfTotalAmount['base_amount'])) {
                                        $tmpBaseTotalAmount = $tmpPdfTotalAmount['base_amount'];
                                    } else {
                                        //since the amount above is already converted to a string we can't convert
                                        $tmpBaseTotalAmount = $tmpPdfTotalAmount['value'];
                                    }
                                }
                                if ($tmpTotalAmount != 0) {
                                    $totals[$sortOrder][] = array(
                                        'key'      =>  $pdfTotal->getSourceField(),
                                        'text'      => $tmpPdfTotalAmount['text'],
                                        'value'     => $tmpTotalAmount,
                                        'baseAmount' => $tmpBaseTotalAmount
                                    );
                                }
                            }
                        }
                    }
                    break;
            }
        }

        //support Mico Rushprocessing
        if ((float)$order->getMicoRushprocessingprice() > 0) {
            $totals[$sortOrder][] = array(
                'key' => $pdfTotal->getSourceField(),
                'text'=>'Product &amp; Packaging:',
                'value'=>(float)$order->getMicoRushprocessingprice(),
                'baseAmount'=>(float)$order->getMicoRushprocessingprice()
            );
        }
        
        //support payment fee by XIB
        //use same settings as shipping (total does not provide separate settings)       
        if ((float)$orderObject->getXibpaymentsFee()) {
            $xibTotal = Mage::helper('xibpayments/pdfcustomiser')->appendTotals(
                $totals[$sortOrder], $orderObject, $order, $storeId
            );
            $xibTotal['key'] = 'xibfee';
            $totals[550][] = $xibTotal;
        }

        //Prepare Cash on Delivery
        //use same settings as shipping (total does not provide separate settings)
        /*
         if ((float)$orderObject->getCodFee()) {
            if (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
            ) {
                $totals[550][] = array(
                    'key'      => 'cashondelivery',
                    'text'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $storeId)
                    ) . ':',
                    'value'    => $orderObject->getCodFee() + $order->getCodTaxAmount(),
                    'baseAmount'=> $orderObject->getBaseCodFee() + $order->getBaseCodTaxAmount()
                );
            } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $storeId)
                == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
            ) {
                $totals[550][] = array(
                    'key'      => 'cashondelivery',
                    'text'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $storeId)
                    ) . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                    'value'    => $orderObject->getCodFee() + $order->getCodTaxAmount(),
                    'baseAmount'=> $orderObject->getBaseCodFee() + $order->getBaseCodTaxAmount()
                );
                $totals[550][] = array(
                    'key'      => 'cashondelivery',
                    'text'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $storeId)
                    ) . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                    'value'    => $orderObject->getCodFee(),
                    'baseAmount'=> $orderObject->getBaseCodFee()
                );
            } else {
                $totals[550][] = array(
                    'key'      => 'cashondelivery',
                    'text'     =>
                    str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $storeId)
                    ) . ':',
                    'value'    => $orderObject->getCodFee(),
                    'baseAmount'=> $orderObject->getBaseCodFee()
                );
            }
        }*/

        //Grand Total
        $grandTotals = array();
        
        if (Mage::getStoreConfigFlag('sales_pdf/all/allonly1grandtotal', $storeId)) {
            $grandTotals[] = array(
                'key'      => 'grand_total',
                'text'      => Mage::helper('sales')->__('Grand Total ') . ':',
                'value'     => $orderObject->getGrandTotal(),
                'baseAmount' => $orderObject->getBaseGrandTotal(),
                'bold'       => true
            );            
        } elseif (Mage::getStoreConfig('tax/sales_display/grandtotal', $storeId)) {
            $grandTotals[] = array(
                'key'      => 'grand_total',
                'text'      => Mage::helper('sales')->__('Grand Total')
                    . ' (' . Mage::helper('tax')->__('Excl. Tax') . '):',
                'value'     => $orderObject->getGrandTotal() - $orderObject->getTaxAmount(),
                'baseAmount' => $orderObject->getBaseGrandTotal() - $orderObject->getBaseTaxAmount(),
                'bold'       => true
            );
            if ((float)$orderObject->getTaxAmount() > 0) {
				$filteredTaxrates = '';
				if(Mage::helper('pickpack')->isInstalled('Fooman_PdfCustomiser')){
                	$filteredTaxrates = Mage::helper('pdfcustomiser')->getCalculatedTaxes($orderObject);
				}
                //Magento loses information of tax rates if an order is split into multiple invoices
                //so only display summary if both tax amounts equal
                if (Mage::getStoreConfig('tax/sales_display/full_summary', $storeId)
                    && $filteredTaxrates
                ) {
                    foreach ($filteredTaxrates as $filteredTaxrate) {
                        $grandTotals[] = array(
                            'key'      => 'tax_amount',
                            'text'      => $filteredTaxrate['title'] . ':',
                            'value'     => (float)$filteredTaxrate['value'],
                            'baseAmount' => (float)$filteredTaxrate['baseAmount'],
                            'bold'       => false
                        );
                    }
                } else {
                    $grandTotals[] = array(
                        'key'      => 'tax_amount',
                        'text'     => 'Tax: ',
                        'value'    => (float)$orderObject->getTaxAmount(),
                        'baseAmount'=> (float)$orderObject->getBaseTaxAmount(),
                        'bold'      => false
                    );
                }
            } elseif (Mage::getStoreConfig('tax/sales_display/zero_tax', $storeId)) {
                    $grandTotals[] = array(
                        'key'      => 'tax_amount',
                        'text'     => 'Tax: ',
                        'value'    => 0,
                        'baseAmount'=> 0,
                        'bold'      => false
                    );
            }
            $grandTotals[] = array(
                    'key'      => 'grand_total',
                    'text'=> Mage::helper('sales')->__('Grand Total'). ' ('.Mage::helper('tax')->__('Incl. Tax').'):',
                    'value'    => $orderObject->getGrandTotal(),
                    'baseAmount'=> $orderObject->getBaseGrandTotal(),
                    'bold'      => true
            );
        } else {
            $grandTotals[] = array(
                'key'      => 'grand_total',
                'text'     => Mage::helper('sales')->__('Grand Total') . ':',
                'value'    => $orderObject->getGrandTotal(),
                'baseAmount'=> $orderObject->getBaseGrandTotal(),
                'bold'      => true
            );
        }

        //Enterprise output refunded to store credit
        if ((float)$orderObject->getCustomerBalanceTotalRefunded()) {
            $grandTotals[] = array(
                'key'      => 'customer_balance_total_refunded',
                'text'     => Mage::helper('enterprise_giftcardaccount')->__('Refunded to Store Credit') . ':',
                'value'    => $orderObject->getCustomerBalanceTotalRefunded(),
                'baseAmount'=> $orderObject->getCustomerBalanceTotalRefunded(),
                'bold'      => true
            );
        }
        
        $totalsSorted = array();
        foreach ($totals as $sortOrder) {
            foreach ($sortOrder as $total) {
                $formattedTotal = $total;
//                 $formattedTotal['text'] = htmlentities($formattedTotal['text'], ENT_QUOTES, 'UTF-8', false);
                $formattedTotal['amount_default'] = $this->formatPrice( $order, $total['value']);
                $formattedTotal['value'] = $this->formatPrice( $order, $total['value']);
                $formattedTotal['base_amount'] = $this->formatPrice( $order, $total['baseAmount'], 'base');
                $totalsSorted['totals'][] = $formattedTotal;
            }           
        }        
        foreach ($grandTotals as $total) {
            $formattedTotal = $total;
//             $formattedTotal['text'] = htmlentities($formattedTotal['text'], ENT_QUOTES, 'UTF-8', false);
            $formattedTotal['amount_default'] = $this->formatPrice( $order, $total['value']);
            $formattedTotal['value'] = $this->formatPrice( $order, $total['value']);
            $formattedTotal['base_amount'] = $this->formatPrice($order, $total['baseAmount'], 'base');
            $totalsSorted['grand_totals'][] = $formattedTotal;
        }
        if (!isset($totalsSorted['totals'])) {
            $totalsSorted['totals'] = array();
        }
        $transport = new Varien_Object();
        $transport->setTotals($totalsSorted);
        return $transport->getTotals();
    }

    /**
     * format the price according to locale settings
     *
     * @param      isRtl
     * @param      $order
     * @param      $price
     * @param null $currency
     *
     * @return string
     */
    public function formatPrice($order, $price, $currency=null,$isRtl=true)
    {
    	return $price;
        if (is_null($price)) {
            return '';
        }
        $price = sprintf("%F", $price);
        if ($isRtl) {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            }
        } else {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array());
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array());
            }
        }
        return $price;
    }
    
}
