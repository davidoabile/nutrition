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
* File        Observer.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Adminhtml_Sales_Order_Grid_Observer
{
    protected $_orderCounter = 10000;

    public function moogento_core_sales_order_prepare($event)
    {
        $block = $event->getBlock();

        if (Mage::getStoreConfig('moogento_shipeasy/orderpage/button_scan_enable')) {
            $block->addButton(
                'barcode_scan',
                array(
                    'label' => Mage::helper('moogento_shipeasy')->__('Process : Scan'),
                    'onclick' => 'clickButtonScan()',
                    'class' => 'moo_process_scan'
                )
            );
        }

        if (Mage::getStoreConfig('moogento_shipeasy/import/import_shipment')) {
            $block->addButton(
                'import_shipments',
                array(
                    'label' => Mage::helper('moogento_shipeasy')->__('Process : CSV'),
                    'onclick' => 'setLocation(\'' . $block->getUrl('*/system_convert_shipments') .'\')',
                    'class' => 'import'
                )
            );
        }

        $block->updateButton('add', 'level', 1);
        
        return $this;
    }

    public function moogento_core_order_grid_columns($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();
        $grid->addCustomColumn('szy_created_at', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Date'),
                'index' => 'created_at',
                'type' => 'datetime',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_date',
                'filter_index' => 'main_table.created_at',
                'width' => '100px',
                'column_css_class' => 'nowrap',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_date',
            ));
        $grid->addCustomColumn('szy_customer_group_id', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Customer Group'),
                'index' => 'szy_customer_group_id',
                'type' => 'options',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_customergroup',
            ));
        $grid->addCustomColumn('szy_store_id', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Store View'),
                'index' => 'store_id',
                'type' => 'store',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_store_storeview',
                'filter_index' => 'main_table.store_id',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_store_storeview',
            ));
        $grid->addCustomColumn('szy_store_name', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Store'),
                'index' => 'store_id',
                'type' => 'store',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_store_storename',
                'filter_index' => 'main_table.store_id',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_store_storename',
            ));
        $grid->addCustomColumn('szy_website_id', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Website'),
                'index' => 'store_id',
                'type' => 'store',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_store_simple',
                'filter_index' => 'main_table.store_id',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_store_simple',
            ));
        if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
            $grid->addCustomColumn('szy_country_region', array(
                    'header' => Mage::helper('moogento_shipeasy')->__('Shipping Zone'),
                    'index' => 'szy_country_region',
                    'type' => 'options',
                    'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setCountryGroupFilter',
                    'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_country_group',
                ));
        }
        $grid->addCustomColumn('szy_country', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Country'),
                'index' => 'szy_country',
                'width' => '100px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_country',
            ));
        $grid->addCustomColumn('szy_custom_attribute', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Custom shipEasy Column 1'),
                'index' => 'szy_custom_attribute',
                'type' => 'text',
                'width' => '40px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_custom',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_custom',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setCustomAttributeFirstFilter',
                'column_css_class' => 'nowrap szy_custom_col',
            ));
        $grid->addCustomColumn('szy_custom_attribute2', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Custom shipEasy Column 2'),
                'index' => 'szy_custom_attribute2',
                'type' => 'options',
                'width' => '40px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_custom2',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_custom2',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setCustomAttributeFilter',
                'column_css_class' => 'nowrap szy_custom_col',
            ));
        $grid->addCustomColumn('szy_custom_attribute3', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Custom shipEasy Column 3'),
                'index' => 'szy_custom_attribute3',
                'type' => 'options',
                'width' => '40px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_custom3',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_custom3',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setCustomAttributeFilter',
                'column_css_class' => 'nowrap szy_custom_col',
            ));
        $grid->addCustomColumn('szy_status', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_status',
                'filter_index' => 'main_table.status',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_status',
                'column_css_class' => 'nowrap szy_status_col',
            ));
        $grid->addCustomColumn('szy_payment_method', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Payment Method'),
                'index' => 'szy_payment_method',
                'type' => 'options',
            ));
        $grid->addCustomColumn('szy_shipping_method', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Shipping Method'),
                'index' => 'szy_shipping_method',
                'type' => 'options',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_shippingmethod',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setShippingMethodFilter',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_shippingmethod'
            ));
        $grid->addCustomColumn('paid', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Paid'),
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_paid',
                'index' => 'total_paid',
                'type' => 'currency',
                'width' => '10em',
                'currency' => 'order_currency_code',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setPaidFilter',
            ));
        $grid->addCustomColumn('szy_region', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Region'),
                'index' => 'szy_region',
                'type' => 'text',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_region',
            ));
        $grid->addCustomColumn('szy_tracking_number', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Tracking'),
                'index' => 'szy_tracking_number',
                'type' => 'input_label',
                'width' => '100px',
                'header_css_class' => 'no-link sort-title',
                'inline_css' => 'tracking_number',
                'sortable' => false,
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_input_label',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setTrackingFilter',
            ));
        $grid->addCustomColumn('szy_product_skus', array(
                'header' => 'Sku',
                'index' => 'szy_product_skus',
                'type' => 'text',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_skus',
                'header_css_class' => 'sort-title',
            ));
        $grid->addCustomColumn('product_image', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Image'),
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_image',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setImageFilter',
                'exportable' => false,
            ));
        $grid->addCustomColumn('szy_product_names', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Product Name'),
                'index' => 'szy_product_names',
                'type' => 'text',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_names',
                'header_css_class' => 'sort-title',
            ));
        $grid->addCustomColumn('contact', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Contact'),
                'index' => 'contact_to_customer',
                'type' => 'text',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_contact',
                'column_css_class' => 'nowrap',
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'sort-title',
                'exportable' => false,
            ));
        $grid->addCustomColumn('admin_comments', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Comments'),
                'index' => 'admin_comments',
                'type' => 'text',
                'width' => '200px',
                'column_css_class' => 'nowrap',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_comment',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setCommentFilter',
                'sortable' => false,
                'header_css_class' => 'sort-title',
            ));
        $grid->addCustomColumn('weight', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Weight'),
                'index' => 'szy_weight',
                'type' => 'number',
                'width' => '10em',
            ));
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $grid->addCustomColumn('szy_ebay_customer_id', array(
                    'header' => Mage::helper('moogento_shipeasy')->__('eBay ID'),
                    'index' => 'szy_ebay_customer_id',
                    'type' => 'text',
                    'width' => '40px',
                    'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setEbayUserIdFilter',
                    'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_ebayuserid',
            ));
        }
        $grid->addCustomColumn('szy_postcode', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Postcode'),
                'index' => 'szy_postcode',
                'width' => '40px',
                'default' => '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_cron',
            ));
        $grid->addCustomColumn('szy_email', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Email'),
                'index' => 'customer_email_list',
                'type' => 'text',
                'width' => '40px',
                'column_css_class' => 'customer_email_list non-edit',
                'filter_index' => 'main_table.szy_customer_email',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_email',
            ));
        $grid->addCustomColumn('szy_custom_product_attribute', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Custom product attribute'),
                'index' => 'szy_custom_product_attribute',
                'width' => '40px',
                'type' => 'text',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_customproducttext',
                'default' => '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_cron',
            ));
        $grid->addCustomColumn('szy_custom_product_attribute2', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Custom product attribute 2'),
                'index' => 'szy_custom_product_attribute2',
                'width' => '40px',
                'type' => 'text',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_customproducttext',
                'default' => '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_cron',
            ));
        $grid->addCustomColumn('curr', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Currency'),
                'index' => 'order_currency_code',
                'type' => 'text',
                'width' => '40px',
                'filter_index' => 'main_table.order_currency_code',
            ));
        $grid->addCustomColumn('szy_sku_number', array(
                'header' => Mage::helper('moogento_shipeasy')->__('# SKU'),
                'index' => 'szy_sku_number',
                'type' => 'text',
                'width' => '40px',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_sku_number',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setSkuNumberFilter',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_sku_number',
            ));

        $grid->addCustomColumn('szy_qty', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Qty'),
                'index' => 'szy_qty',
                'type' => 'number',
                'width' => '40px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_cron',
                'default' => '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>',
            ));

        $grid->addCustomColumn('coupon', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Coupon'),
                'index' => 'coupon_code',
                'type' => 'text',
                'width' => '40px',
            ));
        $grid->addCustomColumn('backorder', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Stock guide'),
                'index' => 'backorder',
                'align' => 'center',
                'type' => 'options',
                'width' => '40px',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_backorders',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setBackordersFilter',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_backorders',
            ));
        $grid->addCustomColumn('gift', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Gift'),
                'index' => 'gift_message_id',
                'align' => 'center',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_gift',
                'filter' => 'moogento_shipeasy/adminhtml_widget_grid_column_filter_gift',
                'options' => array(
                                "1" => Mage::helper("moogento_shipeasy")->__("Yes"),
                                "0" => Mage::helper("moogento_shipeasy")->__("No"),
                            ),
            ));
        $grid->addCustomColumn('timezone', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Timezone'),
                'index' => 'timezone_offset',
                'width' => '100px',
                'type' => 'options',
                'align' => 'center',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_timezone',
                'filter_condition_callback' => 'Moogento_ShipEasy_Helper_Grid::setTimezoneFilter',
                'options' => array(
                                "0" => Mage::helper("moogento_shipeasy")->__("All"),
                                "1" => Mage::helper("moogento_shipeasy")->__("Call Time Not OK"),
                                "2" => Mage::helper("moogento_shipeasy")->__("Call Time OK"),
                            ),
            ));
        $grid->addCustomColumn('szy_company', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Company'),
                'index' => 'szy_company',
                'align' => 'center',
                'exportable' => false,
                'column_css_class' => 'szy_company',
            ));
        
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro') || Mage::helper('moogento_core')->isInstalled('Camiloo_Channelunity')) {
            $grid->addCustomColumn('mkt_order_id', array(
                'header' => Mage::helper('moogento_shipeasy')->__('Mkt Order ID'),
                'index' => 'mkt_order_id',
                'type' => 'text',
                'width' => '40px',
                'renderer' => 'moogento_shipeasy/adminhtml_widget_grid_column_renderer_cron',
                'default' => '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>',
            ));
        }
        if(Mage::getStoreConfig('moogento_shipeasy/grid/'.Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field').'_show')){
            $grid->setDefaultSort(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field'));
            $grid->setDefaultDir(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_type') ? 'asc' : 'dec');
        }        
    }

    public function moogento_core_order_grid_columns_prepare($observer)
    {
        $columnId = $observer->getEvent()->getColumnId();
        /** @var Varien_Object $data */
        $data = $observer->getEvent()->getDataObject();
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();

        $data->setVisible(Mage::getStoreConfigFlag('moogento_shipeasy/grid/' . $columnId . '_show'));
        $data->setOrder($this->_orderCounter++);
        if (Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_order')) {
            $data->setOrder((int)Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_order'));
        }

        if (in_array($columnId, array('created_at', 'status', 'store_name', 'store_id', 'shipping_description'))) {
            $data->setRemoved(true);
            return;
        }

        switch ($columnId) {
            case 'massaction':
                $data->setOrder(-1);
                break;
            case 'szy_status':
                $singleOptions = array();
                $data->setOptions(Mage::getSingleton('sales/order_config')->getStatuses());
                foreach($data->getOptions() as $key => $label) {
                    $singleOptions[] = array(
                        'label' => $label,
                        'value' => $key
                    );
                }

                $optionGroups = array(
                    array(
                        'label' => 'Single Status',
                        'value' => $singleOptions
                    ),
                );

                $existingStatusGroups = Mage::getStoreConfig('moogento_shipeasy/grid/szy_status_status_group');
                if ($existingStatusGroups) {
                    try {
                        $existingStatusGroups = unserialize($existingStatusGroups);
                        if (is_array($existingStatusGroups)) {
                            $groupedOptions = array();
                            foreach($existingStatusGroups as $groupName => $groupStatuses) {
                                $groupedOptions[] = array(
                                    'label' => $groupName,
                                    'value' => ($groupStatuses && count($groupStatuses))
                                        ? implode(',', $groupStatuses)
                                        : ''
                                );
                            }
                            $optionGroups[] = array(
                                'label' => 'Status Groups',
                                'value' => $groupedOptions
                            );
                        }
                    } catch (Exception $e) {
                    }
                }
                $data->setoptionGroups($optionGroups);
                break;
            case 'store_id':
                $data->setHeader(Mage::helper('moogento_shipeasy')->__("Store"));
                $data->setColumnCssClass('a-center');
                $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_store_storeview');
                break;
            case 'szy_sku_number':
                $data->setColumnCssClass('a-center');
                break;
            case 'szy_custom_product_attribute':
                $attr = Mage::getSingleton("eav/config")->getAttribute('catalog_product', Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside'));
                if(($attr->getFrontendInput() == 'select') || ($attr->getFrontendInput() == 'multiselect')){
                    $data->setFilter('moogento_shipeasy/adminhtml_widget_grid_column_filter_customproductselect');
                }
                break;
            case 'szy_custom_product_attribute2':
                $attr = Mage::getSingleton("eav/config")->getAttribute('catalog_product', Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside'));
                if(($attr->getFrontendInput() == 'select') || ($attr->getFrontendInput() == 'multiselect')){
                    $data->setFilter('moogento_shipeasy/adminhtml_widget_grid_column_filter_customproduct2select');
                }
                break;
            case 'szy_custom_attribute':
                $data->setOptions(Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_custom')->getPreset(1));
                break;
            case 'szy_custom_attribute2':
                $data->setOptions(Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_custom')->getPreset(2));
                break;
            case 'szy_custom_attribute3':
                $data->setOptions(Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_custom')->getPreset(3));
                break;
            case 'szy_customer_name':
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_customer_name_expanded')) {
                    $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_name_expanded');
                    $data->setAddressType('both');
                    $data->setColumnCssClass('addresscell');
                }
                break;
            case 'billing_name':
                $data->setHeader(Mage::helper('moogento_shipeasy')->__("Bill To"));
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/billing_name_expanded')) {
                    $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_name_expanded');
                    $data->setAddress_type('billing');
                    $data->setColumnCssClass('addresscell bill_ship');
                } else{
                    $data->setColumnCssClass('bill_ship');
                    $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_name_short');
                }
                break;
            case 'shipping_name':
                $data->setHeader(Mage::helper('moogento_shipeasy')->__("Ship To"));
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/shipping_name_expanded')) {
                    $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_name_expanded');
                    $data->setAddressType('shipping');
                    $data->setColumnCssClass('addresscell bill_ship');
                } else{
                    $data->setColumnCssClass('bill_ship');
                    $data->setRenderer('moogento_shipeasy/adminhtml_widget_grid_column_renderer_name_short');
                }
                break;
            case 'action':
                $data->setHeaderCssClass('sort-title');
                $data->setColumnCssClass('action_col');		
                if ($grid->isDeleteorderRewrite()) {
                    $data->setHeader(Mage::helper('sales')->__('Action'));
                    $data->setWidth('100px');
                    $data->setType('action');
                    $data->setGetter('getId');
                    $data->setRenderer('deleteorder/adminhtml_sales_order_render_delete');
                    $data->setFilter(false);
                    $data->setSortable(false);
                    $data->setIndex('stores');
                    $data->setIsSystem(true);
                }
                break;
            case 'szy_country_region':
                $data->setOptions(Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_country_group')->getCountryGroups());
                break;
            case 'szy_shipping_method':
                $optionGroups = array(
                    array(
                        'label' => Mage::helper('moogento_shipeasy')->__('Carriers'),
                        'value' => Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_shipping_method')->toOptionArray(false),
                    ),
                );

                $existingStatusGroups = Mage::getStoreConfig('moogento_shipeasy/grid/szy_shipping_method_method_group');
                if ($existingStatusGroups) {
                    try {
                        @$existingStatusGroups = unserialize($existingStatusGroups);
                        if (is_array($existingStatusGroups) && count($existingStatusGroups)) {
                            $groupedOptions = array();
                            foreach($existingStatusGroups as $groupStatuses) {
                                $groupedOptions[] = array(
                                    'label' => $groupStatuses['name'],
                                    'value' => 'group_' . $groupStatuses['name']
                                );
                            }
                            $optionGroups[] = array(
                                'label' => 'Carrier Groups',
                                'value' => $groupedOptions
                            );
                        }
                    } catch (Exception $e) {
                    }
                }
                $data->setoptionGroups($optionGroups);
                break;
            case 'szy_payment_method':
                $data->setOptions(Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_payment_method')->getPaymentMethods());
                break;
            case 'szy_customer_group_id':
                $data->setOptions(Mage::getResourceModel('customer/group_collection')->load()->toOptionHash());
                break;
        }
        if (Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_header')) {
            $data->setOrigHeader($data->getHeader());
            $data->setHeader(Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_header'));
        }
        
        if(Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_width')){
            $data->setWidth(Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_width'));
        } else {
            $data->setWidth(null);
        }
    }

    public function moogento_core_order_grid_actions($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();

        $grid->getMassactionBlock()->setTemplate('moogento/shipeasy/sales/order/grid/massaction.phtml');
        $grid->getMassactionBlock()->setUseSelectAll(true);

        $items = Mage::helper('moogento_shipeasy/grid')->getMassActionItems();

        foreach ($items as $_itemId => $_itemData) {
            $grid->getMassactionBlock()->addItem($_itemId, $_itemData);
        }
    }

    public function moogento_core_order_grid_init($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();
        $grid->setRowClickCallback("openGridRowNew");
    }
    
    public function moogento_shipeasy_order_address_save_after($observer)
    {
        $address = $observer->getEvent()->getAddress();
        try {
            $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
            $resource->updateGridRow($address->getParentId(), "szy_company", $address->getCompany());
        } catch (Exception $e) {
            Mage::log('Выброшено исключение: '.$e->getMessage()."\n");
        }
    }
    
    public function moogento_shipeasy_order_save_commit_after($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $billing_address = $order->getBillingAddress();
        if($billing_address->getId()){
            try {
                $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
                $resource->updateGridRow($order->getId(), "szy_company", $billing_address->getCompany());
            } catch (Exception $e) {
                Mage::log('Выброшено исключение: '.$e->getMessage()."\n");
            }        
        }
    }
}