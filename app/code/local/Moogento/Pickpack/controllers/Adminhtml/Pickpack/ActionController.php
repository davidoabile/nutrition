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
* File        OrderController.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

$magento_base_dir = '';
$magento_base_dir = Mage::getBaseDir('app');
require_once($magento_base_dir . "/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php");
class Moogento_Pickpack_Adminhtml_Pickpack_ActionController extends Mage_Adminhtml_Sales_OrderController
{
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Return some checking result
     *
     * @return void
     */

    protected $default_config_values = array();

    public function resetAction()
    {
        $result = 0;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
		$tableName1 = $resource->getTableName('core_config_data');
		$tableName2 = $resource->getTableName('core_resource');

		$delete_core_resource = 'DELETE FROM '.$tableName2.' WHERE code like "pickpack_setup"';
		$delete_core_config_data = 'DELETE FROM '.$tableName1.' WHERE path like "%pickpack_option%"';

		$writeConnection->query($delete_core_resource);
		$writeConnection->query($delete_core_config_data);

		$query1 = 'SELECT * FROM '.$tableName1.' WHERE path like "%pickpack_option%" LIMIT 1';
		$data1  = $readConnection->fetchAll($query1);
		if(empty($data1))
		{
			echo 'pickPack Reset OK';
		}
		else
			echo 'Can\'t Reset pickPack.';
		exit;
        Mage::app()->getResponse()->setBody($data1);
    }

    public function clearcacheAction()
    {
        $PNG_TEMP_DIR = Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack';
        try
        {
            if (file_exists($PNG_TEMP_DIR))
            {
                if (!function_exists('clearUTF')) {
                    function delTree($dir)
                    { 
                        $files = array_diff(scandir($dir), array('.', '..')); 

                        foreach ($files as $file) { 
                            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
                        }

                        return rmdir($dir); 
                    } 
                }
                delTree($PNG_TEMP_DIR);
            }
            
            if (!file_exists($PNG_TEMP_DIR))
                mkdir($PNG_TEMP_DIR,0777,true);
            echo 'All cached images in pickPack are cleared.';
        }
        catch(Exception $e)
        {
            echo  'Can\'t clear cached images in pickPack now.';
        }
        exit;
    }

    public function exportAction(){
    	$resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
		$tableName1 = $resource->getTableName('core_config_data');
		$tableName2 = $resource->getTableName('core_resource');
		$query = 'SELECT * FROM ' . $tableName1 . ' WHERE path LIKE "pickpack%"';
		$data1 = $readConnection->fetchAll($query);
		$pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_csvexport')->getCsvexport($data1);
		$pdf =  utf8_decode($pdf);
		$fileName = 'pickpack-export-csv_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.csv';
		return $this->_prepareDownloadResponse($fileName, $pdf,'text/csv');
    }
    public function importAction(){
    	$data = $_POST;
        $data_array = array();
    	foreach ($data as $key => $value) {
            $value = trim($value, '"');
    		$data_array = explode("\n", $value);
            if($data_array != "")
                break;
    	}
        $header = array();
        $data_array_after = array();
        $header = explode(",", trim($data_array[0], '"'));
        for ($i=1; $i<count($data_array); $i++) {
            $array_temp = array();
            $data_array_after[$i] = array();
            $array_temp = explode(",", trim($data_array[$i], '"'));
            for ($k = 0; $k<(count($header) - 1); $k ++) {
                $data_array_after[$i][trim($header[$k], '"')] = isset($array_temp[$k]) ? trim($array_temp[$k], '"') : "";
            }
        }
        $model_config = new Mage_Core_Model_Config();
        foreach ($data_array_after as $key => $value) {
            $model_config->saveConfig($value["path"], $value["value"], $value["scope"], $value["scope_id"]);
        }
        $this->_redirect('*/*/');
    }

    public function presetAction()
    {
        $result = 0;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $tableName1 = $resource->getTableName('core_config_data');
        $tableName2 = $resource->getTableName('core_resource');
        $store_id = Mage::app()->getStore()->getId();
        $show_background = trim(Mage::getStoreConfig('cn22_options/custom_section/show_background_1', $store_id));
        $country_name = '';
        switch ($show_background)
        {
            case 1:
                $country_name ='';
            break;
            case 2: 
                 $country_name = 'us';
            break;
            case 3:
                $country_name = 'uk';
            break;
            case 4:
                $country_name = 'thailand';
            break;
            case 5:
                $country_name = 'custom';
            break;
            case 6:
                $country_name = 'holland';
            break;
            case 7:
                $country_name = 'germany';
            break;
        }
        $this->setDefaultValues($country_name);

        //Step 1: clear pre data
        $delete_core_config_data = 'DELETE FROM '.$tableName1.' WHERE path like "%cn22_options%"';
        $writeConnection->query($delete_core_config_data);
        $query1 = 'SELECT * FROM '.$tableName1.' WHERE path like "%cn22_options%" LIMIT 1';
        $data1  = $readConnection->fetchAll($query1);
        if(empty($data1))
        {
            echo 'Step 1: Reset autoCN22 OK.';
        }
        else
        {
            echo 'Step 1: Error resetting autoCN22.';
            exit;
        }

         //Step 2: add pre data
        foreach($this->default_config_values as $key => $value)
        {
            $path = 'cn22_options/custom_section/'.$key;
            $sql = 'INSERT INTO `'.$tableName1.'`(`scope`, `scope_id`, `path`, `value`) VALUES ("default",0,"'.$path.'","'.$value.'")';
            $writeConnection->query($sql);
        }
        echo 'Preset successfully';
        exit;
        Mage::app()->getResponse()->setBody($data1);
    }

    public function presetajaxAction()
    {
        if(isset($_GET['selected']))
        {
            $selected_value = $_GET['selected'];
        }
        
        $result = 0;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $tableName1 = $resource->getTableName('core_config_data');
        $tableName2 = $resource->getTableName('core_resource');
        $store_id = Mage::app()->getStore()->getId();

        if(isset($_GET['selected']))
        {
            $show_background = $_GET['selected'];
        }
        else
            $show_background = trim(Mage::getStoreConfig('cn22_options/custom_section/show_background_1', $store_id));

        $country_name = '';
        switch ($show_background)
        {
            case 1:
                $country_name ='';
            break;
            case 2: 
                 $country_name = 'us';
            break;
            case 3:
                $country_name = 'uk';
            break;
            case 4:
                $country_name = 'thailand';
            break;
            case 5:
                $country_name = 'custom';
            break;
            case 6:
                $country_name = 'holland';
            break;
            case 7:
                $country_name = 'germany';
            break;
        }
        if($country_name == '')
        {
            echo "We have no default values for 'Text'";
            exit;
        }
        $this->setDefaultValues($country_name);
        //Step 1: clear pre data
        $delete_core_config_data = 'DELETE FROM '.$tableName1.' WHERE path like "%cn22_options/custom_section%"';
        $delete_core_config_data_label_width = 'DELETE FROM '.$tableName1.' WHERE path like "%cn22_options/cn22_label/label_width%"';
        $delete_core_config_data_label_height = 'DELETE FROM '.$tableName1.' WHERE path like "%cn22_options/cn22_label/label_height%"';

        $writeConnection->query($delete_core_config_data);
        $writeConnection->query($delete_core_config_data_label_width);
        $writeConnection->query($delete_core_config_data_label_height);

        $query1 = 'SELECT * FROM '.$tableName1.' WHERE path like "%cn22_options/custom_section%" LIMIT 1';
        $data1  = $readConnection->fetchAll($query1);
        if(empty($data1))
        {
            echo 'autoCN22 Reset';
        }
        else
        {
            echo 'autoCN22 Reset Failed';
            exit;
        }

         //Step 2: add pre data
        foreach($this->default_config_values as $key => $value)
        {
            if($key =='label_width' || $key == 'label_height')
                $path = 'cn22_options/cn22_label/'.$key;
            else    
                $path = 'cn22_options/custom_section/'.$key;
            $sql = 'INSERT INTO `'.$tableName1.'`(`scope`, `scope_id`, `path`, `value`) VALUES ("default",0,"'.$path.'","'.$value.'")';
            $writeConnection->query($sql);
        }
        echo 'Preset successfully';
        exit;
        Mage::app()->getResponse()->setBody($data1);
    }

    public function setDefaultValues($country_name = '')
    {
        $this->default_config_values['filter_shipping_zone_yn'] = 0;
        $this->default_config_values['filter_shipping_zone'] = '';       
        $this->default_config_values['show_background'] = 1;     
        $this->default_config_values['show_background_1'] = 1;     
        $this->default_config_values['show_background_color'] = '#FFFFFF';  

        //Order Id
        $this->default_config_values['show_order_id'] = 1;
        $this->default_config_values['show_order_id_nudge'] = '7,87';
        
        //Description
        $this->default_config_values['show_description'] = 0;
        $this->default_config_values['show_description_text'] = '';
        $this->default_config_values['show_description_code'] = 'sku';
        $this->default_config_values['show_description_number_of_lines'] = 1;
        $this->default_config_values['show_description_box_demension'] = '150,16';
        $this->default_config_values['show_description_nudge'] = '15,160';
        $this->default_config_values['show_description_width'] = '150';
        
        //Qty
        $this->default_config_values['show_qty_column'] = 0;
        $this->default_config_values['show_qty_xpos'] = '8';
        
        //Weight
        $this->default_config_values['show_weight'] = 1;
        $this->default_config_values['show_weight_nudge_total'] = '50,44';
        $this->default_config_values['weight_multiply'] = 1;
        $this->default_config_values['show_weight_column'] = 0;
        $this->default_config_values['show_weight_xnudge'] = 100;
        
        //Value
        $this->default_config_values['show_value'] = 1;
        $this->default_config_values['show_value_nudge_total'] = '50,30';
        $this->default_config_values['value_multiply'] = 1;
        $this->default_config_values['show_value_column'] = 0;
        $this->default_config_values['show_value_xnudge'] = 120;
        
        //Hs tariff
        $this->default_config_values['show_hs_tariff_number'] = 0;
        $this->default_config_values['show_hs_tariff_number_fixed_value'] = '';
        $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
        $this->default_config_values['show_hs_tariff_xnudge'] = 210;
        
        //Country of origin
        $this->default_config_values['show_country_of_origin_number'] = 0;
        $this->default_config_values['show_country_of_origin_number_fixed_value'] = '';
        $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
        $this->default_config_values['show_country_of_origin_xnudge'] = 210;
        
        //Signature
        $this->default_config_values['show_signature_image'] = 1;
        $this->default_config_values['signature_image'] = '' ;
        $this->default_config_values['show_signature_image_nudge'] = '145,15';
        
        //Movable text
        $this->default_config_values['show_fixed_text_yn'] = 0;
        $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
        $this->default_config_values['show_fixed_text_width'] = 150;
        $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
        $this->default_config_values['show_fixed_text_font_size'] = 8;
        $this->default_config_values['show_fixed_text_nudge'] = '10,110';        
        
        //Print date
        $this->default_config_values['show_print_date'] = 1;
        $this->default_config_values['show_print_date_text'] = '';
        $this->default_config_values['show_print_date_width'] = 100;
        $this->default_config_values['date_format'] = 'M. j, Y';
        $this->default_config_values['show_print_date_nudge'] = '218,25';
        
        if($country_name == 'holland')
        {
            $this->default_config_values['show_background'] = 6;     
            $this->default_config_values['show_background_1'] = 6;
            $this->default_config_values['show_background_color'] = '#F5FAB4';       
  
            //Order Id
            $this->default_config_values['show_order_id'] = 1;
            $this->default_config_values['show_order_id_nudge'] = '5,395';
            
            //Description
            $this->default_config_values['show_description'] = 3;
            $this->default_config_values['show_description_text'] = '';
            $this->default_config_values['show_description_code'] = '';
            $this->default_config_values['show_description_number_of_lines'] = 5;
            $this->default_config_values['show_description_box_demension'] = '150,16';
            $this->default_config_values['show_description_nudge'] = '30,233';
            
            //Qty
            $this->default_config_values['show_qty_column'] = 1;
            $this->default_config_values['show_qty_xpos'] = '8';
            
            //Weight
            $this->default_config_values['show_weight_6'] = 1;
            $this->default_config_values['show_weight_6_nudge_total'] = '160,125';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_weight_6_column'] = 1;
            $this->default_config_values['show_weight_6_xnudge'] = 160;
            
            //Value
            $this->default_config_values['show_value_6'] = 2;
            $this->default_config_values['show_value_6_nudge_total'] = '200,125';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_value_6_column'] = 1;
            $this->default_config_values['show_value_6_xnudge'] = 200;
            
            //Hs tariff
            $this->default_config_values['show_hs_tariff_number'] = 0;
            $this->default_config_values['show_hs_tariff_number_fixed_value'] = '';
            $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
            $this->default_config_values['show_hs_tariff_xnudge'] = 210;
            
            //Country of origin
            $this->default_config_values['show_country_of_origin_number'] = 0;
            $this->default_config_values['show_country_of_origin_number_fixed_value'] = '';
            $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
            $this->default_config_values['show_country_of_origin_xnudge'] = 210;
            
            //Signature
            $this->default_config_values['show_signature_image'] = 0;
            $this->default_config_values['signature_image'] = '';
            $this->default_config_values['show_signature_image_nudge'] = '0,0';
         
            //Movable text
            $this->default_config_values['show_fixed_text_yn'] = 0;
            $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
            $this->default_config_values['show_fixed_text_width'] = 150;
            $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
            $this->default_config_values['show_fixed_text_font_size'] = 8;
            $this->default_config_values['show_fixed_text_nudge'] = '10,110';        
        
            //Print date
            $this->default_config_values['show_print_date'] = 3;
            $this->default_config_values['show_print_date_text'] = '';
            $this->default_config_values['show_print_date_width'] = 100;
            $this->default_config_values['date_format'] = 'M. j, Y';
            $this->default_config_values['show_print_date_nudge'] = '8,41.5';

            $this->default_config_values['show_currency_symbol'] = 1;
            $this->default_config_values['currency_code'] = 'EUR';

            $this->default_config_values['label_width'] = 240;
            $this->default_config_values['label_height'] = 390;
        }   
        
        if($country_name == 'germany')
        {
            $this->default_config_values['show_background_color'] = '#F5FAB4';       
            $this->default_config_values['show_background'] = 7;     
            $this->default_config_values['show_background_1'] = 7;
  
            //Order Id
            $this->default_config_values['show_order_id'] = 1;
            $this->default_config_values['show_order_id_nudge'] = '5,395';
            
            //Description
            $this->default_config_values['show_description'] = 3;
            $this->default_config_values['show_description_text'] = '';
            $this->default_config_values['show_description_code'] = '';
            $this->default_config_values['show_description_number_of_lines'] = 4;
            $this->default_config_values['show_description_box_demension'] = '120,16';
            $this->default_config_values['show_description_nudge'] = '35,275';
            
            //Qty
            $this->default_config_values['show_qty_column'] = 1;
            $this->default_config_values['show_qty_xpos'] = '8';
            
            //Weight
            $this->default_config_values['show_weight_7'] = 1;
            $this->default_config_values['show_weight_7_nudge_total'] = '140,150';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_weight_7_column'] = 1;
            $this->default_config_values['show_weight_7_xnudge'] = 140;
            
            //Value
            $this->default_config_values['show_value_7'] = 2;
            $this->default_config_values['show_value_7_nudge_total'] = '190,150';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_value_7_column'] = 1;
            $this->default_config_values['show_value_7_xnudge'] = 190;
            
            //Hs tariff
            $this->default_config_values['show_hs_tariff_number'] = 0;
            $this->default_config_values['show_hs_tariff_number_fixed_value'] = '';
            $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
            $this->default_config_values['show_hs_tariff_xnudge'] = 210;
            
            //Country of origin
            $this->default_config_values['show_country_of_origin_number'] = 0;
            $this->default_config_values['show_country_of_origin_number_fixed_value'] = '';
            $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
            $this->default_config_values['show_country_of_origin_xnudge'] = 210;
            
            //Signature
            $this->default_config_values['show_signature_image'] = 0;
            $this->default_config_values['signature_image'] = '';
            $this->default_config_values['show_signature_image_nudge'] = '0,0';
         
            //Movable text
            $this->default_config_values['show_fixed_text_yn'] = 0;
            $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
            $this->default_config_values['show_fixed_text_width'] = 150;
            $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
            $this->default_config_values['show_fixed_text_font_size'] = 8;
            $this->default_config_values['show_fixed_text_nudge'] = '10,10';     
        
            //Print date
            $this->default_config_values['show_print_date'] = 3;
            $this->default_config_values['show_print_date_text'] = '';
            $this->default_config_values['show_print_date_width'] = 100;
            $this->default_config_values['date_format'] = 'M. j, Y';
            $this->default_config_values['show_print_date_nudge'] = '30,43';

            $this->default_config_values['show_currency_symbol'] = 1;
            $this->default_config_values['currency_code'] = 'EUR';

            $this->default_config_values['label_width'] = 240;
            $this->default_config_values['label_height'] = 390;    
        }

        if($country_name == 'us')
        {
            $this->default_config_values['show_background_color'] = '#F5FAB4';   
            $this->default_config_values['show_background'] = 2;     
            $this->default_config_values['show_background_1'] = 2;    
   
            //Order Id
            $this->default_config_values['show_order_id'] = 1;
            $this->default_config_values['show_order_id_nudge'] = '5,235';
            
            //Description
            $this->default_config_values['show_description'] = 3;
            $this->default_config_values['show_description_text'] = '';
            $this->default_config_values['show_description_code'] = '';
            $this->default_config_values['show_description_number_of_lines'] = 4;
            $this->default_config_values['show_description_box_demension'] = '120,18';
            $this->default_config_values['show_description_nudge'] = '8,182';
            
            //Qty
            $this->default_config_values['show_qty_column'] = 1;
            $this->default_config_values['show_qty_xpos'] = '107';
            $this->default_config_values['show_qty_add_x'] = 0;
            
            //Weight
            $this->default_config_values['show_weight_2'] = 1;
            $this->default_config_values['show_weight_2_nudge_total'] = '120,105';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_weight_2_column'] = 1;
            $this->default_config_values['show_weight_2_xnudge'] = 120;
            
            //Value
            $this->default_config_values['show_value_2'] = 2;
            $this->default_config_values['show_value_2_nudge_total'] = '148,105';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_value_2_column'] = 1;
            $this->default_config_values['show_value_2_xnudge'] = 149;
            
            //Hs tariff
            $this->default_config_values['show_hs_tariff_number'] = 1;
            $this->default_config_values['show_hs_tariff_number_fixed_value'] = '12003';
            $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
            $this->default_config_values['show_hs_tariff_xnudge'] = 177;
            
            //Country of origin
            $this->default_config_values['show_country_of_origin_number'] = 1;
            $this->default_config_values['show_country_of_origin_number_fixed_value'] = 'USA';
            $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
            $this->default_config_values['show_country_of_origin_xnudge'] = 210;
            
            //Signature
            $this->default_config_values['show_signature_image'] = 1;
            $this->default_config_values['signature_image'] = '';
            $this->default_config_values['show_signature_image_nudge'] = '5,15';
         
            //Movable text
            $this->default_config_values['show_fixed_text_yn'] = 0;
            $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
            $this->default_config_values['show_fixed_text_width'] = 150;
            $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
            $this->default_config_values['show_fixed_text_font_size'] = 8;
            $this->default_config_values['show_fixed_text_nudge'] = '10,10';     
        
            //Print date
            $this->default_config_values['show_print_date'] = 1;
            $this->default_config_values['show_print_date_text'] = '';
            $this->default_config_values['show_print_date_width'] = 100;
            $this->default_config_values['date_format'] = 'M. j, Y';
            $this->default_config_values['show_print_date_nudge'] = '90,27';

            $this->default_config_values['show_currency_symbol'] = 0;
            $this->default_config_values['currency_code'] = 'USD';

            $this->default_config_values['label_width'] = 240;
            $this->default_config_values['label_height'] = 230;
        }

        if($country_name == 'uk')
        {
            $this->default_config_values['show_background_color'] = '#FFFFFF';       
            $this->default_config_values['show_background'] = 3;     
            $this->default_config_values['show_background_1'] = 3;
    
	        //Order Id
            $this->default_config_values['show_order_id'] = 1;
            $this->default_config_values['show_order_id_nudge'] = '5,245';
            
            //Description
            $this->default_config_values['show_description'] = 3;
            $this->default_config_values['show_description_text'] = '';
            $this->default_config_values['show_description_code'] = '';
            $this->default_config_values['show_description_number_of_lines'] = 3;
            $this->default_config_values['show_description_box_demension'] = '120,16.5';
            $this->default_config_values['show_description_nudge'] = '20,137';
            
            //Qty
            $this->default_config_values['show_qty_column'] = 1;
            $this->default_config_values['show_qty_xpos'] = '7';
            $this->default_config_values['show_qty_add_x'] = 0;
            
            //Weight
            $this->default_config_values['show_weight_3'] = 1;
            $this->default_config_values['show_weight_3_nudge_total'] = '160,60';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_weight_3_column'] = 1;
            $this->default_config_values['show_weight_3_xnudge'] = 152;
            
            //Value
            $this->default_config_values['show_value_3'] = 2;
            $this->default_config_values['show_value_3_nudge_total'] = '202,60';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_value_3_column'] = 1;
            $this->default_config_values['show_value_3_xnudge'] = 202;
            
            //Hs tariff
            $this->default_config_values['show_hs_tariff_number'] = 0;
            $this->default_config_values['show_hs_tariff_number_fixed_value'] = '';
            $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
            $this->default_config_values['show_hs_tariff_xnudge'] = 177;
    
            //Country of origin
            $this->default_config_values['show_country_of_origin_number'] = 0;
            $this->default_config_values['show_country_of_origin_number_fixed_value'] = 'UK';
            $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
            $this->default_config_values['show_country_of_origin_xnudge'] = 210;
            
            //Signature
            $this->default_config_values['show_signature_image'] = 1;
            $this->default_config_values['signature_image'] = '';
            $this->default_config_values['show_signature_image_nudge'] = '95,3';
         
            //Movable text
            $this->default_config_values['show_fixed_text_yn'] = 0;
            $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
            $this->default_config_values['show_fixed_text_width'] = 150;
            $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
            $this->default_config_values['show_fixed_text_font_size'] = 8;
            $this->default_config_values['show_fixed_text_nudge'] = '100,4.5';     
        
            //Print date
            $this->default_config_values['show_print_date'] = 1;
            $this->default_config_values['show_print_date_text'] = '';
            $this->default_config_values['show_print_date_width'] = 100;
            $this->default_config_values['date_format'] = 'M. j, Y';
            $this->default_config_values['show_print_date_nudge'] = '170,14';

            $this->default_config_values['show_currency_symbol'] = 1;
            $this->default_config_values['currency_code'] = 'EUR';

            $this->default_config_values['label_width'] = 240;
            $this->default_config_values['label_height'] = 240;
        }

        if($country_name == 'thailand')
        {
            $this->default_config_values['show_background_color'] = '#B8D495'; 
            $this->default_config_values['show_background'] = 4;     
            $this->default_config_values['show_background_1'] = 4;      
    
	        //Order Id
            $this->default_config_values['show_order_id'] = 1;
            $this->default_config_values['show_order_id_nudge'] = '5,255';
            
            //Description
            $this->default_config_values['show_description'] = 3;
            $this->default_config_values['show_description_text'] = '';
            $this->default_config_values['show_description_code'] = '';
            $this->default_config_values['show_description_number_of_lines'] = 3;
            $this->default_config_values['show_description_box_demension'] = '120,16.5';
            $this->default_config_values['show_description_nudge'] = '20,143';
            
            //Qty
            $this->default_config_values['show_qty_column'] = 1;
            $this->default_config_values['show_qty_xpos'] = '7';
            $this->default_config_values['show_qty_add_x'] = 0;
            
            //Weight
            $this->default_config_values['show_weight_4'] = 1;
            $this->default_config_values['show_weight_4_nudge_total'] = '130,60';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_weight_4_column'] = 1;
            $this->default_config_values['show_weight_4_xnudge'] = 127;
            
            //Value
            $this->default_config_values['show_value_4 contries in filtercontries'] = 2;
            $this->default_config_values['show_value_4 contries in filtercontries_nudge_total'] = '153,60';
            $this->default_config_values['weight_multiply'] = 1;
            $this->default_config_values['show_value_4 contries in filtercontries_column'] = 1;
            $this->default_config_values['show_value_4 contries in filtercontries_xnudge'] = 155;
            
            //Hs tariff
            $this->default_config_values['show_hs_tariff_number'] = 0;
            $this->default_config_values['show_hs_tariff_number_fixed_value'] = '';
            $this->default_config_values['show_hs_tariff_number_code'] = 'sku';
            $this->default_config_values['show_hs_tariff_xnudge'] = 177;
    
            //Country of origin
            $this->default_config_values['show_country_of_origin_number'] = 0;
            $this->default_config_values['show_country_of_origin_number_fixed_value'] = 'Thailand';
            $this->default_config_values['show_country_of_origin_number_code'] = 'sku';
            $this->default_config_values['show_country_of_origin_xnudge'] = 210;
            
            //Signature
            $this->default_config_values['show_signature_image'] = 1;
            $this->default_config_values['signature_image'] = '';
            $this->default_config_values['show_signature_image_nudge'] = '85,2';
         
            //Movable text
            $this->default_config_values['show_fixed_text_yn'] = 0;
            $this->default_config_values['show_fixed_text'] = 'This is a sample text example';
            $this->default_config_values['show_fixed_text_width'] = 150;
            $this->default_config_values['show_fixed_text_font_family'] = 'helvetica';
            $this->default_config_values['show_fixed_text_font_size'] = 8;
            $this->default_config_values['show_fixed_text_nudge'] = '100,4.5';     
        
            //Print date
            $this->default_config_values['show_print_date'] = 1;
            $this->default_config_values['show_print_date_text'] = '';
            $this->default_config_values['show_print_date_width'] = 100;
            $this->default_config_values['date_format'] = 'M. j, Y';
            $this->default_config_values['show_print_date_nudge'] = '140,10';

            $this->default_config_values['show_currency_symbol'] = 1;
            $this->default_config_values['currency_code'] = 'THB';

            $this->default_config_values['label_width'] = 180;
            $this->default_config_values['label_height'] = 250;
        }
    }

}