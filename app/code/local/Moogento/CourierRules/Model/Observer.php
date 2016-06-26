<?php

class Moogento_CourierRules_Model_Observer
{
    const CRON_STRING_PATH = 'crontab/jobs/moogento_courierrules/schedule/cron_expr';

    const CRON_NO_LOG = 0;
    const CRON_LOG_EMPTY = 1;
    const CRON_LOG_ALL = 2;

    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();

        $section = $request->getParam('section');
        
        switch ($section) {
            case 'moogento_shipeasy':
                $post_data = $request->getPost('groups');
                $post_data = (array)$post_data;
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('courierrules/settings/order_grid', $post_data['grid']['fields']["courierrules_description_show"]["value"]);
                if(Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')){
                    $config->saveConfig('courierrules/settings/order_grid_original', $post_data['grid']['fields']["szy_shipping_method_show"]["value"]);
                    $config->saveConfig('moogento_shipeasy/grid/shipping_description_show', $post_data['grid']['fields']["szy_shipping_method_show"]["value"]);
                } else {
                    $config->saveConfig('courierrules/settings/order_grid_original', $post_data['grid']['fields']["shipping_description_show"]["value"]);
                    $config->saveConfig('moogento_shipeasy/grid/szy_shipping_method_show', $post_data['grid']['fields']["szy_shipping_method_show"]["value"]);
                }
                
                $groups = $request->getPost('courierrules_group_table', array());
                $newValue = array();
                foreach($groups as $statusGroup) {
                    $statuses = (!empty($statusGroup['courierrules']) && count($statusGroup['courierrules']))
                        ? $statusGroup['courierrules']
                        : array();
                    $custom_val = !empty($statusGroup['custom_value']) ? $statusGroup['custom_value'] : "";
                    $newValue[$statusGroup['name']] = array('courierrules' => $statuses, 'custom_value' => $custom_val);
                }
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('moogento_shipeasy/grid/courierrules_description_status_group', serialize($newValue));
                
                break;
            
            case 'courierrules_zones':

                $dataToSave = $request->getPost('shipping_zone', array());
                $idsToKeep = array(0);

                foreach ($dataToSave as $id => $data) {
                    if ($id > 0) {
                        $idsToKeep[] = $id;
                        $zone = Mage::getModel('moogento_courierrules/zone')->load($id);
                        if (!$zone) {
                            $zone = Mage::getModel('moogento_courierrules/zone');
                        }
                        $zone->addData($data);
                        $zone->save();
                    }
                }

                $collection = Mage::getModel('moogento_courierrules/zone')->getCollection()
                    ->addFieldToFilter('id', array("nin" => $idsToKeep));
                $collection->walk('delete');

                foreach ($dataToSave as $id => $data) {
                    if ($id < 0) {
                        $zone = Mage::getModel('moogento_courierrules/zone');
                        $zone->addData($data);
                        $zone->save();
                    }
                }
                
                if (isset($_FILES['shipping_zones_file']) && !$_FILES['shipping_zones_file']['error']) {
                    $this->_importZones($_FILES['shipping_zones_file']);
                    return;
                }

                break;

            case 'courierrules_tracking':
                $dataToSave = $request->getPost('tracking_pool', array());
                $idsToKeep = array(0);

                foreach ($dataToSave as $id => $data) {
                    if ($id > 0) {
                        $idsToKeep[] = $id;
                        $tracking = Mage::getModel('moogento_courierrules/tracking')->load($id);
                        if (!$tracking) {
                            $tracking = Mage::getModel('moogento_courierrules/tracking');
                        }
                        $tracking->addData($data);
                        $tracking->save();
                    }
                }

                $collection = Mage::getModel('moogento_courierrules/tracking')->getCollection()
                    ->addFieldToFilter('id', array("nin" => $idsToKeep));
                $collection->walk('delete');

                foreach ($dataToSave as $id => $data) {
                    if ($id < 0) {
                        $tracking = Mage::getModel('moogento_courierrules/tracking');
                        $tracking->addData($data);
                        $tracking->save();
                    }
                }

                if (isset($_FILES['tracking_numbers_file']) && !$_FILES['tracking_numbers_file']['error']) {
                    $this->_importTracking($_FILES['tracking_numbers_file']);
                    return;
                }

                $settings = $request->getPost('settings', array());
                $config = new Mage_Core_Model_Config();

                $config->saveConfig('courierrules/tracking/email_notification', isset($settings['email_notification']) ? $settings['email_notification'] : Mage::getStoreConfig('trans_email/ident_' . Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY) . '/email'));

                break;

            case 'courierrules_rules':

                $dataToSave = $request->getPost('shipping_rule', array());
                $idsToKeep = array(0);
 
 
                foreach ($dataToSave as $id => $data) {
                    if ($id > 0) {
                        foreach (array('min_product_attribute', 'max_product_attribute', 'min_product_attribute2', 'max_product_attribute2', 'min_product_attribute3', 'max_product_attribute3', 'min_weight', 'max_weight', 'min_amount', 'max_amount', 'shipping_zone', 'quantity_all_items', 'quantity_free_discount_items','shipping_cost_filter_min','shipping_cost_filter_max', 'cost_filter_min', 'cost_filter_max') as $code) {
                            if (isset($data[$code]) && $data[$code] == '') {
                                $data[$code] = new Zend_Db_Expr("NULL");
                            }
                        }
                        if (!isset($data['scope'])) {
                            $data['scope'] = 'default';
                        }
                        if (!isset($data['shipping_method'])) {
                            $data['shipping_method'] = Moogento_CourierRules_Model_Rule::ANY_METHOD;
                        }
                        if (!isset($data['active'])) {
                            $data['active'] = 0;
                        }

                        $idsToKeep[] = $id;
                        $rule = Mage::getModel('moogento_courierrules/rule')->load($id);
                        if (!$rule) {
                            $rule = Mage::getModel('moogento_courierrules/rule');
                        }
                        $rule->addData($data);
                        $rule->save();
                    }
                }
                
                $collection = Mage::getModel('moogento_courierrules/rule')->getCollection()
                    ->addFieldToFilter('id', array("nin" => $idsToKeep));
                $collection->walk('delete');
                
                foreach ($dataToSave as $id => $data) {
                    if ($id < 0) {
                        foreach (array('min_product_attribute', 'max_product_attribute', 'min_product_attribute2', 'max_product_attribute2', 'min_product_attribute3', 'max_product_attribute3', 'min_weight', 'max_weight', 'min_amount', 'max_amount', 'shipping_zone', 'quantity_all_items', 'quantity_free_discount_items','shipping_cost_filter_min','shipping_cost_filter_max', 'cost_filter_min', 'cost_filter_max') as $code) {
                            if (isset($data[$code]) && $data[$code] == '') {
                                $data[$code] = null;
                            }
                        }
                        if (!isset($data['scope'])) {
                            $data['scope'] = 'default';
                        }
                        if (!isset($data['shipping_method'])) {
                            $data[''] = Moogento_CourierRules_Model_Rule::ANY_METHOD;
                        }
                        if (!isset($data['active'])) {
                            $data['active'] = 0;
                        }

                        $zone = Mage::getModel('moogento_courierrules/rule');
                        $zone->addData($data);
                        $zone->save();
                    }
                }

                $settings = $request->getPost('settings');
                $display = array_merge($settings['display'], $settings['display_original']);

                $config = new Mage_Core_Model_Config();

                foreach(array('order_grid', 'order_grid_original', 'shipping_grid', 'shipping_grid_original') as $key) {
                    if (in_array($key, $display)) {
                        $config->saveConfig('courierrules/settings/' . $key, "1");
                        if($key=='order_grid') $config->saveConfig('moogento_shipeasy/grid/courierrules_description_show', "1");
                        if($key=='order_grid_original') {
                            $config->saveConfig('moogento_shipeasy/grid/shipping_description_show', "1");
                            $config->saveConfig('moogento_shipeasy/grid/szy_shipping_method_show', "1");
                        }
                    } else {
                        $config->saveConfig('courierrules/settings/' . $key, "0");
                        if($key=='order_grid') $config->saveConfig('moogento_shipeasy/grid/courierrules_description_show', "0");
                        if($key=='order_grid_original'){ 
                            $config->saveConfig('moogento_shipeasy/grid/shipping_description_show', "0");
                            $config->saveConfig('moogento_shipeasy/grid/szy_shipping_method_show', "0");
                        }
                    }
                }

                $config->saveConfig('courierrules/settings/use_product_attribute', isset($settings['use_product_attribute']) ? $settings['use_product_attribute'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_range', isset($settings['use_product_attribute_range']) ? $settings['use_product_attribute_range'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_sum', isset($settings['use_product_attribute_sum']) ? $settings['use_product_attribute_sum'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute2', isset($settings['use_product_attribute2']) ? $settings['use_product_attribute2'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_range2', isset($settings['use_product_attribute_range2']) ? $settings['use_product_attribute_range2'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_sum2', isset($settings['use_product_attribute_sum2']) ? $settings['use_product_attribute_sum2'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute3', isset($settings['use_product_attribute3']) ? $settings['use_product_attribute3'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_range3', isset($settings['use_product_attribute_range3']) ? $settings['use_product_attribute_range3'] : 0);
                $config->saveConfig('courierrules/settings/use_product_attribute_sum3', isset($settings['use_product_attribute_sum3']) ? $settings['use_product_attribute_sum3'] : 0);
                $config->saveConfig('courierrules/settings/enable_cron', isset($settings['enable_cron']) ? $settings['enable_cron'] : 0);
                $config->saveConfig('courierrules/settings/exact_match', isset($settings['exact_match']) ? $settings['exact_match'] : 0);
                $config->saveConfig('courierrules/settings/replace_shipment', isset($settings['replace_shipment']) ? $settings['replace_shipment'] : 0);
                $config->saveConfig('courierrules/settings/cron_period', isset($settings['cron_period']) ? $settings['cron_period'] : 5);
                $config->saveConfig('courierrules/settings/cron_limit', isset($settings['cron_limit']) ? $settings['cron_limit'] : 100);
                $config->saveConfig('courierrules/settings/cron_log', isset($settings['cron_log']) ? $settings['cron_log'] : self::CRON_NO_LOG);
                $config->saveConfig('courierrules/settings/cron_email', isset($settings['cron_email']) ? $settings['cron_email'] : 0);
                $config->saveConfig('courierrules/settings/cron_email_to', isset($settings['cron_email_to']) ? $settings['cron_email_to'] : 0);
                $config->saveConfig('courierrules/settings/create_missing_options', isset($settings['create_missing_options']) ? $settings['create_missing_options'] : 0);
                $config->saveConfig('courierrules/settings/quantity_all_items', isset($settings['quantity_all_items']) ? $settings['quantity_all_items'] : 0);
                $config->saveConfig('courierrules/settings/quantity_free_discount_items', isset($settings['quantity_free_discount_items']) ? $settings['quantity_free_discount_items'] : 0);
                $config->saveConfig('courierrules/settings/wipe_current_rules_when_importing', isset($settings['wipe_current_rules_when_importing']) ? $settings['wipe_current_rules_when_importing'] : 0);
                $config->saveConfig('courierrules/settings/shipping_cost_filter', isset($settings['shipping_cost_filter']) ? $settings['shipping_cost_filter'] : 0);
                $config->saveConfig('courierrules/settings/cost_filter', isset($settings['cost_filter']) ? $settings['cost_filter'] : 0);
                $config->saveConfig('courierrules/settings/predefined_options', isset($settings['predefined_options']) ? implode(',', $settings['predefined_options']) : '');

                $oldProductAttribute = Mage::getStoreConfig('courierrules/settings/product_attribute');
                $newProductAttribute = isset($settings['product_attribute']) ? $settings['product_attribute'] : '';
                $config->saveConfig('courierrules/settings/product_attribute', $newProductAttribute);
                if ($oldProductAttribute != $newProductAttribute) {
                    Mage::getResourceModel('moogento_courierrules/rule')->unsetProductAttribute();
                }

                $oldProductAttribute = Mage::getStoreConfig('courierrules/settings/product_attribute2');
                $newProductAttribute = isset($settings['product_attribute2']) ? $settings['product_attribute2'] : '';
                $config->saveConfig('courierrules/settings/product_attribute2', $newProductAttribute);
                if ($oldProductAttribute != $newProductAttribute) {
                    Mage::getResourceModel('moogento_courierrules/rule')->unsetProductAttribute2();
                }

                $oldProductAttribute = Mage::getStoreConfig('courierrules/settings/product_attribute3');
                $newProductAttribute = isset($settings['product_attribute3']) ? $settings['product_attribute3'] : '';
                $config->saveConfig('courierrules/settings/product_attribute3', $newProductAttribute);
                if ($oldProductAttribute != $newProductAttribute) {
                    Mage::getResourceModel('moogento_courierrules/rule')->unsetProductAttribute2();
                }


                if (isset($settings['cron_period'])) {
                    try {
                        Mage::getModel('core/config_data')
                            ->load(self::CRON_STRING_PATH, 'path')
                            ->setValue('*/' . (int)$settings['cron_period'] . ' * * * *')
                            ->setPath(self::CRON_STRING_PATH)
                            ->save();
                    } catch (Exception $e) {
                        throw new Exception(Mage::helper('moogento_courierrules')->__('Unable to save the cron expression.'));
                    }
                }
                Mage::getConfig()->reinit();
                if (isset($_FILES['shipping_zones_file']) && !$_FILES['shipping_zones_file']['error']) {
                    if (Mage::getStoreConfigFlag('courierrules/settings/wipe_current_rules_when_importing')){
                        $collection_for_delete = Mage::getModel('moogento_courierrules/rule')->getCollection()->load();
                        foreach($collection_for_delete as $val){
                            $val->delete();
                        }
                    }                   
                    $this->_importRules($_FILES['shipping_zones_file']);
                    return;
                }

                break;
        }
    }

    public function sales_order_shipment_resource_init_virtual_grid_columns($observer)
    {
        $resource = $observer->getEvent()->getResource();

        $resource ->addVirtualGridColumn(
            'shipping_description',
            'sales/order',
            array('order_id' => 'entity_id'),
            'shipping_description'
        )->addVirtualGridColumn(
            'courierrules_description',
            'sales/order',
            array('order_id' => 'entity_id'),
            'courierrules_description'
        );
    }

    protected function _importZones($fileData)
    {
        $dataToImport = $this->_processCsv($fileData);
        foreach ($dataToImport as $zoneData) {
            $zone = Mage::getModel('moogento_courierrules/zone');
            if (isset($zoneData['id'])) {
                unset($zoneData['id']);
            }

            $zoneData['countries'] = isset($zoneData['countries']) ? explode(',', $zoneData['countries']) : array();
            $zoneData['zip_codes'] = isset($zoneData['zip_codes']) ? explode(',', $zoneData['zip_codes']) : array();
            $zone->addData($zoneData);
            $zone->save();
        }
    }

    protected function _importTracking($fileData)
    {
        $dataToImport = $this->_processCsv($fileData);
        foreach ($dataToImport as $trackingData) {
            $tracking = Mage::getModel('moogento_courierrules/tracking');
            if (isset($trackingData['id'])) {
                unset($trackingData['id']);
            }

            $tracking->addData($trackingData);
            $tracking->save();
        }
    }

    protected function _importRules($fileData)
    {
        $helper = Mage::helper('moogento_courierrules');

        $session = Mage::getSingleton('adminhtml/session');
        /* @var $session Mage_Adminhtml_Model_Session */

        $dataToImport = $this->_processCsv($fileData);
        $attribute = false;
        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
            $attribute = $helper->getAttribute('product_attribute');
            $inputType = $attribute->getFrontend()->getInputType();
        }

        $missingOptions = array();
        
        foreach ($dataToImport as $key => $ruleDataArray) {
            $rule = Mage::getModel('moogento_courierrules/rule');

            $ruleData = array_filter($ruleDataArray);
            
            if (isset($ruleData['id'])) {
                unset($ruleData['id']);
            }
            
            if (!isset($ruleData['shipping_method']) || !$ruleData['shipping_method']) {
                $ruleData['shipping_method'] = Moogento_CourierRules_Model_Rule::ANY_METHOD;
            }

            if (isset($ruleData['shipping_zone'])) {
                $zone = Mage::getModel('moogento_courierrules/zone')->load($ruleData['shipping_zone'], 'name');
                if ($zone->getId()) {
                    $ruleData['shipping_zone'] = $zone->getId();
                } else {
                    $session->addError($helper->__('Shipping zone "%s" mentioned in rule "%s" does not exist.', $ruleData['shipping_zone'], $ruleData['name']));
                    $ruleData['shipping_zone'] = null;
                }
            }
            if (isset($ruleData['tracking_pool'])) {
                $tracking = Mage::getModel('moogento_courierrules/tracking')->load($ruleData['tracking_pool'], 'name');
                if ($tracking->getId()) {
                    $ruleData['tracking_id'] = $tracking->getId();
                } else {
                    $session->addError($helper->__('tracking pool "%s" mentioned in rule "%s" does not exist.', $ruleData['tracking_pool'], $ruleData['name']));
                    $ruleData['tracking_id'] = null;
                }
                unset($ruleData['tracking_pool']);
            }
            
            if (isset($ruleData['custom_courierrules_method'])) {
                $ruleData['target_custom'] = $ruleData['custom_courierrules_method'];
                unset($ruleData['custom_courierrules_method']);
            }
            
            foreach (array('min_weight', 'max_weight', 'min_amount', 'max_amount') as $code) {
                if (isset($ruleData[$code]) && $ruleData[$code] == '') {
                    unset($ruleData[$code]);
                }
            }
            
            if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
                if (isset($ruleData['product_attribute'])) {
                    if ($attribute->usesSource()) {
                        if ($inputType == 'multiselect') {
                            $data = explode(',', $ruleData['product_attribute']);
                            $result = array();
                            foreach ($data as $one) {
                                $optionId = $attribute->getSource()->getOptionId($one);
                                if ($optionId) {
                                    $result[] = $attribute->getSource()->getOptionId($one);
                                } else {
                                    if (Mage::getStoreConfig('courierrules/settings/create_missing_options')) {
                                        $result[] = $this->_createOption($attribute, $one);
                                    } else {
                                        $missingOptions[] = $one;
                                    }
                                }
                            }
                            $ruleData['product_attribute'] = $result;
                        } else {
                            $optionId = $attribute->getSource()->getOptionId($ruleData['product_attribute']);
                            if (!$optionId) {
                                if (Mage::getStoreConfig('courierrules/settings/create_missing_options')) {
                                    $optionId = $this->_createOption($attribute, $ruleData['product_attribute']);
                                } else {
                                    $missingOptions[] = $ruleData['product_attribute'];
                                }
                            }
                            $ruleData['product_attribute'] = $optionId;
                        }
                    }
                }
            } else {
                if (isset($ruleData['product_attribute'])) {
                    unset($ruleData['product_attribute']);
                }
            }
            
            $rule->addData($ruleData);
            $rule->save();
        }
        if (Mage::getStoreConfigFlag('courierrules/settings/use_product_attribute')) {
            array_unique($missingOptions);
            if (count($missingOptions)) {
                $session->addWarning(Mage::helper('moogento_courierrules')->__('The following option(s) are missing for attribute "%s": %s', $helper->getAttribute('product_attribute')->getFrontendLabel(), implode(', ', $missingOptions)));
            }
        }
    }

    protected function _processCsv($fileData)
    {
        $csv = new Varien_File_Csv();
        $data = $csv->getData($fileData['tmp_name']);

        $headers = array_shift($data);

        $result = array();

        foreach ($data as $row) {
            $converted = array();
            foreach ($headers as $index => $header) {
                $converted[$header] = $row[$index];
            }

            $result[] = $converted;
        }

        return $result;
    }

    protected function _createOption($attribute, $label)
    {
        $option = array(
            'value' => array(
                'new_option' => array(
                    0 => $label,
                )
            )
        );
        $attribute->setOption($option)
            ->save();

        Mage::helper('moogento_courierrules')->resetAttribute();
        return Mage::helper('moogento_courierrules')->getAttribute('product_attribute')->getSource()->getOptionId($label);
    }

    public function process()
    {
        if (Mage::getStoreConfigFlag('courierrules/settings/enable_cron')) {
            $resource = Mage::getSingleton('core/resource');

            /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
            $orders = Mage::getModel('sales/order')->getCollection();
            $orders->addFieldToFilter('courierrules_processed', array('null' => true));
            $orders->getSelect()->order('created_at DESC');
            $orders->addFieldToFilter('state', array('nin' => array(Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_CANCELED)));
            $orders->getSelect()->joinLeft(array('processing' => $resource->getTableName('moogento_courierrules/cron_processing')), 'main_table.entity_id = processing.entity_id', '');
            $orders->getSelect()->where('processing.entity_id IS NULL');
            $orders->setPageSize(Mage::getStoreConfig('courierrules/settings/cron_limit'));

            $writeConnection = $resource->getConnection('core_write');
            foreach ($orders as $order) {
                $writeConnection->query("INSERT INTO {$resource->getTableName('moogento_courierrules/cron_processing')} VALUES ({$order->getId()}, NOW(), 0)");
                $order = $order->load($order->getId());
                Mage::helper('moogento_courierrules')->processOrder($order);
                $writeConnection->query("DELETE FROM {$resource->getTableName('moogento_courierrules/cron_processing')} where entity_id = {$order->getId()}");
            }
        }
    }

    public function sales_order_shipment_save_after($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        if ($order->getCourierrulesTracking() && count($shipment->getAllTracks()) == 0) {
            @list($trackingNumber, $carrier) = explode('||', $order->getCourierrulesTracking());
            Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $trackingNumber, $carrier);
            $order->setCourierrulesTracking('');
            $order->save();
        }
    }

    public function report_errors()
    {
        if (Mage::getStoreConfig('courierrules/tracking/email_notification')) {
            $order_ids = array();
            $resource = Mage::getSingleton('core/resource');

            $orders = Mage::getModel('sales/order')->getCollection();
            $orders->getSelect()->columns(array('entity_id', 'increment_id'));
            $orders->getSelect()->join(array('processing' => $resource->getTableName('moogento_courierrules/cron_processing')), 'main_table.entity_id = processing.entity_id', '');
            $orders->getSelect()->where('processing.date + INTERVAL 30 MINUTE < NOW() AND mail_sent = 0');

            foreach ($orders as $order) {
                $order_ids[$order->getId()] = $order->getIncrementId();
            }

            if (count($order_ids) > 0) {
                $translate = Mage::getSingleton('core/translate');
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);

                Mage::getModel('core/email_template')
                    ->sendTransactional(
                        Mage::getStoreConfig('courierrules/email/error_report'),
                        Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY),
                        Mage::getStoreConfig('courierrules/tracking/email_notification'),
                        null,
                        array(
                            'order_ids'  => implode(', ', $order_ids),
                        )
                    );

                $translate->setTranslateInline(true);

                $writeConnection = $resource->getConnection('core_write');
                $writeConnection->query("UPDATE {$resource->getTableName('moogento_courierrules/cron_processing')} set mail_sent = 1 where entity_id in (" . implode(',', array_keys($order_ids)) . ")");
            }
        }
    }

    public function moogento_core_order_grid_columns($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();
        $displayOriginal = Mage::getStoreConfigFlag('courierrules/settings/order_grid_original');
        $displayCourierRules = Mage::getStoreConfigFlag('courierrules/settings/order_grid');

        $grid->addCustomColumn('shipping_description', array(
                'header' => Mage::helper('moogento_courierrules')->__('Shipping Method'),
                'index' => 'shipping_description',
                'after' => 'shipping_name',
                'visible' => $displayOriginal,
        ));
        $grid->addCustomColumn('courierrules_description', array(
                'header'  => Mage::helper('moogento_courierrules')->__('Courier Rules Method'),
                'index'   => 'courierrules_description',
                'type'    => 'options',
                'after' => $displayOriginal ? 'shipping_description' : 'shipping_name',
                'visible' => $displayCourierRules,
                'filter' => 'moogento_courierrules/adminhtml_column_filter_courierrulesdescription',
                'filter_condition_callback' => 'Moogento_CourierRules_Helper_Data::setCourierRulesMethodFilter',
                'renderer' => 'moogento_courierrules/adminhtml_column_renderer_courierrulesdescription',
                'column_css_class' => 'courierrules_td',
            ));

        $grid->addCustomColumn('connector', array(
            'header'  => Mage::helper('moogento_courierrules')->__('Shipping connector'),
            'index'   => 'connector',
            'filter' => 'moogento_courierrules/adminhtml_column_filter_connector',
            'filter_condition_callback' => 'Moogento_CourierRules_Helper_Data::setConnectorFilter',
            'renderer' => 'moogento_courierrules/adminhtml_column_renderer_connector',
            'column_css_class' => 'a-center',
        ));
    }

    public function moogento_core_order_grid_actions($observer)
    {
        /** @var Moogento_Core_Block_Adminhtml_Sales_Order_Grid $grid */
        $grid = $observer->getEvent()->getGrid();
        if(Mage::getStoreConfig('courierrules_rules/action_menu/process_courier_rules')){
            $grid->getMassactionBlock()
                ->addItem('reprocess_courierrules', array(
                    'label'=> Mage::helper('moogento_courierrules')->__('Process Courier Rules'),
                    'url'  => $grid->getUrl('*/sales_processing/massProcessCourierRules'),
                ));
        }
        if(Mage::getStoreConfig('courierrules_rules/action_menu/set_cr_method')){
            $grid->getMassactionBlock()        
                ->addItem('set_cr_method', array(
                    'label'=> Mage::helper('moogento_courierrules')->__('cR Set courierRules Method'),
                    'url'  => $grid->getUrl('*/courierrules_rule/updateCourierRuleOrders'),
                    'additional' => $this->_getCRMetodBlock('set_cr_method'),
                    'func' => 'setCRMethodsFromGridMassaction',
                ));
        }
        if(Mage::getStoreConfig('courierrules_rules/action_menu/print_raw_connector_label')){
            $grid->getMassactionBlock()
                ->addItem('print_connector_labels', array(
                    'label'=> Mage::helper('moogento_courierrules')->__('Print Raw Connector Label'),
                    'url'  => $grid->getUrl('*/sales_processing/massPrintConnectorLabels'),
                ));
        }
    }
    
    public function moogento_shipeasy_system_config_grid_get_additional_fields($observer)
    {
        $data = $observer->getEvent()->getData('column');
        $columnId = $data->getData('column_id');
        $fields = $data->getData('fields');

        if($columnId == "courierrules_description"){
            $value = @unserialize(Mage::getStoreConfig('moogento_shipeasy/grid/courierrules_description_status_group'));
            $values = array();
            if (is_array($value)) {
                foreach ($value as $name => $courierrules) {
                    $values[] = array(
                        'name'     => $name,
                        'courierrules' => $courierrules["courierrules"],
                        'custom_value' => $courierrules["custom_value"],
                    );
                }
            }
            $fields[] = array(
                'key' => 'courierrules_group',
                'label' => Mage::helper('moogento_courierrules')->__("Order Courierrules Groups"),
                'type' => 'serializable_table',
                'fields' => array(
                    array(
                        'key' => 'name',
                        'label' => Mage::helper('moogento_courierrules')->__("Name"),
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'courierrules',
                        'label' => Mage::helper('moogento_courierrules')->__("Courierrules"),
                        'type' => 'multiselect',
                        'options' => Mage::helper('moogento_courierrules')->getCourierRulesToOptionsArray(),
                    ),
                    array(
                        'key' => 'custom_value',
                        'label' => Mage::helper('moogento_courierrules')->__("Custom value"),
                        'type' => 'text',
                        'visible' => array('courierrules' => 'custom_value'),
                    )
                ),
                'value' => $values,
            );
            $data->setData('fields',$fields);
        }
    }
    
    private function _getCRMetodBlock($action)
    {
        $block = Mage::app()->getLayout()->createBlock('moogento_courierrules/adminhtml_sales_order_grid_massaction_crmethod');
        $block->setActionCode($action);
        return $block;
    }

    public function moogento_core_order_get_shipping_method($observer)
    {
        if (Mage::getStoreConfig('courierrules/settings/replace_shipment')) {
            $order = $observer->getOrder();
            $values = $observer->getValues();
            if ($order->getCourierrulesProcessed() && $order->getCourierrules()) {
                if ($values->getAsObject()) {
                    $values->getMethod()->addData(array(
                        'carrier_code' => 'courierrules',
                        'method'       => 'custom',
                    ));
                } else {
                    $values->setMethod('courierrules_custom');
                }
            }
        }
    }

    public function moogento_core_order_get_shipping_description($observer)
    {
        if (Mage::getStoreConfig('courierrules/settings/replace_shipment')) {
            $order = $observer->getOrder();
            $values = $observer->getValues();
            if ($order->getCourierrulesProcessed() && $order->getCourierrules()) {
                $values->setDescription($order->getCourierrulesDescription());
            }
        }
    }
} 