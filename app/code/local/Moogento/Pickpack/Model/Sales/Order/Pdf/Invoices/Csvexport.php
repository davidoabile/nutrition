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
* File        Csvorders.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Csvexport extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices{
	public function getCsvexport($data){
		$csv_output = '';
		$field_quotes = '"';
		$separator = ",";
		$header_column = '';
		$header = "config_id" . "," . "scope" . "," . "scope_id" . "," . "path" . "," . "value";
		
		$columns = explode(",", $header);
		foreach ($columns as $key => $value) {
			$header_column .= $field_quotes . $value . $field_quotes . $separator;
		}
		$header_column .= "\n";
		$csv_output = $header_column;
		foreach ($data as $key => $row) {
			foreach ($columns as $key => $value) {
				$value = trim($value);
				$csv_output .= $field_quotes . $row[$value] . $field_quotes . $separator;
			}
			$csv_output .= "\n";
		}
		
		return $csv_output;
	}
}
?>