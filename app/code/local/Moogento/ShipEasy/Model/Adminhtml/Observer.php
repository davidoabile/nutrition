<?php

class Moogento_ShipEasy_Model_Adminhtml_Observer
{
    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();

        $section = $request->getParam('section');
        $config  = new Mage_Core_Model_Config();
        switch ($section) {
            case 'moogento_shipeasy':
                $groups   = $request->getPost('status_group_table', array());
                $newValue = array();
                foreach ($groups as $statusGroup) {
                    $statuses                         = (!empty($statusGroup['statuses'])
                                                         && count($statusGroup['statuses']))
                        ? $statusGroup['statuses']
                        : array();
                    $newValue[ $statusGroup['name'] ] = $statuses;
                }

                $config->saveConfig('moogento_shipeasy/grid/szy_status_status_group', serialize($newValue));

                $groups = $request->getPost('method_group_table', array());
                $config->saveConfig('moogento_shipeasy/grid/szy_shipping_method_method_group', serialize($groups));

                $post_data = $request->getPost('groups');
                $post_data = (array) $post_data;
                if (!isset($post_data['grid']['fields']["backorder_transparent_status"])) {
                    $config->saveConfig('moogento_shipeasy/grid/backorder_transparent_status', '');
                }

                if (isset($post_data['general']['fields']['cron_period'])) {

                    $period = (int)$post_data['general']['fields']['cron_period']['value'];
                    if (!$period || $period == 1) {
                        $value = '* * * * *';
                    } else {
                        $value = '*/' . $period . ' * * * *';
                    }
                    foreach (array(
                        'moogento_shipeasy_mkt_order_id_update',
                        'moogento_shipeasy_timezone_update',
                        'moogento_shipeasy_ebay_items_links_update',
                        'moogento_shipeasy_fill_columns',
                        'moogento_shipeasy_fix_old_columns',
                        'moogento_shipeasy_fill_product_columns'
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
                
                $import_data = $request->getPost('import_settings') ? array_values($request->getPost('import_settings')) : array();
                if (!$import_data) {
                    $import_data = array();
                }
                $config->saveConfig('moogento_shipeasy/config/import_options', Mage::helper('core')->jsonEncode($import_data));
                break;
        }
    }
    
    public function customer_save_after($observer)
    {
        $customer = $observer->getCustomer();
        $new_email = $customer->getEmail();
        $old_email = $customer->getOrigData('email');

        if($new_email != $old_email){
            $orders = Mage::getResourceModel('sales/order_grid_collection')->addFilter('main_table.customer_id', $customer->getId());
            foreach ($orders as $order){
                $customer_email_list = $order->getCustomerEmailList();
                if(strpos($customer_email_list, $new_email) === false){
                    $customer_email_list = $new_email.' '.$customer_email_list;
                } else {
                    $customer_email_list = $new_email.' '.str_replace($new_email, "", $customer_email_list);
                }
                $order->setCustomerEmailList($customer_email_list);
                Mage::helper('moogento_shipeasy/sales')->updateOnlyOrderGrigAttribute($order->getId(),'customer_email_list',$customer_email_list);
            }
        }
    }
}
