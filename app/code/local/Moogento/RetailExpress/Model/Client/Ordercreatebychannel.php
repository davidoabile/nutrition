<?php

class Moogento_RetailExpress_Model_Client_Ordercreatebychannel {

    public $OrderXML;
    public $ChannelId;
    protected $_orders = array();

    public function __construct() {
        $this->ChannelId = Mage::getStoreConfig('moogento_retailexpress/general/channel_id');
    }

    public function setChannelId($id) {
        $this->ChannelId = (int) $id;
    }

    protected function _formatPrice($price) {
        return number_format($price, 6, '.', '');
    }

    protected function _getOrdersXml() {
        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $ordersXml = $doc->createElement("Orders");

        $excludedProducts = array();
        if (Mage::getStoreConfig('moogento_retailexpress/order/exclude_products')) {
            $excludedProducts = preg_split("/\r?\n/", Mage::getStoreConfig('moogento_retailexpress/order/exclude_products'));
            array_walk($excludedProducts, array($this, 'cleanSkus'));
        }

        foreach ($this->_orders as $order) {
             if((int) $order->getChannelid() > 0 ) {
                $this->setChannelId($order->getChannelid());
            }
            $orderXml = $doc->createElement("Order");
            $dateCreated = date_format(new DateTime($order->getCreatedAt()), 'Y-m-d\TH:i:s.000\Z');

            $item = $doc->createElement("ExternalOrderId");
            $item->appendChild($doc->createTextNode($order->getId()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DateCreated");
            $item->appendChild($doc->createTextNode($dateCreated));
            $orderXml->appendChild($item);

            $item = $doc->createElement("OrderTotal");
            $item->appendChild($doc->createTextNode($this->_formatPrice($order->getGrandTotal())));
            $orderXml->appendChild($item);

            if (Mage::helper('moogento_core')->isInstalled('NWH_SaveInsurance') && $order->getInsurance()) {
                $item = $doc->createElement("FreightTotal");
                $item->appendChild($doc->createTextNode($this->_formatPrice($order->getShippingInclTax() + NWH_SaveInsurance_Block_Adminhtml_Sales_Order_Totals::FEE_AMOUNT)));
                $orderXml->appendChild($item);
            } else {
                $item = $doc->createElement("FreightTotal");
                $item->appendChild($doc->createTextNode($this->_formatPrice($order->getShippingInclTax())));
                $orderXml->appendChild($item);
            }



            $item = $doc->createElement("OrderStatus");
            //$item->appendChild( $doc->createTextNode($order->getStatus()));
            $item->appendChild($doc->createTextNode("Processed"));
            $orderXml->appendChild($item);


            $item = $doc->createElement("ExternalCustomerId");
            $item->appendChild($doc->createTextNode($order->getCustomerId()));
            $orderXml->appendChild($item);

            Mage::helper('moogento_retailexpress/api_customer')->buildOrderXml($doc, $orderXml, $order);

            $item = $doc->createElement("ReceivesNews");
            $item->appendChild($doc->createTextNode(1));
            $orderXml->appendChild($item);

            $orderItems = $doc->createElement("OrderItems");

            $productIdCode = Mage::getStoreConfig('moogento_retailexpress/product/retail_express_id');
            $fixedTaxRate = Mage::getStoreConfig('moogento_retailexpress/order/force_tax_rate');

            $productAdded = 0;
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getProduct()->isComposite())
                    continue;

                $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
                if ($orderItem->getParentItemId() && $orderItem->getParentItem()->getProductType() == 'configurable') {
                    $orderItem = $orderItem->getParentItem();
                }

                if (in_array($orderItem->getSku(), $excludedProducts))
                    continue;


                if ($product->getData($productIdCode)) {
                    $productAdded++;
                    $orderItemXml = $doc->createElement("OrderItem");

                    $item = $doc->createElement("ProductId");
                    $item->appendChild($doc->createTextNode($product->getData($productIdCode)));
                    $orderItemXml->appendChild($item);

                    $item = $doc->createElement("QtyOrdered");
                    $item->appendChild($doc->createTextNode((int) $orderItem->getQtyOrdered()));
                    $orderItemXml->appendChild($item);

                    $item = $doc->createElement("QtyFulfilled");
                    $item->appendChild($doc->createTextNode((int) $orderItem->getQtyOrdered()));
                    $orderItemXml->appendChild($item);

                    if ($orderItem->getPriceInclTax()) {
                        $item = $doc->createElement("UnitPrice");
                        $price = $this->_formatPrice($orderItem->getPriceInclTax() - $orderItem->getDiscountAmount() / $orderItem->getQtyOrdered());
                       /* if ($orderItem->getProductType() === 'bundle') {
                            $bprice = $orderItem->getPriceInclTax() - $orderItem->getDiscountAmount();
                            if($price !== $bprice) {
                                
                            }
                        }*/
                        $item->appendChild($doc->createTextNode($price));
                        $orderItemXml->appendChild($item);
                        $item = $doc->createElement("TaxRateApplied");
                        $item->appendChild($doc->createTextNode($fixedTaxRate ? $fixedTaxRate / 100 : $orderItem->getTaxPercent() / 100));
                        $orderItemXml->appendChild($item);
                    } else if ($orderItem->getParentItemId()) {
                        $item = $doc->createElement("UnitPrice");
                        if ($orderItem->getParentItem()->getProductType() == 'bundle') {
                            $price = $this->_getBundleItemPrice($orderItem);
                        } else {
                            $price = $this->_formatPrice($orderItem->getParentItem()->getPriceInclTax() - $orderItem->getParentItem()->getDiscountAmount() / $orderItem->getParentItem()->getQtyOrdered());
                        }
                        $item->appendChild($doc->createTextNode($price));
                        $orderItemXml->appendChild($item);
                        $item = $doc->createElement("TaxRateApplied");
                        $item->appendChild($doc->createTextNode($fixedTaxRate ? $fixedTaxRate / 100 : $orderItem->getParentItem()->getTaxPercent() / 100));
                        $orderItemXml->appendChild($item);
                    } else {
                        $item = $doc->createElement("UnitPrice");
                        $item->appendChild($doc->createTextNode(0.00));
                        $orderItemXml->appendChild($item);
                        $item = $doc->createElement("TaxRateApplied");
                        $item->appendChild($doc->createTextNode(0));
                        $orderItemXml->appendChild($item);
                    }

                    $orderItems->appendChild($orderItemXml);
                }
            }

            if (!$productAdded) {
                throw new Exception('This order does not have retailExpress products');
            }

            if (Mage::getStoreConfig('moogento_retailexpress/order/free_products')) {
                $productsList = preg_split("/\r?\n/", Mage::getStoreConfig('moogento_retailexpress/order/free_products'));
                foreach ($productsList as $freeProduct) {
                    $productData = explode('|', $freeProduct);
                    $rexId = trim($productData[0]);
                    if ($rexId) {
                        $qty = isset($productData[1]) ? (int) $productData[1] : 1;

                        $orderItemXml = $doc->createElement("OrderItem");

                        $item = $doc->createElement("ProductId");
                        $item->appendChild($doc->createTextNode($rexId));
                        $orderItemXml->appendChild($item);

                        $item = $doc->createElement("QtyOrdered");
                        $item->appendChild($doc->createTextNode($qty));
                        $orderItemXml->appendChild($item);

                        $item = $doc->createElement("QtyFulfilled");
                        $item->appendChild($doc->createTextNode($qty));
                        $orderItemXml->appendChild($item);

                        $item = $doc->createElement("UnitPrice");
                        $item->appendChild($doc->createTextNode(0.00));
                        $orderItemXml->appendChild($item);
                        $item = $doc->createElement("TaxRateApplied");
                        $item->appendChild($doc->createTextNode(0));
                        $orderItemXml->appendChild($item);

                        $orderItems->appendChild($orderItemXml);
                    }
                }
            }

            $orderXml->appendChild($orderItems);

            $orderPayments = $doc->createElement("OrderPayments");
            $orderPayment = $doc->createElement("OrderPayment");

            $item = $doc->createElement("MethodId");
            $item->appendChild($doc->createTextNode(Mage::helper('moogento_retailexpress')->getRetailPaymentMethod($order)));
            $orderPayment->appendChild($item);

            $item = $doc->createElement("Amount");
            $item->appendChild($doc->createTextNode($this->_formatPrice($order->getGrandTotal(), 2)));
            $orderPayment->appendChild($item);

            $item = $doc->createElement("DateCreated");
            $item->appendChild($doc->createTextNode($dateCreated));
            $orderPayment->appendChild($item);

            $orderPayments->appendChild($orderPayment);

            $orderXml->appendChild($orderPayments);

            $ordersXml->appendChild($orderXml);
        }

        $doc->appendChild($ordersXml);

        return $doc->saveXML();
    }

    public function addOrders($orders) {
        $this->_orders = $orders;

        $this->OrderXML = new SoapVar('<ns1:OrderXML><![CDATA[' . $this->_getOrdersXml() . ']]></ns1:OrderXML>', XSD_ANYXML);
    }

    public function addOrder($order) {
        $this->_orders[] = $order;

        $this->OrderXML = new SoapVar('<ns1:OrderXML><![CDATA[' . $this->_getOrdersXml() . ']]></ns1:OrderXML>', XSD_ANYXML);
    }

    public function cleanSkus(&$sku) {
        $sku = trim($sku);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return float
     */
    protected function _getBundleItemPrice($orderItem) {
        
        /*
        $parentItem = $orderItem->getParentItem();
        $parentPrice = ($parentItem->getPriceInclTax() - $parentItem->getDiscountAmount()) / $parentItem->getQtyOrdered();
        $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
        $productPrice = $product->getPrice();

        $bundleChildSum = 0;
        foreach ($parentItem->getChildrenItems() as $child) {
            $bundleChildSum += $child->getQtyOrdered(); //$child->getProduct()->getPrice();
        }

        return $this->_formatPrice($parentPrice * $productPrice / $bundleChildSum);
         * 
         */
        $parentItem = $orderItem->getParentItem();
            $parentPrice = ($parentItem->getPriceInclTax() - $parentItem->getDiscountAmount());
           
            $bundleChildSum = 0;
            foreach ($parentItem->getChildrenItems() as $child) {
                $bundleChildSum += $child->getQtyOrdered();
               
            }
          //  var_dump($parentPrice / $bundleChildSum );
          return $parentPrice / $bundleChildSum ;  
    }

}
