<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Abstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Sales_Order_Processor_Abstract2 extends Varien_Object
{
    protected $_configGroupPrefix = '';
    protected $_hiddenOrderFlag = '';
    protected $_fieldPrefix  = 'auto_processing';
    protected $_filePath = 'auto_PickPack2';
    protected $auto_save_folder = 'autoProcessing';


    public function getPdf($orderIds, $storeId = 0)
    {
        return $this;
    }

    protected function _getFileName($orderIds)
    {
        return $this;
    }

    protected function _getConfig($key, $storeId = 0)
    {
        return Mage::getStoreConfig("pickpack_options/{$this->_configGroupPrefix}/{$key}", $storeId);
    }

    protected function _getConfigFlag($key, $storeId = 0)
    {
        return Mage::getStoreConfigFlag("pickpack_options/{$this->_configGroupPrefix}/{$key}", $storeId);
    }
    
    protected function getFlagValue($orderid = null,$flagname = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT '.$flagname.' FROM ' . $table . ' WHERE orderid = '
             .(int)$orderid . ' LIMIT 1';
        
        try{
        $value = $readConnection->fetchOne($query);
        return $value;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    
    protected function updateFlagValue($orderid = null,$flagname = null,$value = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');       
        $newSku = 'new-sku';
        $query = "UPDATE {$table} SET {$flagname} = {$value} WHERE orderid = "
                 . (int)$orderid;
        // try{
        //  $writeConnection->query($query);
        // }
        // catch(Exception $e)
        // {
        // }
    }
    
    protected function insertFlagValue($orderid = null,$flagname = null,$value = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');       
        $newSku = 'new-sku';
        $query = "INSERT INTO {$table} (orderid,{$flagname})
        VALUES ({$orderid}, {$value})";
        try{
            $writeConnection->query($query);
        }
        catch(Exception $e)
        {
        }
    }
    
    protected function _sendAnEmail_old($pdf, $fileName, $storeId = 0)
    {
        $sender = $this->_getConfig("auto_processing_sender", $storeId);
        $sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
        $subject = $this->_getConfig("auto_processing_subject", $storeId);
        $body = $this->_getConfig("auto_processing_body", $storeId);

        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject);
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email'));
        $mailTemplate->setTemplateText($body);
        $mailTemplate->getTemplateType(Mage_Core_Model_Email_Template::TYPE_TEXT);

        $at = new Zend_Mime_Part($pdf->render());
        $at->type = 'application/pdf';
        $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding = Zend_Mime::ENCODING_BASE64;
        $at->filename = $fileName;
        $mailTemplate->getMail()->addAttachment($at);

        $mailTemplate->send($sendTo);

        return $this;
    }

    protected function _sendAnEmail($pdf, $fileName, $storeId = 0)
    {
        $sender = $this->_getConfig("auto_processing_sender_2nd", $storeId);
        $sendTo = $this->_getConfig("auto_processing_send_to_2nd", $storeId);
        $subject = $this->_getConfig("auto_processing_subject_2nd", $storeId);
        $body = $this->_getConfig("auto_processing_body_2nd", $storeId);
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject);
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email'));
        $mailTemplate->setTemplateText($body);
        $mailTemplate->getTemplateType(Mage_Core_Model_Email_Template::TYPE_TEXT);
        $mailTemplate->getMail()->createAttachment($pdf->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $fileName);
        $mailTemplate->send($sendTo);
        return $this;
    }

    public function _sendMail($pdf, $fileName, $storeId = 0)
    {
    	try{
			$sender = $this->_getConfig("auto_processing_sender_2nd", $storeId);
            $senderName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name');
			$senderEmail= Mage::getStoreConfig('trans_email/ident_' . $sender . '/email');
			$sendTo = $this->_getConfig("auto_processing_send_to_2nd", $storeId);
			$subject = $this->_getConfig("auto_processing_subject_2nd", $storeId);
			$body = $this->_getConfig("auto_processing_body_2nd", $storeId);
			$translate  = Mage::getSingleton('core/translate');
			$emailTemplate = Mage::getModel('core/email_template')->loadDefault('moogento_pickpack_auto_processing');
			$emailTemplateVariables = array(); 
			$emailTemplateVariables['body'] = $body;
			$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
			$emailTemplate->setSenderName($senderName);
			$emailTemplate->setSenderEmail($senderEmail);
			$emailTemplate->setTemplateSubject($subject);
			$emailTemplate->getMail()->createAttachment($pdf->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $fileName);
			$emailTemplate->send($sendTo);
            Mage::log('2nd Auto processing: sent mail to '.$sendTo, null, 'moogento_pickpack.log');
 
        }catch(Exception $e)
        {
            Mage::log($e->getMessage(),null, 'moogento_pickpack.log'); 
        }
    	
    }

    protected function _saveAsFile($pdf, $fileName, $storeId = 0)
    {
        try {
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $path = Mage::getBaseDir() . DS . str_replace('/', DS, $this->_getConfig("auto_processing_export_path_2nd", $storeId));
            $io->open(array('path' => $path));
            $io->write($fileName, $pdf->render());
            $io->close();
        } catch (Exception $e) {
            Mage::logException($e);
        }


        return $this;
    }

     protected function _sendToFtp($pdf, $fileName, $storeId = 0)
    {
    	$baseDir = Mage::getBaseDir();
		$varDir = $baseDir.DS.$this->auto_save_folder;		
		$timeOfImport = date('jmY_his');
		$pickPackDir = $varDir.DS.$this->_filePath.DS;
		$io_file = new Varien_Io_File();
		$io_file->checkAndCreateFolder(Mage::getBaseDir('var').DS.'auto_pickPack');
		$io_file->checkAndCreateFolder(Mage::getBaseDir().DS.$this->auto_save_folder.DS.$this->_filePath);
		$port_ftp = $this->_getConfig("auto_processing_ftp_port_2nd", $storeId);
		$port_sftp = $port_ftp;
		if($port_ftp =='')
			$port_ftp = '21';
		if($port_sftp =='')
			$port_sftp = '22';
		$remote_path = $this->_getConfig("auto_processing_ftp_remote_path_2nd", $storeId);
        try { 
            $io = new Varien_Io_Ftp();
            $io->open(array(
                'host' => $this->_getConfig("auto_processing_ftp_host_2nd", $storeId),
                'user' => $this->_getConfig("auto_processing_ftp_user_2nd", $storeId),
                'password' => $this->_getConfig("auto_processing_ftp_password_2nd", $storeId),
                'port' => $port_ftp,
                'path' =>$remote_path,
                'timeout'   => '12'
            ));

//          $io->write($fileName, $pdf->render());
// 			$io->write($pickPackDir.$fileName, $pdf->render());
			$io->write($fileName, $pdf->render());
            $io->close();
        } catch (Exception $e) 
        {
        	try {
        		
				$io = new Varien_Io_Sftp();
				$io->open(array(
					'host' => $this->_getConfig("auto_processing_ftp_host_2nd", $storeId),
					'username' => $this->_getConfig("auto_processing_ftp_user_2nd", $storeId),
					'password' => $this->_getConfig("auto_processing_ftp_password_2nd", $storeId),
					'port' => $port_sftp,
					'path' =>$remote_path,
					'timeout'   => '20'
				));
				// 			$io->write($pickPackDir.$fileName, $pdf->render());
				$io->write($fileName, $pdf->render());
				$io->close();
			} 
			catch (Exception $e) {
				//Should add message into session
				Mage::logException($e);
			}
            
        }
    }


    protected function _processReadyPdf($pdf, $fileName, $storeId = 0)
    {

        switch ($this->_getConfig("auto_processing_2nd", $storeId)) {
            case 1:
                //send as email
                $this->_sendMail($pdf, $fileName, $storeId);
                break;
            case 2:
                //save as file
                $this->_saveAsFile($pdf, $fileName, $storeId);
                break;
            case 3:
                //send to ftp
                $this->_sendToFtp($pdf, $fileName, $storeId);
            default:
                //don't do anything
                break;
        }

        return $this;
    }
    
    public function filter_by_status($order,$storeId=0)
    {
        if ($this->_getConfig("auto_processing_condition_type_2nd", $storeId) == 'status') {
            $triggerStatus = $this->_getConfig("auto_processing_condition_status_2nd", $storeId);
            if (strpos($triggerStatus, ',') !== false) {
                $triggerStatus = explode(',', $triggerStatus);
            } else {
                $triggerStatus = array($triggerStatus);
            }

            if (in_array($order->getStatus(), $triggerStatus)) {
                return true;
            }
            return false;
         }
         return true;
    }
    
    public function check_trigger_status($order,$storeId=0)
    {
            $triggerStatus = $this->_getConfig("auto_processing_main_condition_status_2nd", $storeId);
            if (strpos($triggerStatus, ',') !== false) {
                $triggerStatus = explode(',', $triggerStatus);
            } else {
                $triggerStatus = array($triggerStatus);
            }

            if (in_array($order->getStatus(), $triggerStatus)) {
                return true;
            }
            return false;
    }
    
    public function filter_by_shipping_method($order,$storeId=0,$filter_pattern='')
    {
        $shipping_method_temp = $order->getShippingDescription();
		$shipping_method_temp = strtolower($shipping_method_temp);	
		$filter_pattern = strtolower($filter_pattern);
		$filter_pattern = trim($filter_pattern);
		if(strlen($filter_pattern) == 0)
			return true;			
		$filter_pattern_arr = explode(',',$filter_pattern);
		foreach($filter_pattern_arr as $filter_element)
		{
			if(strpos($shipping_method_temp,$filter_element)!==false)
				return true;
		}
		return false;
    }
    

    public function filter_by_order_attribute($order,$code,$value)
    {
        try
        {
            $code = strtolower($code);
            $value = strtolower($value);
            $order_att_data = $order->getData($code);
            if(strpos($order_att_data,$value) !== false)
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        catch(Exception $e)
        {
            return fasle;
        }
        return true;
    }

    public function filter_by_stock($order,$storeId=0)
    {
        $checkStock = $this->_getConfigFlag("auto_processing_required_stock_2nd", $storeId);
        if (
            ($checkStock) ||
            ($checkStock && Mage::helper('pickpack')->checkStock($order))
        ) {
            return true;
        }
        return false;
    }
    
   public function filter_by_custom_attribute($order,$storeId=0,&$canProcessOrder)
    {                   
        if ($this->_getConfigFlag("auto_processing_check_2nd", $storeId)) {
            $canProcessOrder -= 1;                            
            $checkField = $this->_getConfig("auto_processing_check_attribute_2nd", $storeId);                   
            
            if($checkField == 'szy_custom_attribute')
                $postfix =  'szy_check_custom_value_1_2nd';
            else
                if($checkField == 'szy_custom_attribute2')
                    $postfix =  'szy_check_custom_value_2_2nd';
                else
                    $postfix =  'szy_check_custom_value_3_2nd';
                    

            $checkValue = $this->_getConfig($postfix,$storeId);

            if ($checkValue == '_custom_') {
                
                if($checkField == 'szy_custom_attribute')
                    $postfix =  'szy_check_own_value1_2nd';
                else
                    if($checkField == 'szy_custom_attribute2')
                        $postfix =  'szy_check_own_value2_2nd';
                    else
                        $postfix =  'szy_check_own_value3_2nd';
                $checkValue = $this->_getConfig($postfix,$storeId);

            }
            
            $orderValue = null;
            if ($order->hasData($checkField)) {
                $orderValue = $order->getData($checkField);
            } else {
                $orderValue = $this->_getOrderTargetValue($checkField, $order->getId());
            }

            if ($this->_ifValueMatched($orderValue, $checkValue)) {
                $canProcessOrder += 1;
                return true;
            }
            
            return false;
        }
        return true;
    }
    
    public function filter_by_run_one($order,$storeId=0,&$canProcessOrder)
    {
        if ($this->_getConfigFlag("auto_processing_print_flag_2nd", $storeId)) {
            $flagautoaction = Mage::getModel('pickpack/flagautoaction')->load($order->getId(),'orderid');;
            $flag_value = $flagautoaction->getData($this->flagColumn);
            if($flag_value)
            {
                 $canProcessOrder -= 1;
                 return false;
            }
            return true;
        }
        return true;
    }

    public function processOrderStatusUpdate($order)
    {
        $storeId = $order->getStoreId();
        $flag_continue = false;
     
        if (!$storeId) {
            $storeId = 0;
        }
	
		if ($this->_getConfig("enable_auto_processing", $storeId))
			if($this->check_trigger_status($order,$storeId))
				if ($this->_getConfig("auto_processing_2nd", $storeId)) {
					if ($this->_getConfig("auto_processing_condition_type_2nd", $storeId) == 'status') {

						//Get order filter
						// If status 
						//Filter 1: Check status
						$order_filter_type = $this->_getConfig("auto_processing_condition_order_attribute_2nd", $storeId);
						//TODO continue here
						if($order_filter_type == 'status')
							$flag_continue = $this->filter_by_status($order,$storeId);
						else
							if($order_filter_type == 'shipping_method')
							{
								$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value_2nd", $storeId);
								$flag_continue = $this->filter_by_shipping_method($order,$storeId,$order_filter_pattern);
							}
						else
							if($order_filter_type == 'attribute')
							{
								$order_filter_code = $this->_getConfig("auto_processing_condition_order_attribute_code_2nd", $storeId);

								$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value_2nd", $storeId);
								$flag_continue = $this->filter_by_order_attribute($order,$order_filter_code,$order_filter_pattern);
							}
							else
								if($order_filter_type == 'no')
									$flag_continue = true;

						if($flag_continue == false)
							return $this;
						//Else if order attribute
						//Filter 2 : CHECK STOCK
						$flag_continue = $this->filter_by_stock($order,$storeId);

							if($flag_continue == false)
								return $this;
						//Filter 3: CHECK CUSTOM ATTRIBUTE VALUE    
						$canProcessOrder = 1;
						$flag_continue = $this->filter_by_custom_attribute($order,$storeId,$canProcessOrder);  

						if($flag_continue == false)
								return $this;        
						//Filter 4: CHECK RUN ONE. 
						$flag_continue = $this->filter_by_run_one($order,$storeId,$canProcessOrder);   

						if($flag_continue == false)
								return $this;

						$flagautoaction = Mage::getModel('pickpack/flagautoaction')->load($order->getId(),'orderid');
						$flag_value = $flagautoaction->getData($this->flagColumn);     
				
						$pdf = $this->getPdf($order->getId(), $storeId);

						if(empty($pdf))
							return $this;
						else
							$this->_processReadyPdf($pdf, $this->_getFileName($order->getIncrementId()), $storeId);
				
				
						//Update 1 Order Attribute
							$this->_updateAttribute($this->_configGroupPrefix,$order->getId(),$this->_fieldPrefix);    
				
						//Update 2 Flagautoaction table
				
						if(!$flag_value) 
						{
							try{
								$flagautoaction = Mage::getModel('pickpack/flagautoaction');
								$flagautoaction->setOrderid($order->getId());

								switch($this->flagColumn)
								{
									case 'pack_printed':
										$flagautoaction->setPack_printed(1);
										break;
									case 'pp_invoice_printed':
										$flagautoaction->setPp_pp_invoice_printed(1);
										break;
									case 'separate_printed':
										$flagautoaction->setSeparate_printed(1);
										break;
									case 'combined_printed':
										$flagautoaction->setCombined_printed(1);                        
										break;
								}
					
									$flagautoaction->save();
							}
							catch(Exception $e)
							{
								Mage::logException($e);
							}
						}
						else
						{
							try{
								$flagautoaction = Mage::getModel('pickpack/flagautoaction')->load($order->getId(),'orderid');
								$flag_value = $flagautoaction->getData($this->flagColumn);
								switch($this->flagColumn)
								{
									case 'pack_printed':
										$flagautoaction->setPack_printed($flag_value + 1);
										break;
									case 'pp_invoice_printed':
										$flagautoaction->setPp_pp_invoice_printed($flag_value + 1);
										break;
									case 'separate_printed':
										$flagautoaction->setSeparate_printed($flag_value + 1);
										break;
									case 'combined_printed':
										$flagautoaction->setCombined_printed($flag_value + 1);                      
										break;
								}
					
									$flagautoaction->save();
							}
							catch(Exception $e)
							{
								Mage::logException($e);
							}
						}
					} 
				}

        return $this;
    }
   
    protected function _getProcessingConfigHash($storeId)
    {
        $hashArray['type'] = $this->_getConfig("auto_processing", $storeId);
        switch ($hashArray['type']) {
            case 1:
                $hashArray['send_to'] = $this->_getConfig("auto_processing_send_to_2nd", $storeId);
                $hashArray['subject'] = $this->_getConfig("auto_processing_subject_2nd", $storeId);
                $hashArray['body'] = $this->_getConfig("auto_processing_body_2nd", $storeId);
                $hashArray['sender'] = $this->_getConfig("auto_processing_sender_2nd", $storeId);
                break;

            case 2:
                $hashArray['path'] = $this->_getConfig("auto_processing_export_path_2nd", $storeId);
                break;

            case 3:
                $hashArray['host'] = $this->_getConfig("auto_processing_ftp_host_2nd", $storeId);
                $hashArray['user'] = $this->_getConfig("auto_processing_ftp_user_2nd", $storeId);
                $hashArray['password'] = $this->_getConfig("auto_processing_ftp_password_2nd", $storeId);
                $hashArray['port'] = $this->_getConfig("auto_processing_ftp_port_2nd", $storeId);
                break;

            default:
                break;
        }

        return md5(implode('||', $hashArray));
    }

    protected function _updateAttribute($configPrefix, $orderIds, $fieldPrefix = '')
    {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        if (!empty($fieldPrefix)) {
            $fieldPrefix .= '_';
        }

        $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
        foreach ($orderIds as $orderId) {

            $storeId = Mage::getResourceSingleton('pickpack/sales_order')->getOrderStoreId($orderId);
            if (!$storeId) {
                $storeId = 0;
            }
            
            if (Mage::getStoreConfigFlag("pickpack_options/{$configPrefix}/{$fieldPrefix}additional_action_2nd", $storeId)) {

                $attributeToUpdate = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}szy_attribute_to_update_2nd", $storeId);
                if($attributeToUpdate == 'custom_attribute')
                    $postfix =  'szy_custom_value1_2nd';
                else
                    if($attributeToUpdate == 'custom_attribute2')
                        $postfix =  'szy_custom_value2_2nd';
                    else
                        $postfix =  'szy_custom_value3_2nd';
                    
               

                $value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);

                if($value =='{{date}}')
                {               
                    $value = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
                }
                else
                    if ($value == '_custom_') {
                        if($attributeToUpdate == 'custom_attribute')
                            $postfix =  'szy_own_value1_2nd';
                        else
                            if($attributeToUpdate == 'custom_attribute2')
                                $postfix =  'szy_own_value2_2nd';
                            else
                                $postfix =  'szy_own_value3_2nd';
                        $value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);
                    }

                try {
                    $pickpack_version = (int)Mage::getConfig()->getNode()->modules->Moogento_Pickpack->version;
                    $shipeasy_version = (int)Mage::getConfig()->getNode()->modules->Moogento_ShipEasy->version;
                    if($shipeasy_version < 3){
                        $resource->updateGridRow($orderId, $attributeToUpdate, $value);
                    }
                    else
                    {
                        $resource->updateGridRow($orderId, 'szy_'.$attributeToUpdate, $value);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

    protected function _getOrderTargetValue($field, $orderId)
    {
        if (in_array($field, array('custom_attribute', 'custom_attribute2', 'custom_attribute3','szy_custom_attribute', 'szy_custom_attribute2', 'szy_custom_attribute3'))) {
            return Mage::getResourceSingleton('pickpack/sales_order')->getOrderGridColumnValue($field, $orderId);
        }
        return Mage::getResourceSingleton('pickpack/sales_order')->getOrderColumnValue($field, $orderId);
    }

    protected function _ifValueMatched($orderValue, $neededValue)
    {
        $result = false;

        if ($neededValue === '0') {
            if ((float)$orderValue == (float)$neededValue) {
                $result = true;
            }
        } else if (empty($neededValue)) {
            if (!$orderValue || empty($orderValue)) {
                $result = true;
            }
        } else {
            if ($orderValue == $neededValue) {
                $result = true;
            }
        }

        return $result;
    }
    
     protected function _processBulkPdfs($storePdfs)
    {
        $groups = array();

        foreach ($storePdfs as $storeId => $storePdfInfo) {

            $storePdf = $storePdfInfo['pdf'];
            $orderIds = $storePdfInfo['order_ids'];

            if ($this->_getConfigFlag("auto_processing_groupping_2nd", $storeId)) {
                $storeConfigHash = $this->_getProcessingConfigHash($storeId);
                if (!isset($groups[$storeConfigHash])) {
                    $groups[$storeConfigHash] = array(
                        'store_id' => $storeId,
                        'pdf' => $storePdf,
                        'order_ids' => $orderIds
                    );
                } else {
                    $groupedPdf = $groups[$storeConfigHash]['pdf'];
                    foreach ($storePdf->pages as $page) {
                        $groupedPdf->pages[] = $page;
                    }

                    $groups[$storeConfigHash]['order_ids'] = array_unique(array_merge($groups[$storeConfigHash]['order_ids'], $orderIds));
                }
            } else {
                $storeConfigHash = md5('store_' . $storeId);
                $groups[$storeConfigHash] = array(
                    'store_id' => $storeId,
                    'pdf' => $storePdf,
                    'order_ids' => $orderIds
                );
            }
        }

        foreach ($groups as $groupedData) {
            $storeId = $groupedData['store_id'];
            $pdf = $groupedData['pdf'];
            $orderIds = $groupedData['order_ids'];
            $this->_processReadyPdf($pdf, $this->_getFileName($orderIds), $storeId);
        }
    }
    
    public function processBulk2($digitDay)
    { 
        $stores = Mage::app()->getStores(true);
        $ordersByStores = array();
        $avalable_orders_processing = 3;
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if ($this->_getConfig("auto_processing_2nd", $storeId)) {
                if ($this->_getConfig("auto_processing_condition_type_2nd", $storeId) == 'day') {
                    $daysToRun = $this->_getConfig("auto_processing_condition_specific_day_2nd", $storeId);
                    if (strpos($daysToRun, $digitDay) !== false) {
                        $triggerStatus = $this->_getConfig("auto_processing_condition_status_2nd", $storeId);
                        if (strpos($triggerStatus, ',') !== false) {
                            $triggerStatus = explode(',', $triggerStatus);
                        } else {
                            $triggerStatus = array($triggerStatus);
                        }
                        $orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('status', array('in' => $triggerStatus));
                        $orders->addFieldToFilter('store_id', $storeId);
                        if (count($orders)) {
                            $checkStock = $this->_getConfigFlag("auto_processing_required_stock_2nd", $storeId);
                            $orderIds = array();
                            foreach ($orders as $order) {
                                //Neu khong add order id vao danh sach order Id , neu co , dung helper de kiem tra. Neu con stock thi add vao.
                                if (!$checkStock) {
                                    $orderIds[] = $order->getId();
                                } else if (Mage::helper('pickpack')->checkStock($order)) {
                                    $orderIds[] = $order->getId();
                                }
                            }
                            if (count($orderIds)) {
                                $tmpOrderIds = array();
                                if ($this->_getConfigFlag("auto_processing_check_2nd", $storeId)) {
                                    foreach ($orderIds as $_orderId) {
                                        //  Filter 2
                                        $canProcessOrder = 0;     
                                        
                                        // //Filter 1 Table status
                                        $flag_value = $this->getFlagValue($_orderId,$this->flagColumn);
                                        if ($this->_getConfigFlag("auto_processing_print_flag_2nd", $storeId)) {
                                            if($flag_value )
                                                 $canProcessOrder -= 1;
                                        }
                                        
                                        $checkField = $this->_getConfig("auto_processing_check_attribute_2nd", $storeId);
                                        
                                        if($checkField == 'szy_custom_attribute')
                                            $postfix =  'szy_check_custom_value_1';
                                        else
                                            if($checkField == 'szy_custom_attribute2')
                                                $postfix =  'szy_check_custom_value_2';
                                            else
                                                $postfix =  'szy_check_custom_value_3';
                        
                                        $checkValue = $this->_getConfig($postfix,$storeId);
                                        if ($checkValue == '_custom_') {
                                            
                                            if($attributeToUpdate == 'szy_custom_attribute')
                                                $postfix =  'szy_check_own_value1';
                                            else
                                                if($attributeToUpdate == 'szy_custom_attribute2')
                                                    $postfix =  'szy_check_own_value2';
                                                else
                                                    $postfix =  'szy_check_own_value3';
                                            $checkValue = $this->_getConfig($postfix,$storeId);
                                        }
                        
                                        $orderValue = null;
                                        if ($order->hasData($checkField)) {
                                            $orderValue = $order->getData($checkField);
                                        } else {
                                            $orderValue = $this->_getOrderTargetValue($checkField, $order->getId());
                                        }
                                        if ($this->_ifValueMatched($orderValue, $checkValue)) {
                                            $canProcessOrder += 1;
                                        }
                                        
                                        
                                        if ($canProcessOrder > 0) {
                                            $tmpOrderIds[] = $_orderId;
                                        }
                                    }
                                    $orderIds = $tmpOrderIds;
                                    if (!count($orderIds)) {
                                        continue;
                                    }                               
                                }
                            
                                $new_orderIds = count($orderIds);
                                $output = array();
                                if($new_orderIds > $avalable_orders_processing)
                                {
                                    $output = array_slice($orderIds, 0, $avalable_orders_processing);

                                }
                                else
                                {
                                    $output = $orderIds; 

                                }
                                
                                if(count($output) > 0 )
                                    $ordersByStores[$storeId] = $output;
                                    
                                $avalable_orders_processing -= count($output);

                                if($avalable_orders_processing <= 0)
                                {
                                    continue; 
                                }
                                unset($output);

                            }
                        }
                    }
                }
            }
        }
        //Get not printed orderId in trigger status array from all stores. Change flag for each column
        foreach ($ordersByStores as $storeId => $storeOrderIds) {
            $pdf = $this->getPdf($storeOrderIds, $storeId);
            foreach($storeOrderIds as $orderId)
            {
                $flag_value = $this->getFlagValue($orderId,$this->flagColumn);          
                if($flag_value === false)
                {
                    $insert_flat_value = $this->insertFlagValue($orderId,$this->flagColumn,1);
                }
                else
                {
                    $update_flat_value = $this->updateFlagValue($orderId,$this->flagColumn,((int)$flag_value + 1));                             
                }
            }            
            $ordersByStores[$storeId] = array(
                'order_ids' => $storeOrderIds,
                'pdf' => $pdf
            );
        }
        $this->_processBulkPdfs($ordersByStores);
        return $this;
    }
    
    public function processBulk($digitDay)
    { 
        $stores = Mage::app()->getStores(true);
        $ordersByStores = array();
        //Change max numbers of order print in the same time.
        $avalable_orders_processing = 20;
        foreach ($stores as $store) {
        	$tmpOrderIds = array();
            $storeId = $store->getId();
        if ($this->_getConfig("enable_auto_processing", $storeId))
            if ($this->_getConfig("auto_processing_2nd", $storeId)) {
                if ($this->_getConfig("auto_processing_condition_type_2nd", $storeId) == 'day') {
                    $daysToRun = $this->_getConfig("auto_processing_condition_specific_day_2nd", $storeId);                    
                    if (strpos($daysToRun, $digitDay) !== false) {
                    	//Filter 1: Order status
                        
                        $orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('store_id', $storeId);
                        if (count($orders)) {
                            $orderIds = array();
                            foreach ($orders as $order) {
                            	//Main filter:
								$flag_continue = $this->check_trigger_status($order,$storeId);
								if($flag_continue == false)
									continue;
                            	 $canProcessOrder = 1;										
                            	 $order_filter_type = $this->_getConfig("auto_processing_condition_order_attribute_2nd", $storeId);
                            	 //Filter 1: CHECK Status | Shipping method | Order attribute
                            	if($order_filter_type == 'status')
                            	{
									$flag_continue = $this->filter_by_status($order,$storeId);
								}
								else
									if($order_filter_type == 'shipping_method')
									{
										$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value_2nd", $storeId);
										$flag_continue = $this->filter_by_shipping_method($order,$storeId,$order_filter_pattern);
									}
									else
										if($order_filter_type == 'attribute')
										{
											$order_filter_code = $this->_getConfig("auto_processing_condition_order_attribute_code_2nd", $storeId);

											$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value_2nd", $storeId);
											$flag_continue = $this->filter_by_order_attribute($order,$order_filter_code,$order_filter_pattern);
										}
								
								if($flag_continue == false)
                                	continue;
                                	
                                //Filter 2 : CHECK STOCK
								$flag_continue = $this->filter_by_stock($order,$storeId);
								if($flag_continue == false)
                                	continue;
                                
                                //Filter 3 : CHECK CUSTOM ATTRIBUTE
                                $flag_continue = $this->filter_by_custom_attribute($order,$storeId,$canProcessOrder);  
								if($flag_continue == false)
                                	continue;
                                
                            	//Filter 4: CHECK RUN ONE. 
								$flag_continue = $this->filter_by_run_one($order,$storeId,$canProcessOrder); 
								if($flag_continue == false)
									continue;						
									
                                $tmpOrderIds[] = $order->getId();
                                
                            }
                            
                            $orderIds = $tmpOrderIds;
                            unset($tmpOrderIds);
							if (!count($orderIds)) {
								continue;
							} 
							
							$new_orderIds = count($orderIds);
							$output = array();
							if($new_orderIds > $avalable_orders_processing)
							{
								$output = array_slice($orderIds, 0, $avalable_orders_processing);

							}
							else
							{
								$output = $orderIds; 

							}
							
							if(count($output) > 0 )
								$ordersByStores[$storeId] = $output;
								
							$avalable_orders_processing -= count($output);

							if($avalable_orders_processing <= 0)
							{
								continue; 
							}
							unset($output);
							
                        }
                    }
                }
            }
        }
        
        //Get not printed orderId in trigger status array from all stores. Change flag for each column
        $flagautoaction_model = Mage::getModel('pickpack/flagautoaction');
        foreach ($ordersByStores as $storeId => $storeOrderIds) { 
        	$pdf = $this->getPdf($storeOrderIds, $storeId); 
            foreach($storeOrderIds as $orderId)
            {
            	$str_order_id =''.$orderId;
            	$flagautoaction_model->load($str_order_id,'orderid');
                $flag_value = $flagautoaction_model->getData($this->flagColumn);  
                if(!$flag_value) 
                {
                    try{
                        $flagautoaction = Mage::getModel('pickpack/flagautoaction');
                        $flagautoaction->setOrderid($orderId);

                        switch($this->flagColumn)
                        {
                            case 'pack_printed':
                                $flagautoaction->setPack_printed(1);
                                break;
                            case 'invoice_printed':
                                $flagautoaction->setInvoice_printed(1);
                                break;
                            case 'separate_printed':
                                $flagautoaction->setSeparate_printed(1);
                                break;
                            case 'combined_printed':
                                $flagautoaction->setCombined_printed(1);                        
                                break;
                        }
                            $flagautoaction->save();
                            unset($flagautoaction);
                    }
                    catch(Exception $e)
                    {
                        Mage::logException($e);
                    }
                }
                else
                {
                    try{
                        switch($this->flagColumn)
                        {
                            case 'pack_printed':
                                $flagautoaction_model->setPack_printed($flag_value + 1);
                                break;
                            case 'invoice_printed':
                                $flagautoaction_model->setInvoice_printed($flag_value + 1);
                                break;
                            case 'separate_printed':
                                $flagautoaction_model->setSeparate_printed($flag_value + 1);
                                break;
                            case 'combined_printed':
                                $flagautoaction_model->setCombined_printed($flag_value + 1);                      
                                break;
                        }
                    
                            $flagautoaction_model->save();
                    }
                    catch(Exception $e)
                    {
                        Mage::logException($e);
                    }
                }
            }
                       
            $ordersByStores[$storeId] = array(
                'order_ids' => $storeOrderIds,
                'pdf' => $pdf
            );
        }
        $this->_processBulkPdfs($ordersByStores);
        return $this;
    }

}
