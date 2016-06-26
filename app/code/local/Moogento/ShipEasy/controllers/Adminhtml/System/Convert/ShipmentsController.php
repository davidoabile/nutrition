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
* File        ShipmentsController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Adminhtml_System_Convert_ShipmentsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('system/convert')
        ->_addBreadcrumb($this->__('System'), $this->__('System'))
        ->_addBreadcrumb($this->__('Import Shipments'), $this->__('Process Orders : CSV'));

        $this->_title($this->__('System'))->_title($this->__('Process Orders : CSV'));
        
        $this->renderLayout();
    }

    public function postAction()
    {
        if (isset($_FILES['import_file']['tmp_name']) && $file = $_FILES['import_file']['tmp_name']) {
            try {
                $uploader = new Varien_File_Uploader('import_file');
                $uploader->setAllowedExtensions(array('csv'));
                $path = Mage::app()->getConfig()->getTempVarDir().'/import/';
                $uploader->save($path);
                if ($uploadFile = $uploader->getUploadedFileName()) {
                    $newFilename = 'import-'.date('YmdHis').'-track_import_'.$uploadFile;
                    rename($path.$uploadFile, $path.$newFilename);
                }
            } catch(Exception $e) {
                $this->_getSession()->addError($this->__($e->getMessage()));
                $this->_redirect('*/*/index');
                return;
            }
            if (isset($newFilename) && $newFilename) {
                $contents = file_get_contents($path.$newFilename);
                if (ord($contents[0]) == 0xEF && ord($contents[1]) == 0xBB && ord($contents[2]) == 0xBF) {
                    $contents = substr($contents, 3);
                    file_put_contents($path.$newFilename, $contents);
                }
                unset($contents);
            }
            
            $additionalParams = array();
            
            // $_GET
			// $productId = Mage::app()->getRequest()->getParam('product_id');
// 			// The second parameter to getParam allows you to set a default value which is returned if the GET value isn't set
// 			$productId = Mage::app()->getRequest()->getParam('product_id', 44);
// 			$postData = Mage::app()->getRequest()->getPost();
// 			// You can access individual variables like...
// 			$productId = $postData['product_id']);
            $additionalParams['notify_customer'] = $this->getRequest()->getParam('notify_customer', 0);
            $additionalParams['action'] = $this->getRequest()->getParam('action', 'ship');
            $additionalParams['status'] = $this->getRequest()->getParam('status', 'pending');

            for($i=1; $i<=3; $i++) {
                $useCustomAttribute = $this->getRequest()->getParam("additional_action_$i", 0);
                $additionalParams["additional_action_$i"] = $useCustomAttribute;
                if ($useCustomAttribute) {
                    $value = $this->getRequest()->getParam("attr_preset_$i", 0);
                    if ($value == 'custom') {
                        $value = $this->getRequest()->getParam("attr_custom_value_$i", 0);
                    }
                    $additionalParams["additional_action_value_$i"] = trim($value);
                }
            }

            $profile = Mage::helper('moogento_shipeasy/track_import')
                ->getImportProfile(
                    'var/import',
                    $newFilename,
                    $additionalParams
                );



            /**
             * Dummy id to pass ID checks
             */
            $profile->setId(1000);
            Mage::register('current_convert_profile', $profile);

            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_getSession()->addError(Mage::helper('moogento_shipeasy')->__('Can\'t find import file :('));
            $this->_redirect('*/*/index');
        }
    }

    public function batchRunAction()
    {
        if ($this->getRequest()->isPost()) {
            $batchId = $this->getRequest()->getPost('batch_id',0);
            $rowIds  = $this->getRequest()->getPost('rows');

            $batchModel = Mage::getModel('dataflow/batch')->load($batchId);
            /* @var $batchModel Mage_Dataflow_Model_Batch */

            if (!$batchModel->getId()) {
                return ;
            }
            if (!is_array($rowIds) || count($rowIds) < 1) {
                return ;
            }
            if (!$batchModel->getAdapter()) {
                return ;
            }

            $batchImportModel = $batchModel->getBatchImportModel();
            $importIds = $batchImportModel->getIdCollection();

            $adapter = Mage::getModel($batchModel->getAdapter());
            $adapter->setBatchParams($batchModel->getParams());

            $errors = array();
            $saved  = 0;

            $additionalParams = array(
                'action'          => 'ship',
                'notify_customer' => 0,
                'status' => 'pending',
                'additional_action_1' => 0,
                'additional_action_value_1' => '',
                'additional_action_2' => 0,
                'additional_action_value_2' => '',
                'additional_action_3' => 0,
                'additional_action_value_3' => '',
            );
            $batchParams = unserialize($batchModel->getData('params'));


            foreach($additionalParams as $key => $value) {
                if (!empty($batchParams[$key])) {
                    $additionalParams[$key] = $batchParams[$key];
                }
            }

            
            $session = Mage::getSingleton('core/session');
            $session->setData('notify_customer_for_special_import', $additionalParams['notify_customer']);
            foreach ($rowIds as $importId) {
                $batchImportModel->load($importId);
                if (!$batchImportModel->getId()) {
                    $errors[] = Mage::helper('dataflow')->__('Skip undefined row.');
                    continue;
                }

                try {
                    $importData = $batchImportModel->getBatchData();
                    $adapter->saveRow($importData, $additionalParams);
                }
                catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    continue;
                }
                $saved ++;
            }

            $result = array(
                'savedRows' => $saved,
                'errors'    => $errors
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function batchFinishAction()
    {
        if ($batchId = $this->getRequest()->getParam('id')) {
            $batchModel = Mage::getModel('dataflow/batch')->load($batchId);
            /* @var $batchModel Mage_Dataflow_Model_Batch */

            if ($batchModel->getId()) {
                $result = array();
                try {
                    $this->_savedImportShipment();
                    $batchModel->beforeFinish();
                }
                catch (Mage_Core_Exception $e) {
                    $result['error'] = $e->getMessage();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                    $result['error'] = Mage::helper('adminhtml')->__('An error occurred while finishing processing. ');
                }
                $batchModel->delete();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    }
    
    private function _savedImportShipment()
    {
        $session = Mage::getSingleton('core/session');
        $notifyCustomer = $session->getData('notify_customer_for_special_import');
        $session->unsetData('notify_customer_for_special_import');
        if($session->getData('import_data_for_save')){
            $orders_data = $session->getData('import_data_for_save');
            $session->unsetData('import_data_for_save');
            foreach($orders_data as $order_id => $order_data){
                $order = Mage::getModel('sales/order')->load($order_id, 'increment_id');
                foreach($order_data as $tracking_number => $data) {
                    $itemsarray = array();
                    foreach($order->getAllItems() as $item) {
                        if(array_key_exists($item->getSku(),$data)){
                            $item_id = (int)$item->getItemId();
                            if ($item->getParentId()) $item_id = (int)$item->getParentId();
                            $itemsarray[$item_id] = $data[$item->getSku()] === false ? (int)$item->getQtyOrdered() : $data[$item->getSku()];
                            if ($itemsarray[$item_id] > (int)$item->getQtyOrdered() - (int)$item->getQtyCancelled() - (int)$item->getQtyShipped()) {
                                $itemsarray[$item_id] = (int)$item->getQtyOrdered() - (int)$item->getQtyCancelled() - (int)$item->getQtyShipped();
                            }
                            if (!$itemsarray[$item_id]) {
                                unset($itemsarray[$item_id]);
                            }
                        }
                    }
                    if($order->canShip() && count($itemsarray)){
                        $shipmentIncrementId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsarray ,'Created by import' ,false,1);
                        if ($tracking_number != '__notracking__') {
                            Mage::getModel('sales/order_shipment_api')->addTrack(
                                $shipmentIncrementId, 'custom', 'Custom', $tracking_number
                            );
                        }
                        if($notifyCustomer){
                            try{
                                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentIncrementId, 'increment_id');
                                $shipment->sendEmail($notifyCustomer);
                                $shipment->setEmailSent($notifyCustomer);
                                $shipment->save();
                                Mage::log('Send email for shipment #'.$shipmentIncrementId.' : '.date('d/m/y H:i.s'), null, 'szy.log');
                            } catch (Exception $ex) {
                                Mage::log($ex.' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
                            }
                        }
                    }
                }
            }
        }
    }
}
