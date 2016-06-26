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


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Observer
{
    protected $_websiteCodeFields = null;
    protected $_groupCodeFields = null;
    protected $_storeCodeFields = null;

    protected $_flagsCache = array();

    protected function _getWebsiteCodeFields()
    {
        if (is_null($this->_websiteCodeFields)) {
            $this->_websiteCodeFields = array();
            foreach(Mage::app()->getWebsites() as $website) {
                $this->_websiteCodeFields[] = 'szy_website_id_'.$website->getCode().'_logo';
            }
        }
        return $this->_websiteCodeFields;
    }

    protected function _getStoreCodeFields()
    {
        if (is_null($this->_storeCodeFields)) {
            $this->_storeCodeFields = array();
            foreach(Mage::app()->getWebsites() as $website) {
                foreach($website->getStores() as $store)
                {
                    $this->_storeCodeFields[] = 'szy_store_id_store_view_'.$store->getCode().'_logo';                       
                }   
            }
        }

        return $this->_storeCodeFields;
    }

    protected function _getGroupCodeFields()
    {
        if (is_null($this->_groupCodeFields)) {
            $this->_groupCodeFields = array();
            foreach(Mage::app()->getWebsites() as $website) {
                foreach($website->getGroups() as $group)
                {
                    $this->_groupCodeFields[] = 'szy_store_name_store_view_'.$group->getId().'_logo';                       
                }   
            }
        }
        return $this->_groupCodeFields;
    }

    public function model_config_data_save_before($observer) 
    {
        $this->_flagsCache['mkt_order_id_show_ebay_sales_number'] = Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_ebay_sales_number');
        $this->_flagsCache['mkt_order_id_show_mkt_link'] = Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_mkt_link');
        $this->_flagsCache['szy_custom_product_attribute_inside'] = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside');
        $this->_flagsCache['szy_custom_product_attribute2_inside'] = Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside');
    }

    public function saveBefore($observer)
    {
        $configElm = $observer->getConfigData();

        $session = Mage::getSingleton('adminhtml/session');
        $session->unsetData('sales_order_gridfilter');
        
        if (in_array($configElm->getData('field'), $this->_getWebsiteCodeFields())) {

            $value = $configElm->getValue();
            if (is_array($value) && isset($value['delete'])) {
                $configElm->setValue('');
            } else if ($value) {
                if (isset($_FILES['groups'])) {
                    if (!empty($_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'])) {

                        $_FILES[$configElm->getData('field')]['name'] = $_FILES['groups']['name']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['type'] = $_FILES['groups']['type']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['tmp_name'] = $_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['error'] = $_FILES['groups']['error']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['size'] = $_FILES['groups']['size']['grid']['fields'][$configElm->getData('field')]['value'];

                        try {
                            $uploader = new Varien_File_Uploader($configElm->getData('field'));
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setAllowCreateFolders(true);
                            $uploader->save(Mage::getBaseDir('media').DS.'moogento/shipeasy/szy_websites');
                            $filename = $uploader->getUploadedFileName();
                            $configElm->setValue(Mage::getBaseUrl('media') . 'moogento/shipeasy/szy_websites/' . $filename);
                        } catch (Exception $e) {
                            $configElm->setValue('');
                            throw $e;
                        }
                    }
                }
            }
        }
        else
            if (in_array($configElm->getData('field'), $this->_getStoreCodeFields())) {
            
            $value = $configElm->getValue();
            if (is_array($value) && isset($value['delete'])) {
                $configElm->setValue('');
            } else if ($value) {
                if (isset($_FILES['groups'])) {
                    if (!empty($_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'])) {

                        $_FILES[$configElm->getData('field')]['name'] = $_FILES['groups']['name']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['type'] = $_FILES['groups']['type']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['tmp_name'] = $_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['error'] = $_FILES['groups']['error']['grid']['fields'][$configElm->getData('field')]['value'];
                        $_FILES[$configElm->getData('field')]['size'] = $_FILES['groups']['size']['grid']['fields'][$configElm->getData('field')]['value'];

                        try {
                            $uploader = new Varien_File_Uploader($configElm->getData('field'));
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setAllowCreateFolders(true);
                            $uploader->save(Mage::getBaseDir('media').DS.'moogento/shipeasy/szy_stores');
                            $filename = $uploader->getUploadedFileName();
                            $configElm->setValue(Mage::getBaseUrl('media') . 'moogento/shipeasy/szy_stores/' . $filename);
                        } catch (Exception $e) {
                            $configElm->setValue('');
                            throw $e;
                        }
                    }
                }
            }
        } else
            if (in_array($configElm->getData('field'), $this->_getGroupCodeFields())) {

                $value = $configElm->getValue();
                if (is_array($value) && isset($value['delete'])) {
                    $configElm->setValue('');
                } else if ($value) {
                    if (isset($_FILES['groups'])) {
                        if (!empty($_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'])) {

                            $_FILES[$configElm->getData('field')]['name'] = $_FILES['groups']['name']['grid']['fields'][$configElm->getData('field')]['value'];
                            $_FILES[$configElm->getData('field')]['type'] = $_FILES['groups']['type']['grid']['fields'][$configElm->getData('field')]['value'];
                            $_FILES[$configElm->getData('field')]['tmp_name'] = $_FILES['groups']['tmp_name']['grid']['fields'][$configElm->getData('field')]['value'];
                            $_FILES[$configElm->getData('field')]['error'] = $_FILES['groups']['error']['grid']['fields'][$configElm->getData('field')]['value'];
                            $_FILES[$configElm->getData('field')]['size'] = $_FILES['groups']['size']['grid']['fields'][$configElm->getData('field')]['value'];

                            try {
                                $uploader = new Varien_File_Uploader($configElm->getData('field'));
                                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                                $uploader->setAllowRenameFiles(true);
                                $uploader->setAllowCreateFolders(true);
                                $uploader->save(Mage::getBaseDir('media').DS.'moogento/shipeasy/szy_groups');
                                $filename = $uploader->getUploadedFileName();
                                $configElm->setValue(Mage::getBaseUrl('media') . 'moogento/shipeasy/szy_groups/' . $filename);
                            } catch (Exception $e) {
                                $configElm->setValue('');
                                throw $e;
                            }
                        }
                    }
                }
            }            
        return $this;
    }

    public function admin_system_config_changed_section_moogento_shipeasy($observer)
    {

        $section = Mage::app()->getRequest()->getParam('section');
        if ($section == 'moogento_shipeasy') {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
                if ($this->_flagsCache['mkt_order_id_show_ebay_sales_number']
                    != Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_ebay_sales_number')
                    || $this->_flagsCache['mkt_order_id_show_mkt_link']
                       != Mage::getStoreConfigFlag('moogento_shipeasy/grid/mkt_order_id_show_mkt_link')
                ) {
                    $query
                        = "
                        UPDATE " . Mage::getSingleton('core/resource')->getTableName('sales/order_grid') . "
                        SET mkt_order_id = NULL
                        WHERE entity_id in (
                          select magento_order_id from " . Mage::getSingleton('core/resource')
                                                               ->getTableName('M2ePro/Order') . "
                          where magento_order_id is not null
                        )";

                    $write->query($query);
                }
            }

            if ($this->_flagsCache['szy_custom_product_attribute_inside'] != Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside')
                || $this->_flagsCache['szy_custom_product_attribute2_inside'] != Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside'))
            {
                if ($this->_flagsCache['szy_custom_product_attribute_inside'] != Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute_inside')) {
                    $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('sales/order_grid') . ' SET szy_custom_product_attribute = NULL');
                }
                if ($this->_flagsCache['szy_custom_product_attribute2_inside'] != Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_product_attribute2_inside')) {
                    $write->query('UPDATE ' . Mage::getSingleton('core/resource')->getTableName('sales/order_grid') . ' SET szy_custom_product_attribute2 = NULL');
                }

                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('moogento_shipeasy')->__('The product attribute columns may be incorrect for some time untill they get filled in'));
            }
        }
    }
}
