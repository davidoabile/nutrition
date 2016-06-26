<?php


class Moogento_ShipEasy_Block_Adminhtml_System_Config_Grid extends Mage_Adminhtml_Block_Widget implements
    Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'moogento/shipeasy/system/config/grid.phtml';
    protected $_columnsData = null;

    public function initForm()
    {
        return $this;
    }

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');

        $head->addJs('moogento/general/jquery.min.js');
        $head->addJs('moogento/general/jquery-ui.min.js');
        $head->addJs('moogento/general/chosen.jquery.min.js');
        $head->addJs('moogento/general/jquery.switchButton.js');
        $head->addJs('moogento/general/knockout.js');
        $head->addJs('moogento/general/knockout-sortable.min.js');
        $head->addJs('moogento/general/knockout.bindings.js');

        $head->addJs('moogento/shipeasy/grid/grid.js');

        $head->addCss('moogento/general/chosen.min.css');
        $head->addCss('moogento/general/config.css');
        $head->addCss('moogento/general/jqueryui/jquery-ui-1.10.4.custom.min.css');

        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _getColumns()
    {
        if (is_null($this->_columnsData)) {
            $this->_columnsData = array();
            $grid    = Mage::app()->getLayout()->createBlock('moogento_core/adminhtml_sales_order_grid');
            $columns = $grid->getFullColumnsList();
            foreach ($columns as $key => $column) {
                if (!isset($column['header']) || in_array($key, array('szy_base_shipping_cost', 'status'))) {
                    continue;
                }
                $header        = $column['header'];
                $orig_header   = isset($column['orig_header']) ? $column['orig_header'] : $header;
                $this->_columnsData[] = array(
                    'key'              => $key,
                    'header'           => $header,
                    'orig_header'      => $orig_header,
                    'show'             => Mage::getStoreConfig('moogento_shipeasy/grid/' . $key . '_show'),
                    'order'            => Mage::getStoreConfig('moogento_shipeasy/grid/' . $key . '_order'),
                    'width'            => Mage::getStoreConfig('moogento_shipeasy/grid/' . $key . '_width'),
                    'additionalFields' => $this->_getAdditionalFields($key),
                );
            }
        }

        return $this->_columnsData;
    }

    protected function _getColumnsJson()
    {
        return Mage::helper('core')->jsonEncode($this->_getColumns());
    }

    protected function _getAdditionalFields($columnId)
    {
        $fields = array();
        /*
         * Adding date format for created at attribute
         */
        $data = new Varien_Object(array('column_id' => $columnId, 'fields' => $fields));
        Mage::dispatchEvent('moogento_shipeasy_system_config_grid_get_additional_fields', array('column' => $data));
        $fields = $data->getFields();
        switch ($columnId) {
            case 'szy_created_at':
                $fields[] = array(
                    'key' => 'type',
                    'label' => $this->__('Date Type'),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_grid_datetype')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_type'),
                );
                $fields[] = array(
                    'key' => 'persian_numbers',
                    'label' => $this->__('Use Persian numbers'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_persian_numbers'),
                    'visible' => array('type' => 'persian'),
                );
                $fields[] = array(
                    'key' => 'thai_numbers',
                    'label' => $this->__('Use Thai numbers'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_thai_numbers'),
                    'visible' => array('type' => 'thai'),
                );
                $fields[] = array(
                    'key' => 'format',
                    'label' => $this->__('Date Format'),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_grid_dateformat')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format'),
                );
                $customFormatComment = 'Matches format from <a href="https://php.net/date">php.net/date</a>, eg:<br />';
				$customFormatComment .= '"M j, Y" // Jul 29, 2015 (default)<br />';
				$customFormatComment .= '"F j, Y, g:i a" // March 10, 2001, 5:16 pm<br />';
				$customFormatComment .= '"m.d.y" // 03.10.01<br />';

                $fields[] = array(
                    'key' => 'custom_format',
                    'label' => $this->__('Custom Date Format'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_custom_format'),
                    'visible' => array('format' => '3'),
                    'comment' => $this->__($customFormatComment),
                );
                break;
            case 'szy_country':
                $fields[] = array(
                    'key' => 'show_type',
                    'label' => $this->__('Show as: '),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_grid_showcountry')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_type'),
                );
                break;
            case 'szy_customer_name':
                $fields[] = array(
                    'key' => 'expanded',
                    'label' => $this->__('Show Address?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_expanded'),
                );
                break;
            case 'billing_name':
            case 'shipping_name':
                $fields[] = array(
                    'key' => 'expanded',
                    'label' => $this->__('Show Address?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_expanded'),
                );
                $fields[] = array(
                    'key' => 'fields',
                    'label' => $this->__('Showing fields'),
                    'type' => 'multiselect',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_shipbilladdress')->toOptionArray(),
                    'value' => explode(',', Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_fields')),
                    'visible' => array('expanded' => '1'),
                );
                break;
            case 'szy_region':
                $fields[] = array(
                    'key' => 'show_abbreviation',
                    'label' => $this->__('Show Abbreviation?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_abbreviation'),
                );
                break;
            case 'paid':
                $fields[] = array(
                    'key' => 'show_paypal_logo',
                    'label' => $this->__('Show PayPal info?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_paypal_logo'),
                );
                $fields[] = array(
                    'key' => 'non_invoiced_amounts',
                    'label' => $this->__('Show non-Invoiced Amounts as well?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_non_invoiced_amounts'),
                );
                break;
            case 'admin_comments':
                $fields[] = array(
                    'key' => 'display',
                    'label' => $this->__("Display"),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_comment_display')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_display'),
                );

                $fields[] = array(
                    'key' => 'truncate',
                    'label' => $this->__('Max Comment Length'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_truncate'),
                    'comment' => $this->__('Leave empty or set 0 to disable'),
                );

                $fields[] = array(
                    'key' => 'max_count',
                    'label' => $this->__('Max Comments Count to Display'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_max_count'),
                    'comment' => $this->__('Leave empty or set 0 to show all'),
                );

                $fields[] = array(
                    'key' => 'filter',
                    'label' => $this->__('Filter?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_filter'),
                );
                $fields[] = array(
                    'key' => 'filter_words',
                    'label' => $this->__('Stop words'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_filter_words'),
                    'comment' => $this->__('Comma separated'),
                    'visible' => array('filter' => 1)
                );
                $fields[] = array(
                    'key' => 'filter_labels',
                    'label' => $this->__('Auto-filter blank labels?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_filter_labels'),
                    'comment' => $this->__('Any comment which ends in a ":" is counted as blank'),
                );
                break;
            case 'backorder':
                $fields[] = array(
                    'key' => 'images_of_status',
                    'label' => $this->__("Values Set"),
                    'type' => 'textarea',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_images_of_status'),
                );
                $fields[] = array(
                    'key' => 'transparent_status',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__('Transparent Status'),
                    'type' => 'multiselect',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_order_status')->toOptionArray(),
                    'value' => explode(',', Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_transparent_status')),
                );
                $fields[] = array(
                    'key' => 'product_availability',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' based on"),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_product_availability')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_product_availability'),
                );
                $fields[] = array(
                    'key' => 'custom_qty',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' Qty"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_custom_qty'),
                    'visible' => array('product_availability' => 'qty'),
                );
                break;
            case 'exact_stock_status':
                $fields[] = array(
                    'key' => 'images_of_status',
                    'label' => $this->__("Values Set"),
                    'type' => 'textarea',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_images_of_status'),
                );
                break;
            case 'szy_product_skus':
                $fields[] = array(
                    'key' => 'go-to-product-page',
                    'label' => $this->__('Show go-to-product-page link'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_go-to-product-page'),
                );               
                $fields[] = array(
                    'key' => 'mkt_link',
                    'label' => $this->__('Show mkt link'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_mkt_link'),
                );
                $fields[] = array(
                    'key' => 'fill_color',
                    'label' => $this->__("Color 'Out-of-stock' cells?"),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_fill_color'),
                );
                $fields[] = array(
                    'key' => 'transparent_status',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__('Transparent Status'),
                    'type' => 'multiselect',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_order_status')->toOptionArray(),
                    'value' => explode(',', Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_transparent_status')),
                    'visible' => array('fill_color' => 1),
                );
                $fields[] = array(
                    'key' => 'product_availability',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' based on"),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_product_availability')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_product_availability'),
                    'visible' => array('fill_color' => 1),
                );
                $fields[] = array(
                    'key' => 'custom_qty',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' Qty"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_custom_qty'),
                    'visible' => array('product_availability' => 'qty', 'fill_color' => 1),
                );
                break;
            case 'szy_product_names':
                $fields[] = array(
                    'key' => 'cut_name',
                    'label' => $this->__('Trim long product names?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_cut_name'),
                );
                $fields[] = array(
                    'key' => 'cut_name_length',
                    'label' => $this->__("Max length"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_cut_name_length'),
                    'visible' => array('cut_name' => 1),
                );
                $fields[] = array(
                    'key' => 'mkt_link',
                    'label' => $this->__('Show mkt link?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_mkt_link'),
                );
                $fields[] = array(
                    'key' => 'fill_color',
                    'label' => $this->__("Color 'Out-of-stock' cells?"),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_fill_color'),
                );
                $fields[] = array(
                    'key' => 'transparent_status',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__('Transparent Status'),
                    'type' => 'multiselect',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_order_status')->toOptionArray(),
                    'value' => explode(',', Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_transparent_status')),
                    'visible' => array('fill_color' => 1),
                );
                $fields[] = array(
                    'key' => 'product_availability',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' based on"),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_product_availability')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_product_availability'),
                    'visible' => array('fill_color' => 1),
                );
                $fields[] = array(
                    'key' => 'custom_qty',
                    'label' => '&nbsp; <em class="moo_down_arrow">&#10551;</em> ' . $this->__("'Out-of-stock' Qty"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_custom_qty'),
                    'visible' => array('product_availability' => 'qty', 'fill_color' => 1),
                );
                break;
            case 'szy_status':
                $value = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_status_group'));
                $values = array();
                if (is_array($value)) {
                    foreach ($value as $name => $statuses) {
                        $values[] = array(
                            'name'     => $name,
                            'statuses' => $statuses,
                        );
                    }
                }
                $fields[] = array(
                    'key' => 'status_group',
                    'label' => $this->__("Order Status Groups"),
                    'type' => 'serializable_table',
                    'fields' => array(
                        array(
                            'key' => 'name',
                            'label' => $this->__("Name"),
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'statuses',
                            'label' => $this->__("Statuses"),
                            'type' => 'multiselect',
                            'options' => Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_order_status')->toOptionArray(),
                        )
                    ),
                    'value' => $values,
                );
                break;
            case 'szy_ebay_customer_id':
                $fields[] = array(
                    'key' => 'show_email',
                    'label' => $this->__('Show customer email?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_email'),
                );
                break;
            case 'szy_email':
                $fields[] = array(
                    'key' => 'only_main',
                    'label' => $this->__('Show only customer email?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_only_main'),
                );
                break;
            case 'contact':
                $fields[] = array(
                    'key' => 'allow_comment',
                    'label' => $this->__('Allow Comment'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_allow_comment'),
                );
                $fields[] = array(
                    'key' => 'allow_email',
                    'label' => $this->__('Allow Email'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_allow_email'),
                );
                $fields[] = array(
                    'key' => 'allow_gmail',
                    'label' => $this->__('Allow Gmail'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_allow_gmail'),
                );
                break;
            case 'szy_custom_product_attribute':
            case 'szy_custom_product_attribute2':
                $fields[] = array(
                    'key' => 'inside',
                    'label' => $this->__('Attribute'),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_customproduct')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_inside'),
                );
                break;
            case 'product_image':
                $fields[] = array(
                    'key' => 'show_product_image_type',
                    'label' => $this->__('For configurable products show image from'),
                    'type' => 'select',
                    'options' => array(
                        array("value" => 0, "label" => "Configurable"),
                        array("value" => 1, "label" => "Simple ")
                    ),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_product_image_type'),
                );
                $fields[] = array(
                    'key' => 'max_number',
                    'label' => $this->__("Fold after # images"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_max_number'),
                );
                $fields[] = array(
                    'key' => 'show_product_name',
                    'label' => $this->__('Show product name?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_product_name'),
                );
                $fields[] = array(
                    'key' => 'name_max_number',
                    'label' => $this->__("Truncate if longer than"),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_name_max_number'),
                    'visible' => array('show_product_name' => 1),
                );
                break;
            case 'szy_store_id':
                $fields[] = array(
                    'key' => 'format_store_view',
                    'label' => $this->__('Replace with image?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format_store_view'),
                );
                $fields[] = array(
                    'key' => 'format',
                    'label' => $this->__('Display type'),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_grid_store')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format'),
                    'visible' => array('format_store_view' => 0),
                );
                foreach (Mage::app()->getWebsites() as $website) {
                    foreach ($website->getStores() as $store) {
                        $fields[] = array(
                            'key' => 'store_view_' . $store->getCode() . '_logo',
                            'label' => '&nbsp; <span class="moo_down_arrow">&#10551;</span> [' . $website->getName() . ' <span style="color: #008000;font-size: 2em;margin: 1px;position: relative;top: 5px;">&#10145;</span> ' . $store->getName() . ']',
                            'type' => 'image',
                            'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_store_view_' . $store->getCode() . '_logo'),
                            'visible' => array('format_store_view' => 1),
                            'comment' => '<em>Dimensions: (up to) 173<b style="color:red;">px</b> x 50<b style="color:red;">px</b> @ 74dpi<br />Format : transparent .png</em>',
                        );
                    }

                }
                break;
            case 'szy_store_name':
                $fields[] = array(
                    'key' => 'format_store_view',
                    'label' => $this->__('Replace with image?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format_store_view'),
                );
                $fields[] = array(
                    'key' => 'format',
                    'label' => $this->__('Display type'),
                    'type' => 'select',
                    'options' => Mage::getModel('moogento_shipeasy/adminhtml_system_config_source_grid_store_name')->toOptionArray(),
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format'),
                    'visible' => array('format_store_view' => 0),
                );
                foreach (Mage::app()->getWebsites() as $website) {
                    foreach ($website->getGroups() as $group) {
                        $fields[] = array(
                            'key' => 'store_view_' . $group->getId() . '_logo',
                            'label' => '&nbsp; <span class="moo_down_arrow">&#10551;</span> [' . $website->getName() . ' <span style="color: #008000;font-size: 2em;margin: 1px;position: relative;top: 5px;">&#10145;</span> ' . $group->getName() . ']',
                            'type' => 'image',
                            'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_store_view_' . $group->getId() . '_logo'),
                            'visible' => array('format_store_view' => 1),
                            'comment' => '<em>Dimensions: (up to) 173<b style="color:red;">px</b> x 50<b style="color:red;">px</b> @ 74dpi<br />Format : transparent .png</em>',
                        );
                    }
                }
                break;
            case 'szy_website_id':
                $fields[] = array(
                    'key' => 'format_website',
                    'label' => $this->__('Replace with image?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_format_website'),
                );

                foreach (Mage::app()->getWebsites() as $website) {
                    $fields[] = array(
                        'key' => $website->getCode() . '_logo',
                        'label' => ' &nbsp; <em class="moo_down_arrow">&#10551;</em> [' . $website->getName() . ']',
                        'type' => 'image',
                        'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_' . $website->getCode() . '_logo'),
                        'visible' => array('format_website' => 1),
                        'comment' => '<em>Dimensions: (up to) 173<b style="color:red;">px</b> x 50<b style="color:red;">px</b> @ 74dpi<br />Format : transparent .png</em>',
                    );
                }
                break;
            case 'szy_custom_attribute':
            case 'szy_custom_attribute2':
            case 'szy_custom_attribute3':
                $valueSetComment
                    = '<b>Follow this format:</b><br />
    <b>Text|#FFFFFF </b><<< will show "Text" in dropdown menu, and will have a white background in the grid <br />
    <b><span class="comment_code">{{date}}</span></b> <<< on its own, if selected will enter the current date <br/>
    <b>Flag Name|<span class="comment_code">{{flag_red.png}}</span> </b><<< will show "Flag Name" in dropdown menu, and will show the red flag icon in the grid <br /><br />
    These are pre-defined images which you can use: <br /><br />
    <span class="comment_code">{{flag_red.png}}</span><br />
    <span class="comment_code">{{flag_orange.png}}</span><br />
    <span class="comment_code">{{flag_green.png}}</span><br />
    <span class="comment_code">{{flag_grey.png}}</span><br />
    <span class="comment_code">{{flag_shipped.png}}</span><br />
    <span class="comment_code">{{flag_checkered.png}}</span><br />
    <span class="comment_code">{{flag_alert.png}}</span><br /><br />
    You can use your own by saving .png images in the /skin/adminhtml/default/default/moogento/images/flag_images/ folder and using the filename here.';
                $fields[] = array(
                    'key' => 'preset',
                    'label' => $this->__('Values Set'),
                    'type' => 'textarea',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_preset'),
                    'comment' => $valueSetComment,
                );
                break;
            case 'mkt_order_id':
                $fields[] = array(
                        'key' => 'show_ebay_sales_number',
                        'label' => $this->__('eBay Sales Record Number?'),
                        'type' => 'checkbox',
                        'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_ebay_sales_number'),
                    );
                $fields[] = array(
                    'key' => 'show_mkt_link',
                    'label' => $this->__('Show Market place link?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_mkt_link'),
                );
                break;
            case 'courierrules_description':
                $fields = $data->getFields();
                break;
            case 'szy_shipping_method':
                $values = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/szy_shipping_method_method_group'));
                if (!$values) {
                    $values = array();
                }

                $fields[] = array(
                    'key' => 'method_group',
                    'label' => Mage::helper('moogento_shipeasy')->__("Shipping Method Groups"),
                    'type' => 'serializable_table',
                    'fields' => array(
                        array(
                            'key' => 'name',
                            'label' => Mage::helper('moogento_shipeasy')->__("Name"),
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'method',
                            'label' => Mage::helper('moogento_shipeasy')->__("Shipping Method"),
                            'type' => 'multiselect',
                            'options' => Mage::getSingleton('moogento_shipeasy/adminhtml_system_config_source_shipping_method')->toOptionArray(),
                        ),
                        array(
                            'key' => 'custom_value',
                            'label' => Mage::helper('moogento_shipeasy')->__("Custom value"),
                            'type' => 'text',
                            'visible' => array('method' => 'custom_value'),
                        )
                    ),
                    'value' => $values,
                );
                break;
            case 'szy_tracking_number':
                $fields[] = array(
                    'key' => 'show_carriers',
                    'label' => $this->__('<span style="display:inline-block;line-height:0.9em;">Show carrier-setting dropdown option? <br /><span style="color:#aaa;font-style:italic;font-size:90%;">(If no, it will auto-set carrier based on pattern-match)</span></span>'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_carriers'),
                );
                break;
            case 'timezone':
                $fields[] = array(
                    'key' => 'time_start',
                    'label' => $this->__('Call time start'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_time_start'),
                );
                $fields[] = array(
                    'key' => 'time_end',
                    'label' => $this->__('Call time end'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_time_end'),
                );
                $fields[] = array(
                    'key' => 'type_call',
                    'label' => $this->__('Click to call?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_type_call'),
                );
                $fields[] = array(
                    'key' => 'show_phone_number',
                    'label' => $this->__('Show phone number'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_show_phone_number'),
                );
                $fields[] = array(
                    'key' => 'time',
                    'label' => $this->__('Show remote time?'),
                    'type' => 'checkbox',
                    'checked' => Mage::getStoreConfig('moogento_shipeasy/grid/' . $columnId . '_time'),
                );
                break;
        }
        
        return $fields;
    }

    public function getSortingGridHtml()
    {
        $result =  '<p>';
            $result .= $this->__('(First pageload) Sort orders by').': ';
            $result .= '<select class="chosen" name="groups[grid][fields][common_sorting_field][value]">';
                $result .= '<option value=""></option>';
                foreach($this->_getColumns() as $key => $column){
                    if(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_field') == $column['key']){
                        $result .= '<option value="'.$column['key'].'" selected="selected">';
                    } else {
                        $result .= '<option value="'.$column['key'].'">';
                    }
                     $result .= $column['header'];
                    $result .= '</option>';
                }        
            $result .= '</select>';
            $result .= ' '.$this->__('sort descending').' ';
            if(Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_type')){
                $result .= '<input id="sorting_type_input" type="checkbox" checked="checked"/>';
            } else {
                $result .= '<input id="sorting_type_input" type="checkbox"/>';
            }
            $result .= '<input type="text" value="'.Mage::getStoreConfig('moogento_shipeasy/grid/common_sorting_type').'" name="groups[grid][fields][common_sorting_type][value]" style="display:none"/>';
        $result .= '</p>';
        
        $result .= '<p id="presort">';
            $show_shipeasy = (bool)(Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_type_field') == "ship");
            $show_courierrule = (bool)(Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_type_field') == "courierrules");
            $result .= $this->__('This group top of sort').': ';
            $result .= '<select id="presort_select" class="chosen" name="groups[grid][fields][sorting_group_type_field][value]" onchange="changingPresortingGroup();">';
                $result .= '<option value=""></option>';
                if($show_shipeasy){
                    $result .= '<option value="ship" selected="selected">'.$this->__("Shipping Method").'</option>';
                } else {
                    $result .= '<option value="ship">'.$this->__("Shipping Method").'</option>';
                }
                if(Mage::helper("moogento_core")->isInstalled("Moogento_CourierRules")){
                    if(Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_type_field') == "courierrules"){
                        $result .= '<option value="courierrules" selected="selected">'.$this->__("courierRules Method").'</option>';
                    } else {
                        $result .= '<option value="courierrules">'.$this->__("courierRules Method").'</option>';
                    }
                }
            $result .= '</select>';

            $values = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/szy_shipping_method_method_group'));
            if (!$values) { $values = array(); }
            $value = Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_courier_rule_field');
            $result .= '<select id="sorting_group_ship" class="chosen sorting_group" name="groups[grid][fields][sorting_group_ship_field][value]"';
            $result .= !$show_shipeasy ? 'style="display:none;"' : "";
            $result .= '>';
                foreach($values as $val){
                    if($val['name'] == $value){
                        $result .= '<option value="'.$val['name'].'" selected="selected">';
                    } else {
                        $result .= '<option value="'.$val['name'].'">';
                    }
                    $result .= $val['name'];
                    $result .= '</option>';
                }        
            $result .= '</select>';

            if(Mage::helper("moogento_core")->isInstalled("Moogento_CourierRules")){
                $values = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group'));
                if (!$values) { $values = array(); }
                $value = Mage::getStoreConfig('moogento_shipeasy/grid/sorting_group_courier_rule_field');
                $result .= '<select id="sorting_group_courier_rule" class="chosen sorting_group" name="groups[grid][fields][sorting_group_courier_rule_field][value]" ';
                $result .= !$show_courierrule ? 'style="display:none;"' : "";
                $result .= '>';
                    foreach($values as $key => $val){
                        if($key == $value){
                            $result .= '<option value="'.$key.'" selected="selected">';
                        } else {
                            $result .= '<option value="'.$key.'">';
                        }
                        $result .= $key;
                        $result .= '</option>';
                    } 
                $result .= '</select>';
            }
        $result .= '</p>';
        
        $action_sorting_array = array(
            array("value" => "click-goes-nowhere", "label" => "Does nothing (copy values easily)"),
            array("value" => "click-goes-nowhere-and-selects", "label" => "Selects the order"),
            array("value" => "click-goes-to-order", "label" => "Goes to the order (Magento default)")
        );
        $result .= '<p>';
            $result .= $this->__('Clicking on an order').': ';
            $value = Mage::getStoreConfig('moogento_shipeasy/grid/action_sorting');
            $result .= '<select class="chosen sorting" name="groups[grid][fields][action_sorting][value]">';
                foreach($action_sorting_array as $val){
                    if($val['value'] == $value){
                        $result .= '<option value="'.$val['value'].'" selected="selected">';
                    } else {
                        $result .= '<option value="'.$val['value'].'">';
                    }
                    $result .= $val['label'];
                    $result .= '</option>';
                }        
            $result .= '</select>';
        $result .= '</p>';

        $weight_values = array(
                array('value' => 0, 'label' => $this->__("Top")),
                array('value' => 1, 'label' => $this->__("Bottom")),
            );
        $weight_value = Mage::getStoreConfig('moogento_shipeasy/grid/weight_disposition');
        $result .= '<p>';
            $result .= $this->__('Zoned weights/orders position').': ';
            $result .= '<select class="chosen sorting" name="groups[grid][fields][weight_disposition][value]">';
             foreach($weight_values as $val){
                if($val['value'] == $weight_value){
                    $result .= '<option value="'.$val['value'].'" selected="selected">';
                } else {
                    $result .= '<option value="'.$val['value'].'">';
                }
                $result .= $val['label'];
                $result .= '</option>';
             }
            $result .= '</select>';
        $result .= '</p>';

        return $result;
    }

}