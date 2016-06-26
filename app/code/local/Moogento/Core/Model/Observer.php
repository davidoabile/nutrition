<?php


class Moogento_Core_Model_Observer
{

    public function checkConflict() {

        if (Mage::helper('moogento_core')->isInstalled('BL_CustomGrid') && Mage::getStoreConfigFlag('moogento_shipeasy/general/override_bl_grid')) {
            $value = Mage::getStoreConfig('customgrid/global/exclusions_list');

            try {
                $value = @unserialize($value);
            } catch (Exception $e) {

            }
            $flagSalesOrderGrid = 0;
            $flagCoreOrder      = 0;
            $flagCoreOrderGrid  = 0;
            if (!empty($value) && is_array($value)) {
                foreach ($value as $configData) {
                    if (isset($configData['block_type']) && isset($configData['rewriting_class_name'])) {
                        if ($configData['block_type'] == 'adminhtml/sales_order') {
                            $flagSalesOrderGrid = 1;
                        } elseif ($configData['block_type'] == 'moogento_core/adminhtml_sales_order') {
                            $flagCoreOrder = 1;
                        } elseif ($configData['block_type'] == 'moogento_core/adminhtml_sales_order_grid') {
                            $flagCoreOrderGrid = 1;
                        }
                    }
                }
            } else {
                $value = array();
            }

            if ($flagSalesOrderGrid == 0) {
                $value[] = array(
                    'block_type'           => 'adminhtml/sales_order',
                    'rewriting_class_name' => '*',
                );
            }

            if ($flagCoreOrder == 0) {
                $value[] = array(
                    'block_type'           => 'moogento_core/adminhtml_sales_order',
                    'rewriting_class_name' => '*',
                );
            }
            if ($flagCoreOrderGrid == 0) {
                $value[] = array(
                    'block_type'           => 'moogento_core/adminhtml_sales_order_grid',
                    'rewriting_class_name' => '*',
                );
            }

            if ($flagSalesOrderGrid == 0 || $flagCoreOrder == 0 || $flagCoreOrderGrid == 0) {
                $updateConfig = new Mage_Core_Model_Config();
                $updateConfig->saveConfig('customgrid/global/exclusions_list', serialize($value), 'default', 0);
            }
        }

        return $this;
    }
    
    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();

        $section = $request->getParam('section');
        $config = new Mage_Core_Model_Config();
        switch ($section) {
            case 'moogento_core':

                $groups = $request->getPost('moogento_core', array());
                $config->saveConfig('moogento_core/config/use_custom_address_formatting', $groups["config"]["use_custom_address_formatting"]);
                $groups = $request->getPost('country_template_base_format', array());
                foreach($groups as $group){
                    $id = $group['id'];
                    if(empty($id))
                        unset( $group['id'] );
                    $model = Mage::getModel("moogento_core/country_template");
                    $model->setData($group);
                    $model->save();
                }
                break;
            case 'moogento_carriers':
                $postData = $request->getPost('carriers_list', array());
                $toSave = array();
                foreach ($postData as $id => $row) {
                    if (!empty($_FILES['carriers_list']['tmp_name'][$id]['file'])) {
                        $_FILES[$id]['name']     = $_FILES['carriers_list']['name'][$id]['file'];
                        $_FILES[$id]['type']     = $_FILES['carriers_list']['type'][$id]['file'];
                        $_FILES[$id]['tmp_name'] = $_FILES['carriers_list']['tmp_name'][$id]['file'];
                        $_FILES[$id]['error']    = $_FILES['carriers_list']['error'][$id]['file'];
                        $_FILES[$id]['size']     = $_FILES['carriers_list']['size'][$id]['file'];

                        try {
                            $uploader = new Varien_File_Uploader($id);
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setAllowCreateFolders(true);
                            $uploader->save(Mage::getBaseDir('media').DS.'moogento/core/carriers');
                            $filename = $uploader->getUploadedFileName();
                            $row['file'] = $filename;
                        } catch (Exception $e) {
                            throw $e;
                        }

                    } else {
                        if (!isset($row['remove_image'])) {
                            $row['file'] = isset($row['old_file']) ? $row['old_file'] : '';
                        } else {
                            $row['file'] = '';
                        }
                    }
                    $toSave[] = $row;
                }
                $config->saveConfig('moogento_carriers/formats/list', @serialize($toSave));
                break;
        }
    }

    public function adminhtml_controller_action_predispatch_start()
    {
        if(!Mage::getStoreConfigFlag('moogento_cron/settings/disable_checks') && Mage::getSingleton('admin/session')->isLoggedIn()) {
            $lastCheck = Mage::getStoreConfig('moogento/cron/checked');
            if (!Mage::registry('moogento_cron_checked') && time() - $lastCheck > 30 * 60) {
                Mage::register('moogento_cron_checked', true);
                $schedules = Mage::getModel('cron/schedule')->getCollection();
                /* @var $schedules Mage_Cron_Model_Mysql4_Schedule_Collection */
                $schedules->getSelect()->limit(1)->order('executed_at DESC');
                $schedules->getSelect()->where('job_code LIKE "moogento_%"');
                $schedules->load();

                $cronProblems = false;
                if (!count($schedules)) {
                    $cronProblems = true;
                } else {
                    $schedule = $schedules->getFirstItem();
                    $executed = $schedule->getData('executed_at');
                    $diff     = strtotime(Mage::getModel('core/date')->gmtDate()) - strtotime($executed);
                    if ($diff > 60 * 60) {
                        $cronProblems = true;
                    }
                }

                if ($cronProblems) {
                    Mage::getSingleton('adminhtml/session')
                        ->addError(Mage::helper('moogento_core')
                                       ->__('Warning! Your Magento cron seems to not be running. You need this for your Moogento extensions to run well. (<a target="_blank" href="//moogento.com/guides/Installing_Magento_Extensions_:_Pre-install_Checklist#3._Check_your_cron_is_running">Help?</a>)'));
                }

                $config = new Mage_Core_Model_Config();
                $config->saveConfig('moogento/cron/checked', time());
                Mage::getConfig()->reinit();
            }
        }
    }

    public function sales_order_save_commit_after($observer)
    {
        $cache = Mage::app()->getCache();
        $cache->clean('matchingAnyTag', array('moogento_cache'));
    }
} 