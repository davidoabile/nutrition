<?php

class Moogento_Clean_Model_Observer 
{
    protected $_flagsCache = array();
    protected $_statusCache = array();

    public function overrideTheme()
    {
        Mage::getDesign()->setArea('adminhtml')
            ->setTheme((string)Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_THEME));
    }

    public function sales_order_save_before($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->isObjectNew()
            || $order->dataHasChangedFor('status')
            || $order->dataHasChangedFor('state'))
        {
            $this->_updateDirtyForOrder($order);
        } else {
            $this->_statusCache[$order->getId()] = array(
                'status' => $order->getStatus(),
                'state' => $order->getState(),
            );
        }
    }

    public function sales_order_save_commit_after($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if (isset($this->_statusCache[$order->getId()])) {
            if ($this->_statusCache[$order->getId()]['status'] != $order->getStatus()
            || $this->_statusCache[$order->getId()]['state'] != $order->getState())
            {
                $this->_updateDirtyForOrder($order);
            }
        }
    }

    protected function _updateDirtyForOrder($order)
    {
        $createdAt = new DateTime($order->getCreatedAt());
        $createdAt->setTimezone(new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $aggregatesTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');
        $aggregatesDayTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day');
        $aggregatesBestsellersTable   = Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_bestsellers');

        $query = "INSERT INTO {$aggregatesTable} (date, store_id, is_dirty) values('{$createdAt->format('Y-m-d')}', {$order->getStoreId()}, 1) ON DUPLICATE KEY UPDATE is_dirty = 1";
        $write->query($query);
        $query = "INSERT INTO {$aggregatesDayTable} (date, store_id, is_dirty) values('{$createdAt->format('Y-m-d H:00:00')}', {$order->getStoreId()}, 1) ON DUPLICATE KEY UPDATE is_dirty = 1";
        $write->query($query);

        foreach ($order->getAllItems() as $item) {
            $query = "INSERT INTO {$aggregatesBestsellersTable} (date, store_id, sku, is_dirty) values('{$createdAt->format('Y-m-d')}', {$order->getStoreId()}, '{$item->getSku()}', 1) ON DUPLICATE KEY UPDATE is_dirty = 1";
            $write->query($query);
        }
    }

    public function adminhtml_catalog_product_edit_element_types($observer)
    {
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();

        $types['date'] = Mage::getConfig()->getBlockClassName('moogento_clean/helper_date');
        $types['datetime'] = Mage::getConfig()->getBlockClassName('moogento_clean/helper_datetime');

        $response->setTypes($types);
    }

    public function adminhtml_cms_page_edit_tab_design_prepare_form($observer)
    {
        $form = $observer->getEvent()->getForm();

        $form->getElement('design_fieldset')->addType('date', Mage::getConfig()->getBlockClassName('moogento_clean/helper_date'));
        $form->getElement('design_fieldset')->addType('datetime', Mage::getConfig()->getBlockClassName('moogento_clean/helper_datetime'));
    }

    public function orderAfterSave($observer)
    {
        $order = $observer->getOrder();
        $ordered_items = $order->getAllItems();
        foreach ($ordered_items as $key => $val){
            $product = Mage::getModel('catalog/product')->load($val->getProductId());
            $stockItem = $product->getStockItem();
            if(!$stockItem->getIsInStock()){
                $notification = Mage::getModel('moogento_clean/notification');
                $notification->setProductId($product->getId());
                $notification->setCreateAt(date('Y-m-d H:i:s'));
                $notification->save();
            }
        }
    }

    
    public function adminhtml_block_html_before($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid
            || (Mage::helper('moogento_clean')->isInstalled('Moogento_Core') && $block instanceof Moogento_Core_Block_Adminhtml_Sales_Order_Grid)) {
            if (Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_THEME) == 'extended') {
                if (Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_ACTION_GRID_CSS)) {
                    if (Mage::helper('moogento_clean')->isInstalled('Moogento_ShipEasy')) {
                        $block->getMassactionBlock()
                              ->setTemplate('moogento/shipeasy/sales/order/grid/massaction.phtml');
                        $block->setTemplate('moogento/clean/shipeasy/sales/order/grid.phtml');
                    } else {
                        $block->getMassactionBlock()->setTemplate('moogento/clean/widget/grid/massaction.phtml');
                    }
                }
            } else {
                $block->getMassactionBlock()
                      ->setTemplate('widget/grid/massaction.phtml');
            }
            if (Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_REFRESH)) {
                /** @var Zend_Db_Select $select */
                $select = clone $block->getCollection()->getSelect();
                $select->reset(Zend_Db_Select::COLUMNS);
                $select->columns(new Zend_Db_Expr('MAX(main_table.created_at)'));
                $select->reset(Zend_Db_Select::LIMIT_COUNT);
                $select->reset(Zend_Db_Select::LIMIT_OFFSET);
                $select->reset(Zend_Db_Select::ORDER);

                $maxDate = $select->query()->fetchColumn();

                $block->setAdditionalJavaScript("
                setInterval(function(){
                    var max_date = '{$maxDate}';

                    var filters = $$('#{$block->getId()} .filter input', '#{$block->getId()} .filter select');
                    var elements = [];
                    for(var i in filters){
                        if(filters[i].value && filters[i].value.length) elements.push(filters[i]);
                    }

                    new Ajax.Request('{$block->getUrl('*/sales_order_additional/check')}?filter=' + encode_base64(Form.serializeElements(elements)) + '&date=' + max_date, {
                        onSuccess: function(response) {
                            var result = response.responseText.evalJSON();
                            if (result.need_update) {
                                {$block->getJsObjectName()}.doFilter();
                            }
                        }
                    });

                }, " . Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_REFRESH_INTERVAL) ."  * 60 * 1000);
            ");
            }
        }
    }

    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();

        $section = $request->getParam('section');
        switch ($section) {
            case 'moogento_clean':
                $post_data = $request->getPost('groups');
                $post_data = (array) $post_data;
                if (isset($post_data['dashboard']['fields']['cron_period'])) {

                    $period = (int)$post_data['dashboard']['fields']['cron_period']['value'];
                    if (!$period || $period == 1) {
                        $value = '* * * * *';
                    } else {
                        $value = '*/' . $period . ' * * * *';
                    }
                    Mage::log($value);
                    foreach (array(
                        'moogento_clean_update_dashboard_aggregates',
                        'moogento_clean_update_dashboard_aggregates_day',
                        'moogento_clean_update_dashboard_aggregates_bestsellers',
                        'moogento_clean_update_dashboard_aggregates_visitors',
                    ) as $cronKey) {
                        $cronPath = 'crontab/jobs/' . $cronKey . '/schedule/cron_expr';

                        try {
                            Mage::getModel('core/config_data')
                                ->load($cronPath, 'path')
                                ->setValue($value)
                                ->setPath($cronPath)
                                ->save();
                        } catch (Exception $e) {}
                    }
                }
                break;
        }
    }

    public function model_config_data_save_before($observer)
    {
        $this->_flagsCache['status_list'] = Mage::getStoreConfig('moogento_clean/dashboard/chart_statuses');
    }

    public function admin_system_config_changed_section_moogento_clean($observer)
    {
        $section = Mage::app()->getRequest()->getParam('section');
        if ($section == 'moogento_clean') {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            if ($this->_flagsCache['status_list'] != Mage::getStoreConfig('moogento_clean/dashboard/chart_statuses'))
            {
                $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates') . ' SET is_dirty = 1');
                $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day') . ' SET is_dirty = 1');
                $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_bestsellers') . ' SET is_dirty = 1');
            }
        }
    }
} 