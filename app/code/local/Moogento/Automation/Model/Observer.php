<?php

class Moogento_Automation_Model_Observer
{
    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();
        $section = $request->getParam('section');

        switch ($section) {
            case 'moogento_automation':
                $post_data = $request->getPost('automation_status_update', array());
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('moogento_automation/update/status', serialize($post_data));
                
                $import_data = $request->getPost('import_settings') ? array_values($request->getPost('import_settings')) : array();
                if (!$import_data) {
                    $import_data = array();
                }
                $config->saveConfig('moogento_automation/config/import_shipeasy_csv_options', Mage::helper('core')->jsonEncode($import_data));
                
                break;
        }
    }
}