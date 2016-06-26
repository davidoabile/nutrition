<?php


class Moogento_ShipEasy_Model_Cron
{
    public function updateOrderData()
    {
        $orders = Mage::getResourceModel('sales/order_grid_collection');
        $orders->setPageSize(50);
        $orders->getSelect()->where('szy_qty IS NULL');
        $orders->getSelect()->order('created_at DESC');

        /** @var Mage_Sales_Model_order $order */
        foreach ($orders as $order) {
            $szy_qty        = 0;
            $szy_sku_number = count($order->getAllVisibleItems()) > 1 ? 1 : 0;
            $szy_postcode   = '';

            foreach ($order->getAllVisibleItems() as $item) {
                $szy_qty += $item->getQtyOrdered() - $item->getQtyCanceled();
            }

            $address = $order->getShippingAddress();
            if (!$address) {
                $address = $order->getBillingAddress();
            }
            if ($address) {
                $szy_postcode = $address->getPostcode();
            }

            $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            $resource->updateGridRow($order->getId(), "szy_qty", $szy_qty);
            $resource->updateGridRow($order->getId(), "szy_sku_number", $szy_sku_number);
            $resource->updateGridRow($order->getId(), "szy_postcode", $szy_postcode);
        }
    }

    public function updateProductData()
    {
        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_custom_product_attribute_show')
            || Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_custom_product_attribute2_show')
        ) {
            $orders = Mage::getResourceModel('sales/order_grid_collection');
            $orders->setPageSize(50);
            $orders->getSelect()->where('szy_custom_product_attribute IS NULL OR szy_custom_product_attribute2 IS NULL');
            $orders->getSelect()->order('created_at DESC');

            $attributeCode  = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside');
            $attribute      = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);
            $attributeMulti = $attribute->getFrontend()->getInputType() == 'select'
                              || $attribute->getFrontend()->getInputType() == 'multiselect';


            $attributeCodeSecond  = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside');
            $attributeSecond      = Mage::getModel('catalog/product')->getResource()
                                        ->getAttribute($attributeCodeSecond);
            $attributeMultiSecond = $attributeSecond->getFrontend()->getInputType() == 'select'
                                    || $attributeSecond->getFrontend()->getInputType() == 'multiselect';

            /** @var Mage_Sales_Model_order $order */
            foreach ($orders as $order) {
                $attributeValues       = array();
                $attributeValuesSecond = array();
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                        || $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
                    ) {
                        continue;
                    }
                    $product = Mage::getModel('catalog/product')->setStoreId($order->getStoreId())
                                   ->load($item->getProductId());
                    if ($product->getId()) {
                        if ($attribute) {
                            if ($attributeMulti) {
                                $attributeValues[] = $product->getAttributeText($attributeCode);
                            } else {
                                $attributeValues[] = $product->getData($attributeCode);
                            }
                        }
                        if ($attributeSecond) {
                            if ($attributeMultiSecond) {
                                $attributeValuesSecond[] = $product->getAttributeText($attributeCodeSecond);
                            } else {
                                $attributeValuesSecond[] = $product->getData($attributeCodeSecond);
                            }
                        }
                    }
                }

                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                $resource->updateGridRow($order->getId(), "szy_custom_product_attribute",
                    implode('<br/>', $attributeValues));
                $resource->updateGridRow($order->getId(), "szy_custom_product_attribute2",
                    implode('<br/>', $attributeValuesSecond));
            }
        }
    }

    public function fixOldColumns()
    {
        $this->_fixSzyCustomerEmail();
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $this->_fixSzyEbayCustomerId();
        }
    }

    protected function _fixSzyCustomerEmail()
    {
        $orders = Mage::getResourceModel('sales/order_grid_collection');
        $orders->setPageSize(50);
        $orders->getSelect()->where('szy_customer_email IS NULL');
        $orders->getSelect()->order('created_at DESC');

        foreach ($orders as $order) {
            $customer_email = $order->getData('customer_email');
            Mage::getResourceSingleton('moogento_shipeasy/sales_order')->updateGridRow(
                $order->getId(),
                'szy_customer_email',
                $customer_email
            );
        }
    }

    private function _fixSzyEbayCustomerId()
    {
        $orders = Mage::getResourceModel('sales/order_grid_collection');
        $orders->setPageSize(50);
        $orders->getSelect()->where('szy_ebay_customer_id IS NULL');
        $orders->getSelect()->order('created_at DESC');

        foreach ($orders as $order) {
            $m2eproOrder = Mage::getModel('M2ePro/Order')->load($order->getId(), 'magento_order_id');
            $buyerUserId = '';
            if ($m2eproOrder->getId()) {
                if ($m2eproOrder->getComponentMode() == 'ebay') {
                    $buyerUserId = $m2eproOrder->getChildObject()->getData('buyer_user_id');
                }
            }

            Mage::getResourceSingleton('moogento_shipeasy/sales_order')->updateGridRow(
                $order->getId(),
                'szy_ebay_customer_id',
                $buyerUserId
            );
        }
    }

    public function updateMktOrderId()
    {
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')
            || Mage::helper('moogento_core')->isInstalled('Camiloo_Channelunity')
        ) {
            $orders = Mage::getModel("sales/order")->getCollection();
            $select = $orders->getSelect();
            $select->joinLeft(
                array('grid_table' => Mage::getSingleton('core/resource')->getTableName('sales/order_grid')),
                'grid_table.entity_id = main_table.entity_id',
                array('mkt_order_id')
            );
            $select->where('grid_table.mkt_order_id IS NULL');
            $select->limit(10, 0);
            $select->order('main_table.created_at DESC');
            $m2eproEnabled = Mage::helper('moogento_core')->isInstalled('Ess_M2ePro');
            foreach ($orders as $order) {
                $additional_data = $order->getPayment() ? $order->getPayment()->getAdditionalData() : null;
                $result          = $order->getIncrementId();
                if (($additional_data != '') && (!is_null($additional_data))) {
                    $data = unserialize($additional_data);
                    if (!is_null($data['channel_order_id'])) {
                        $result = $data['channel_order_id'];
                    }
                }
                if ($m2eproEnabled && Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_mkt_link')) {
                    try {
                        $m2eproOrder = Mage::getModel('M2ePro/Order')->load($order->getId(), 'magento_order_id');
                        if ($m2eproOrder->getId()) {
                            if ($m2eproOrder->getComponentMode() == 'ebay') {
                                $orderID = $m2eproOrder->getChildObject()->getEbayOrderId();
                                $parts   = explode('-', $orderID);

                                $marketplaceId = $m2eproOrder->getMarketplaceId();
                                $domain        = Mage::helper('M2ePro/Component_Ebay')
                                                     ->getCachedObject('Marketplace', $marketplaceId)->getUrl();

                                if (count($parts) > 1) {
                                    $url
                                        = 'http://my.' . $domain . '/eBayISAPI.dll?EditSalesRecord&transid=' . $parts[1]
                                          . '&itemid=' . $parts[0];
                                } else {
                                    $url = 'http://my.' . $domain . '/ws/eBayISAPI.dll?EditSalesRecord&orderid='
                                           . $orderID;
                                }
                                $result = '<a href="' . $url . '" target="_blank">' . $result . '</a>';
                            } else if ($m2eproOrder->getComponentMode() == 'amazon') {
                                $url = Mage::helper('M2ePro/Component_Amazon')
                                           ->getOrderUrl($result, $m2eproOrder->getMarketplaceId());
                                if ($url) {
                                    $result = '<a href="' . $url . '" target="_blank">' . $result . '</a>';
                                }
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
                if ($m2eproEnabled
                    && Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_ebay_sales_number')
                ) {
                    try {
                        $m2eproOrder = Mage::getModel('M2ePro/Order')->load($order->getId(), 'magento_order_id');
                        if ($m2eproOrder->getId() && $m2eproOrder->getComponentMode() == 'ebay') {
                            $result .= "\n" . '(SM #' . $m2eproOrder->getChildObject()->getSellingManagerId() . ')';
                        }
                    } catch (Exception $e) {
                    }
                }
                if (Mage::helper('moogento_core')->isInstalled('Camiloo_Channelunity')) {
                    $paymentTransaction = Mage::getModel('sales/order_payment_transaction')
                                              ->load($order->getId(), 'order_id');
                    if ($paymentTransaction->getId()
                        && $paymentTransaction->getAdditionalInformation('RemoteOrderID')
                    ) {
                        $result = $paymentTransaction->getAdditionalInformation('RemoteOrderID');
                    }
                }
                try {
                    Mage::helper('moogento_shipeasy/sales')->updateOrderAttribute($order, 'mkt_order_id', $result);
                } catch (Exception $e) {
                    Mage::log($e . " : " . date('Y-m-d H:i:s'), null, "cron.log");
                }
            }
        }
    }

    public function updateTimezone()
    {
        $orders = Mage::getModel('sales/order')->getCollection();
        $select = $orders->getSelect();
        $select->where('timezone_offset IS NULL');
        $select->limit(10);

        foreach ($orders as $order) {
            $address = $order->getShippingAddress();
            if (is_null($address)) {
                $address = $order->getBillingAddress();
            }
            if (!$address) {
                continue;
            }
            $country = (!$address->getCountry()) ? "" : $address->getCountry();
            $query
                     = <<<HEREDOC
                SELECT tz.gmt_offset
                FROM `shipeasy_timezone_timezone` tz JOIN `shipeasy_timezone_zone` z
                ON tz.zone_id=z.zone_id
                WHERE tz.time_start < UNIX_TIMESTAMP(UTC_TIMESTAMP()) AND z.country_code='{$country}'
                ORDER BY tz.time_start DESC LIMIT 1;
HEREDOC;
            $read    = Mage::getSingleton('core/resource')->getConnection('core_read');
            $data    = $read->fetchOne($query);

            Mage::helper('moogento_shipeasy/sales')->updateOrderAttribute($order, 'timezone_offset', $data);

            $phone = $address->getTelephone();
            if (substr($phone, 0, 1) == 0) {
                try {
                    $country = Mage::getModel('directory/country')->loadByCode($address->getCountryId());
                } catch (Exception $e) {
                    $country = false;
                }
                if ($country && $country->getId()) {
                    if (!is_null($country->getMobileCode())) {
                        $phone = "+" . $country->getMobileCode() . substr($phone, 1);
                    }
                }
            }

            Mage::helper('moogento_shipeasy/sales')->updateOrderAttribute($order, 'phone', $phone);
        }
    }

    public function updateEbayItemsLinks()
    {
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $orderItems = Mage::getModel("sales/order_item")->getCollection();
            $select      = $orderItems->getSelect();
            $select->where('main_table.ebay_item_id IS NULL');
            $select->limit(10, 0);
            $select->order('main_table.created_at DESC');

            $ebaySelect = Mage::getModel("M2ePro/Order_Item")->getCollection()->getSelect();
            $ebaySelect->reset(Zend_Db_Select::COLUMNS);
            $ebaySelect->join(
                array('m2epro_order' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Order')),
                'm2epro_order.id = main_table.order_id',
                array()
            )
            ->join(
                array('m2epro_ebay_order' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Ebay_Order')),
                'm2epro_order.id = m2epro_ebay_order.order_id',
                array()
            )
            ->join(
                array('m2epro_ebay_order_item' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Ebay_Order_Item')),
                'main_table.id = m2epro_ebay_order_item.order_item_id',
                array("CONCAT_WS('|', 'ebay', m2epro_ebay_order_item.item_id, mea.mode, m2epro_order.marketplace_id)  as item_id_for_ebay_link")
            )
            ->joinLeft(
                array('mea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                '(mea.account_id = `m2epro_order`.account_id)',
                array('account_mode' => 'mode')
            );

            $amazonSelect = Mage::getModel("M2ePro/Order_Item")->getCollection()->getSelect();
            $amazonSelect->reset(Zend_Db_Select::COLUMNS);
            $amazonSelect->join(
                array('m2epro_order' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Order')),
                'm2epro_order.id = main_table.order_id',
                array())
                ->join(
                    array('m2epro_amazon_order' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Amazon_Order')),
                    'm2epro_order.id = m2epro_amazon_order.order_id',
                    array()
                )
                ->join(
                    array('m2epro_amazon_order_item' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Amazon_Order_Item')),
                    'main_table.id = m2epro_amazon_order_item.order_item_id',
                    array("CONCAT_WS('|', 'amazon', m2epro_amazon_order_item.general_id, m2epro_order.marketplace_id)  as item_id_for_link")
                );


            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            foreach ($orderItems as $item) {
                try {
                    $tmpSelect = clone $ebaySelect;
                    $tmpSelect->where('main_table.product_id = ' . $item->getProductId());
                    $tmpSelect->where('m2epro_order.magento_order_id = ' . $item->getOrderId());

                    $data = $read->fetchOne((string) $tmpSelect);

                    $ebayItemId = '';
                    if ($data) {
                        $ebayItemId = $data;
                    } else {
                        $tmpSelect = clone $amazonSelect;
                        $tmpSelect->where('main_table.product_id = ' . $item->getProductId());
                        $tmpSelect->where('m2epro_order.magento_order_id = ' . $item->getOrderId());

                        $data = $read->fetchOne((string) $tmpSelect);
                        if ($data) {
                            $ebayItemId = $data;
                        }
                    }
                    $item->setEbayItemId($ebayItemId);
                    $item->save();
                } catch (Exception $e) {
                    Mage::log($e . " : " . date('Y-m-d H:i:s'), null, "cron.log");
                }
            }
        }
    }
}