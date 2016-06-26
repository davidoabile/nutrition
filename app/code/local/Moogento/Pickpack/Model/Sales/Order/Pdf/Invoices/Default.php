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
 * File        Default.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://www.moogento.com/License.html
 */
/*
Print PDF Default for PDF invoice, PDF Packing Sheet and both.
*/

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Default extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $_orderCollection = array();
    protected $_pageFonts = array();
    
    protected $_itemsCollection = array();
    
    protected $_productsCollection = array();   
    protected $_item_qty_array = array();
    protected $pre_print_time =0;
    protected $next_print_time =0;
    protected $max_print_time = 0;
    protected $runtime =0;
    protected $warehouse_title = array();

    protected $columns_xpos_array = array(); //this value use to save xpos to caculate columns at mid page
    
    private function showTopBarcode($page, $order_id, $config_values, $y2,$padded_right)
    {
        $barcode_font_size = 14;
        $barcode_fontsize_shiftleft = 0;
        if(isset($config_values['barcode_type']))
            $barcode_type = $config_values['barcode_type'];
        if(isset($config_values['font_family_barcode']))
            $font_family_barcode = $config_values['font_family_barcode'];
        if(isset($config_values['black_color']))
            $black_color = $config_values['black_color'];
        if(isset($config_values['barcode_nudge']))
            $barcode_nudge = $config_values['barcode_nudge'];

		if(isset($config_values['show_top_logo_yn']))
            $show_top_logo_yn = $config_values['show_top_logo_yn'];

        if ($barcode_type !== 'code128') {
            $barcode_font_size += 12;
            $barcode_fontsize_shiftleft += 75;
        }
        $long_barcode_shiftup = 0;
        $barcodeString_pre = $order_id;
        $barcodeString = $this->convertToBarcodeString($barcodeString_pre, $barcode_type);
        $barcode_width_multiplier = 1.35;
        if (strlen($barcodeString_pre) > 11) {
            if ($barcode_type !== 'code128') $barcode_fontsize_shiftleft += 32;
            $barcode_width_multiplier = 1.19;
            $long_barcode_shiftup = 20;
            $barcode_fontsize_shiftleft += (((16 - ($barcode_font_size)) * 11) * 1);
        } else {
            $barcode_fontsize_shiftleft += ((16 - $barcode_font_size) * 7);
        }

        $barcodeWidth = (($barcode_width_multiplier * $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size)) - 13.5 + $barcode_fontsize_shiftleft);

        $page->setFillColor($black_color);
        $this->_setFontBold($page, 10);
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
        $barcode_nudge[0] = trim((int)$barcode_nudge[0]);
        $barcode_nudge[1] = trim((int)$barcode_nudge[1]);
        if(isset($show_top_logo_yn) && ($show_top_logo_yn == 0)) {
            $barcode_nudge[1] -= 30;
        } // pull the barcode out from under the titlebar
        $page->drawText($barcodeString, ($padded_right - $barcodeWidth + $barcode_nudge[0]), ($y2 - 9 + $long_barcode_shiftup + $barcode_nudge[1]), 'CP1252');
    }
    
    protected function _getConfigTrolley($field, $default = '', $add_default = true, $group = 'trolleybox_picklist', $store = null, $trim = true,$section = 'trolleybox_options')
  {
        if($group=='general')
        {
            return parent::_getConfig($field,$default,$add_default,$group,$store);
        }
        if ($trim)
            $value = trim(Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store));
        else
            $value = Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store);
        if (strstr($field, '_color') !== FALSE) {
            if ($value != 0 && $value != 1) {
                $value = checkColor($value);
            }
        }
        
        if ($value == '') {
            return $default;
        } else {
            if ($field == 'csv_field_separator' && $value == ',')
                return $value;
            if (($value !== '') && (strpos($value, ',') !== false) && (strpos($default, ',') !== false)) 
                {
                $values   = explode(",", $value);
                $defaults = explode(",", $default);
                
                if ($add_default === true) {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= ($values[$i] + $defaults[$i]);
                        else
                            $value .= $v;
                        $count++;
                    }
                } else {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= $values[$i];
                        else
                            $value .= $v;
                        $count++;
                    }
                }
            } else {
                $value = ($add_default) ? ($value + $default) : $value;
            }
            return $value;
        }
    }


    private function getRotateReturnAddress($rotate_return_address)
    {
        
        switch ($rotate_return_address) {
            case 0:
                $rotate = 0;
                break;
            case 1:
                $rotate = 3.14 / 2;
                break;
            case 2:
                $rotate = -3.14 / 2;
                break;
            default:
                $rotate = 0;
        }
        return $rotate;
    }
    
    private function rotateLabel($case_rotate,&$page,$page_top,$padded_right,$nudge_rotate_address_label)
    {
        // X nudge --- Y nudge
//      1. Move top: 
//        Increase Y 50px and Decrease X 50px
//      2. Move bottom: 
//        Decrease Y 50px and Increase X 50px
//      3. Move left: 
//        Decrease X 50px and Decrease Y 50px
//      4. Move right: 
//        Increase X 50px and Increase Y 50px
        //Move all to bototm 100px
        /*
        $x = -105;
        $y = -55;
        if($nudge_rotate_address_label[0] < 0 )
        {
            //Move right
            $x += $nudge_rotate_address_label[0];
            $y += $nudge_rotate_address_label[0];       
        }
        else
            if($nudge_rotate_address_label[0] > 0 )
            {
                //Move left
                $x -= $nudge_rotate_address_label[0];
                $y -= $nudge_rotate_address_label[0];       
            }
        
        if($nudge_rotate_address_label[1] > 0 )
        {
            //Move top
            $x += $nudge_rotate_address_label[1];
            $y -= $nudge_rotate_address_label[1];       
        }
        else
            if($nudge_rotate_address_label[1] < 0 )
            {
                //Move bottom
                $x -= $nudge_rotate_address_label[1];
                $y += $nudge_rotate_address_label[1];       
            }

        $nudge_rotate_address_label[0] = $x;
        $nudge_rotate_address_label[1] = $y;

        switch ($case_rotate) {
            case 1:
                // //TODO Moo rotate 90
                    $rotate = 3.14 / 2;
                    break;
            case 2:
               //TODO Moo rotate 270
                    $rotate = -3.14 / 2;
                    break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], $rotate);
        */
        $x = -155;
        $y = -55;
      
        $x += $nudge_rotate_address_label[0];
        $x += $nudge_rotate_address_label[1];
        $y += $nudge_rotate_address_label[1];
        $y -= $nudge_rotate_address_label[0];
        $nudge_rotate_address_label[0] = $x;
        $nudge_rotate_address_label[1] = $y;

        switch ($case_rotate) {
            case 1:
                // //TODO Moo rotate 90
                    $rotate = 3.14 / 2;
                    break;
            case 2:
               //TODO Moo rotate 270
                    $rotate = -3.14 / 2;
                    break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], $rotate);
    }
    
    private function getSerialCode($order, $item){
        $serial_code = '';
        if(Mage::helper('pickpack')->isInstalled('Mmsmods_Serialcodes')){
            if ($item->getSerialCodes()) {
            /* If so, load the product (used to determine status) */
                $product = Mage::helper('pickpack')->getProductForStore($item->getProductId(), $order->getStoreId());
            /* Retrieve the serial code type from the item */
                $codetype = $item->getSerialCodeType();
            /* Load the serial codes for this item into an array */
                $codes = explode("\n",$item->getSerialCodes());
            /* Retrieve a parallel array containing the internal id for each serial code */
                $codeids = array_pad(explode(',',$item->getSerialCodeIds()),count($codes),'');
            /* Loop through each serial code */
                for ($i=0; $i<count($codes); $i++) {
            /* Check to see if the serial code status is pending; if so hide it from customer */
                  if (Mage::getSingleton('serialcodes/serialcodes')
                                  ->hidePendingCodes($order, $item, $product, $codeids[$i], $i)) {
                    $codes[$i] = Mage::helper('serialcodes')->__('Issued when payment received.');
                    }
                }
            /* Display serial codes */
                if($serial_code == '')
                    $serial_code = $codes[$i];
                else
                    $serial_code = $serial_code . ', ' . $codes[$i];
            }
        }
        return $serial_code;
    }

    private function isMageEnterprise() {
        return Mage::getConfig ()->getModuleConfig ( 'Enterprise_Enterprise' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_AdminGws' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Checkout' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Customer' );
    }
	
	private function convert_state($name, $to='abbrev') {
		$states = array(
		array('name'=>'Alabama', 'abbrev'=>'AL'),
		array('name'=>'Alaska', 'abbrev'=>'AK'),
		array('name'=>'Arizona', 'abbrev'=>'AZ'),
		array('name'=>'Arkansas', 'abbrev'=>'AR'),
		array('name'=>'California', 'abbrev'=>'CA'),
		array('name'=>'Colorado', 'abbrev'=>'CO'),
		array('name'=>'Connecticut', 'abbrev'=>'CT'),
		array('name'=>'Delaware', 'abbrev'=>'DE'),
		array('name'=>'Florida', 'abbrev'=>'FL'),
		array('name'=>'Georgia', 'abbrev'=>'GA'),
		array('name'=>'Guam', 'abbrev'=>'GU'),
		array('name'=>'Hawaii', 'abbrev'=>'HI'),
		array('name'=>'Idaho', 'abbrev'=>'ID'),
		array('name'=>'Illinois', 'abbrev'=>'IL'),
		array('name'=>'Indiana', 'abbrev'=>'IN'),
		array('name'=>'Iowa', 'abbrev'=>'IA'),
		array('name'=>'Kansas', 'abbrev'=>'KS'),
		array('name'=>'Kentucky', 'abbrev'=>'KY'),
		array('name'=>'Louisiana', 'abbrev'=>'LA'),
		array('name'=>'Maine', 'abbrev'=>'ME'),
		array('name'=>'Maryland', 'abbrev'=>'MD'),
		array('name'=>'Massachusetts', 'abbrev'=>'MA'),
		array('name'=>'Michigan', 'abbrev'=>'MI'),
		array('name'=>'Minnesota', 'abbrev'=>'MN'),
		array('name'=>'Mississippi', 'abbrev'=>'MS'),
		array('name'=>'Missouri', 'abbrev'=>'MO'),
		array('name'=>'Montana', 'abbrev'=>'MT'),
		array('name'=>'Nebraska', 'abbrev'=>'NE'),
		array('name'=>'Nevada', 'abbrev'=>'NV'),
		array('name'=>'New Hampshire', 'abbrev'=>'NH'),
		array('name'=>'New Jersey', 'abbrev'=>'NJ'),
		array('name'=>'New Mexico', 'abbrev'=>'NM'),
		array('name'=>'New York', 'abbrev'=>'NY'),
		array('name'=>'North Carolina', 'abbrev'=>'NC'),
		array('name'=>'North Dakota', 'abbrev'=>'ND'),
		array('name'=>'Ohio', 'abbrev'=>'OH'),
		array('name'=>'Oklahoma', 'abbrev'=>'OK'),
		array('name'=>'Oregon', 'abbrev'=>'OR'),
		array('name'=>'Pennsylvania', 'abbrev'=>'PA'),
		array('name'=>'Puerto Rico', 'abbrev'=>'PR'),
		array('name'=>'Rhode Island', 'abbrev'=>'RI'),
		array('name'=>'South Carolina', 'abbrev'=>'SC'),
		array('name'=>'South Dakota', 'abbrev'=>'SD'),
		array('name'=>'Tennessee', 'abbrev'=>'TN'),
		array('name'=>'Texas', 'abbrev'=>'TX'),
		array('name'=>'Utah', 'abbrev'=>'UT'),
		array('name'=>'Vermont', 'abbrev'=>'VT'),
		array('name'=>'Virginia', 'abbrev'=>'VA'),
		array('name'=>'Washington', 'abbrev'=>'WA'),
		array('name'=>'West Virginia', 'abbrev'=>'WV'),
		array('name'=>'Wisconsin', 'abbrev'=>'WI'),
		array('name'=>'Wyoming', 'abbrev'=>'WY'),
		array('name'=>'Alberta', 'abbrev'=>'AB'),
		array('name'=>'British Columbia', 'abbrev'=>'BC'),
		array('name'=>'Manitoba', 'abbrev'=>'MB'),
		array('name'=>'New Brunswick', 'abbrev'=>'NB'),
		array('name'=>'Newfoundland and Labrador', 'abbrev'=>'NL'),
		array('name'=>'Northwest Territories', 'abbrev'=>'NT'),
		array('name'=>'Nova Scotia', 'abbrev'=>'NS'),
		array('name'=>'Nunavut', 'abbrev'=>'NU'),
		array('name'=>'Ontario', 'abbrev'=>'ON'),
		array('name'=>'Prince Edward Island', 'abbrev'=>'PE'),
		array('name'=>'Quebec', 'abbrev'=>'QC'),
		array('name'=>'Saskatchewan', 'abbrev'=>'SK'),
		array('name'=>'Yukon', 'abbrev'=>'YT')
		);

		$return = false;
		foreach ($states as $state) {
			if ($to == 'name') {
				if (strtolower($state['abbrev']) == strtolower($name)){
					$return = $state['name'];
					break;
				}
			} else if ($to == 'abbrev') {
				if (strtolower($state['name']) == strtolower($name)){
					$return = strtoupper($state['abbrev']);
					break;
				}
			}
		}
		return $return;
	}
	
    private function checkPayment($paymentInfo){
        $is_payment_code = false;
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $payment_test = implode(',', $payment);
        $payment_test = strtolower($payment_test);
        $payments = array('Credit Card', 'American Express', 'Master Card', 'Cash on Delivery', 'Purchase Order Purchase Order', 'Payment Visa', 'Payment Mastercard', 'Mastercard#', 'MasterCard', 'Pay with Paypal');
        foreach ($payments as $value) {
            if (strpos($payment_test, strtolower($value)) !== false) {
                $is_payment_code = true;
                return $is_payment_code;
            }
        }
        return $is_payment_code;
    }

    private function cleanPaymentFull($paymentInfo)
    {
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $payment_test = implode(',', $payment);
        $payment_test = trim(str_ireplace(
            array('Credit or Debit Card'),
            array('Card'), $payment_test));
        $payment_test = preg_replace('~^\s*~', '', $payment_test);
        $payment_test = trim(preg_replace('~^:~', '', $payment_test));
        $payment_test = preg_replace('~Paypal(.*)$~i', 'Paypal', $payment_test);
        $payment_test = preg_replace('~Account(.*)$~i', 'Account', $payment_test);
        $payment_test = preg_replace('~Processed Amount(.*)$~i', '', $payment_test);
        $payment_test = preg_replace('~Payer Email(.*)$~i', '', $payment_test);
        $payment_test = preg_replace('~Charge:$~i', '', $payment_test);
        $payment_test = str_ireplace('Expiration', '|Expiration', $payment_test);
        $payment_test = str_ireplace('Name on the Card', '|Name on the Card', $payment_test);
        $payment_test = preg_replace('~^\-~', '', $payment_test);
        $payment_test = preg_replace('~Check / Money order(.*)$~i', 'Check / Money order', $payment_test);
        $payment_test = preg_replace('~Cheque / Money order(.*)$~i', 'Cheque / Money order', $payment_test);
        $payment_test = preg_replace('~Make cheque payable(.*)$~i', '', $payment_test);
        $payment_test = str_ireplace(
            array('CardCC', 'CC Type', 'MasterCardCC', 'MasterCC', ': MC', ': Visa', 'Payment Visa', 'Payment MC', 'CCAmex', 'AmexCC', 'Type: Amex', 'CC Exp.', 'CC (Sage Pay)CC'),
            array('CC', 'CC, Type', 'MC', 'MC', ' MC', ' Visa', 'Visa', 'MC', 'Amex', 'Amex', 'Amex', 'Exp.', '(Sage Pay)'), $payment_test);
        $payment_test = preg_replace('~:$~', '', $payment_test);

        preg_match('~\b(?:\d[ -]*?){13,16}\b~', $payment_test, $cc_matches);
        if (isset($cc_matches[0])) {
            $replacement_cc = str_pad(substr($cc_matches[0], -4), 8, '*', STR_PAD_LEFT);
            $payment_test = str_replace($cc_matches[0], $replacement_cc, $payment_test);
        }

        $payment_test = trim($payment_test);
        return $payment_test;
    }

    private function getPaymentOrder($order)
    {
        $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
        $payment_order = $order->getPayment();
        foreach ($allAvailablePaymentMethods as $payment) {
            if ($payment->getId() == $payment_order->getMethod())
                return $payment_order;
        }
        return $payment_order = '';
    }

    protected function getMaxShippingAddresBackgroundY($order, $store_id)
    {
        $maxShippingAddresBackgroundY = 0;
        $shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $store_id);
        try {
            $shipping_address_background = unserialize($shipping_address_background);
        } catch (Exception $e) {
        }
        $print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
        if ($print_row != -1) {
            $shipping_background_nudge_y = $shipping_address_background[$print_row]['ynudge'];
            $maxShippingAddresBackgroundY = 180 + $shipping_background_nudge_y;
        }
        return $maxShippingAddresBackgroundY;
    }
    
    protected function getConfigValue2($path)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName4 = $resource->getTableName('core_config_data');
        $query = 'SELECT * FROM '.$tableName4.' WHERE path like "%'.$path.'%"'.' LIMIT 1';
        $data  = $readConnection->fetchAll($query);
        $config_value = $data[0]['value'];
         try {
            $shipping_address_background = unserialize($config_value);
            return $shipping_address_background;
        }
        catch (Exception $e) {
            return '';
        }
    }
    
    protected function showShippingAddresBackground($order, $page_top, $wonder, $store_id, $page, $padded_left, $scale = 100, $label_width = 250, $nudge_shipping_addressX = 0, $resolution = null)
    {
        $show_top_shipping_background_yn = $this->_getConfig('top_shipping_address_background_yn', 0, false, $wonder, $store_id);
        $show_bottom_shipping_background_yn = $this->_getConfig('shipping_address_background_yn', 0, false, $wonder, $store_id);
        $bottom_shipping_address_yn = $this->_getConfig('pickpack_bottom_shipping_address_yn', 0, false, $wonder, $store_id);
        $shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $store_id);
        if(strlen(trim($shipping_address_background)) == 0)
        {
            return;
        }
        try {
            $shipping_address_background = unserialize($shipping_address_background);
            if($shipping_address_background == false)
            {
                $shipping_address_background = $this->getConfigValue2('shipping_address_background_shippingmethod');
            }
            $shipping_address_background = $this->checkCourrierrulesAndM2epro($shipping_address_background);
        } catch (Exception $e) {
            return;
        }
        $top_or_bottom = '';
        if ($show_top_shipping_background_yn) {
            $top_or_bottom = $page_top;
            $this->printShippingAddressBackground($order, $scale, $shipping_address_background, $top_or_bottom, $page, $padded_left, $label_width = 250, $nudge_shipping_addressX = 0, $resolution);
        }
        if (($bottom_shipping_address_yn == 1) && $show_bottom_shipping_background_yn) {
            $top_or_bottom = 240;
            $this->printShippingAddressBackground($order, $scale, $shipping_address_background, $top_or_bottom, $page, $padded_left, $label_width = 250, $nudge_shipping_addressX = 0, $resolution);
            //TODO Moo cont. 1
//             $this->printShippingAddressBackground($order, $shipping_address_background, $top_or_bottom, $page, 0, $label_width = 540, $nudge_shipping_addressX = 0, $resolution);
        }
    }

    public function checkItemBelongInvoiceDetail($item_sku, $invoice_id)
    {
        $isBelong = false;
        if ($invoice_id != '') {

            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($item->getSku() == $item_sku) {
                    $isBelong = true;
                    break;
                }
            }

        }
        return $isBelong;
    }

    public function getItemBelongInvoice($item_sku, $invoice_id)
    {
        $item_belong = '';
        if ($invoice_id != '') {        
            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($item->getSku() == $item_sku) {
                    $item_belong = $item;
                    break;
                }
            }
        }
        return $item_belong;
    }

    public function checkItemBelongShipment($item_sku, $shipment_ids)
    {
        $isBelong = false;
        $shipment_model = Mage::getModel('sales/order_shipment')->load($shipment_ids);
        $items = $shipment_model->getAllItems();
        foreach ($items as $item) {
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }
            if ($item->getSku() == $item_sku) {
                $isBelong = true;
                break;
            }
        }
        return $isBelong;
    }

    public function getItemBelongShipment($item_sku, $shipment_ids)
    {
        $item_belong = '';
        if ($shipment_ids != '') {
            $invoice = Mage::getModel('sales/order_shipment')->load($shipment_ids);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($item->getSku() == $item_sku) {
                    $item_belong = $item;
                    break;
                }
            }
        }
        return $item_belong;
    }

    public function getQtyString($from_shipment, $shiped_items_qty, $item, $qty, $wonder, $invoice_id = '', $shipment_ids = '')
    {
        $qty_string = $qty;
        if (!empty($invoice_id) || !empty($shipment_ids)) {
            if ($invoice_id) {
                if ($this->checkItemBelongInvoiceDetail($item->getSku(), $invoice_id)) {
                    $item_belong_invoice = $this->getItemBelongInvoice($item->getSku(), $invoice_id);
                    if ($item_belong_invoice != '') {
                        $qty_string = (int)$item_belong_invoice->getQty();
                    }
                }
            }
            if ($shipment_ids) {
                if ($this->checkItemBelongShipment($item->getSku(), $shipment_ids)) {
                    $item_belong_shipment = $this->getItemBelongShipment($item->getSku(), $shipment_ids);
                    if ($item_belong_shipment != '') {
                        $qty_string = (int)$item_belong_shipment->getQty();
                    }
                }
            }
            return $qty_string;
        }
        $store_id = Mage::app()->getStore()->getId();
        if ($wonder == "pack") {
            $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'wonder', $store_id);
        } else {
            $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'wonder_invoice', $store_id);
        }
        if ($from_shipment == 'shipment') {
            switch ($show_qty_options) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$shiped_items_qty[$item->getData('product_id')]) . ' s:' . (int)$shiped_items_qty[$item->getData('product_id')] . ' o:' . (int)$item->getData('qty_ordered');

                    break;
                case 3:
                    $qty_string = ($qty - (int)$shiped_items_qty[$item->getData('product_id')]);

                    break;

                case 4:
                    $qty_string = (int)$item->getData("qty_invoiced");

                    break;
            }
        } else {
            switch ($show_qty_options) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$item->getQtyShipped()) . ' s:' . (int)$item->getQtyShipped() . ' o:' . $qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$item->getQtyShipped());

                    break;

                case 4:
                    $qty_string = (int)$item->getData("qty_invoiced");

                    break;
            }

        }
        return $qty_string;
    }

    public function getQtyStringBundle($from_shipment, $product_build_value, $qty, $wonder, $invoice_id = '', $shipment_id = '')
    {        
     /*   
        //this code only gets the parent product qty
           if (!empty($invoice_id) || !empty($shipment_id)) {
      
            if (isset($product_build_value['bundle_options_sku']))
                $sku_real = $product_build_value['sku_bundle_real'];
            else
                $sku_real = $product_build_value['sku_print'];
                
            if ($invoice_id) {
                if ($this->checkItemBelongInvoiceDetail($sku_real, $invoice_id)) {
                    $item_belong_invoice = $this->getItemBelongInvoice($sku_real, $invoice_id);

                    if ($item_belong_invoice != '') {
                        $qty_string = (int)$item_belong_invoice->getQty();
                    }
                }
            }
            if ($shipment_id) {
                if ($this->checkItemBelongShipment($sku_real, $shipment_ids)) {
                    $item_belong_shipment = $this->getItemBelongShipment($sku_real, $shipment_ids);
                    if ($item_belong_shipment != '') {
                        $qty_string = (int)$item_belong_shipment->getQty();
                    }
                }
            }
            return $qty_string;
        } 
        */      
        $store_id = Mage::app()->getStore()->getId();
        if ($wonder == "pack") {
            $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'wonder', $store_id);
        } else {
            $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'wonder_invoice', $store_id);
        }
        if ($from_shipment == 'shipment') {
            switch ($show_qty_options) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$product_build_value['bundle_qty_shipped']) . ' s:' . (int)$product_build_value['bundle_qty_shipped'] . ' o:' . (int)$qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$product_build_value['bundle_qty_shipped']);

                    break;

                case 4:
                    $qty_string = (int)$product_build_value['bundle_qty_invoiced'];

                    break;
            }
        } else {
            switch ($show_qty_options) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$product_build_value['bundle_qty_shipped']) . ' s:' . (int)$product_build_value['bundle_qty_shipped'] . ' o:' . (int)$qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$product_build_value['bundle_qty_shipped']);

                    break;

                case 4:
                    $qty_string = (int)$product_build_value['bundle_qty_invoiced'];

                    break;
            }

        }
        return $qty_string;
    }

    private function createMsgArray2($gift_message, $max_width = 250, $font_size = 10, $font_temp = null)
    {
        if ($font_temp == null)
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        else
            $font_temp = $this->getFontName2($font_temp, 'regular', 0);
        $line_width = $this->parseString('1234567890', $font_temp, $font_size * 0.8);
        $char_width = $line_width / 11;

        $max_chars = round($max_width / $char_width);

        $gift_message_wordwrap = wordwrap($gift_message, $max_chars, "\n", false);
        $gift_msg_array = array();
        $token = strtok($gift_message_wordwrap, "\n");
        $msg_line_count = 2.5;
        while ($token != false) {
            $gift_msg_array[] = $token;
            $msg_line_count++;
            $token = strtok("\n");
        }
        return $gift_msg_array;
    }

    private function drawBackgroundGiftMessage($background_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg)
    {
        if (($background_color_gift_message != '') && ($background_color_gift_message != '#FFFFFF')) {
            $page->setFillColor($background_color_temp);
            $page->setLineColor($background_color_temp);
            $page->setLineWidth(0.5);
            $page->drawRectangle($left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
        }
    }

    private function getHeightLine($gift_msg_array, $font_size_gift_message)
    {
        $temp_height = 0;
        foreach ($gift_msg_array as $gift_msg_line) {
            $temp_height += $font_size_gift_message + 3;
        }
        return $temp_height;
    }

    private function getProductGiftMessage($gift_message_array)
    {
        $gift_message_combined = '';
        if (isset($gift_message_array['items']))
            foreach ($gift_message_array['items'] as $item_key => $item_message) {
                if (isset($item_message['printed'])) {
                    if ($item_message['printed'] == 0) {
                        if (isset($item_message['message-content']) && is_array($item_message['message-content'])) {
                            foreach ($item_message['message-content'] as $k2 => $v2)
                                $gift_message_combined .= "\n" . $v2;
                        }
                    }
                }
            }
        return $gift_message_combined;
    }

    private function getProductGiftMessageUnderShip($order, $max_chars_message)
    {
        $itemsCollection = $order->getAllVisibleItems();
        // add product gift message and history ebay note to order message
        $gift_message_combined = '';
        foreach ($itemsCollection as $item) {
            $item_message = $this->getItemGiftMessage($item, $max_chars_message);
            if (count($item_message) > 2) {
                $item_message['message-content'] = $item_message[2];
                $item_message['from'] = $item_message[0];
                $item_message['to'] = $item_message[1];
                if (isset($item_message) && is_array($item_message)) {
                    foreach ($item_message['message-content'] as $k2 => $v2)
                        $gift_message_combined .= "\n" . $v2;
                }
            }
        }
        return $gift_message_combined;
    }

    private function skuWordwrap($minDistanceSku, $font_size_sku, $sku_print)
    {
        $maxWidthPage = $minDistanceSku - 10;
        $chunks = array();
        if (strlen($sku_print) > 0) {
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $line_width = $this->parseString($sku_print, $font_temp, $font_size_sku);
            $char_width = $line_width / strlen($sku_print);
            $max_chars = round($maxWidthPage / $char_width);

            if (strlen($sku_print) > $max_chars) {
                $chunks = str_split($sku_print, $max_chars);
            } else
                $chunks[] = $sku_print;
        }
        return $chunks;
    }

    private function checkFilterNotes($comment, $notes_filter)
    {
        $is_filter = false;
        $note_filter_array = explode('|', $notes_filter);
        foreach ($note_filter_array as $filter) {
            if (stripos($comment, $filter) !== false) {
                $is_filter = true;
                break;
            }
        }
        return $is_filter;
    }

    private function getAllSupplier($order, $supplier_attribute)
    {
        $is_warehouse_supplier = 0;
        if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
        {
            if($supplier_attribute == 'warehouse')
            {
                $is_warehouse_supplier = 1;
            }
        }
        $itemsCollection = $order->getAllVisibleItems();
        $supplier_array = array();
        foreach ($itemsCollection as $item) {
            if($is_warehouse_supplier == 1)
            {
                $warehouse_title = $item->getWarehouseTitle();
                $warehouse = $item->getWarehouse();
                $warehouse_code = $warehouse->getData('code');
                $supplier = $warehouse_code;
                $warehouse_code = trim(strtoupper($supplier));
                $this->warehouse_title[$warehouse_code] = $warehouse_title;
            }
            else
            {
                $product = $this->_getProductFromItem($item);            
                $supplier = $this->getProductAttributeValue($product, $supplier_attribute);
            }
            if (is_array($supplier)) $supplier = implode(',', $supplier);
            if (!$supplier) $supplier = '~Not Set~';
            $supplier_array[] = trim(strtoupper($supplier));
        }       
        return array_unique($supplier_array);
    }

    protected function getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute,$bundle_children = false,$product_id = null)
    {

        if ($product_sku_barcode_attribute != '') {
            if($bundle_children == true && $product_id != null)
            {
                $attributeName = $product_sku_barcode_attribute;
                $product = Mage::helper('pickpack')->getProduct($product_id);
                if ($product->getData($attributeName)) {
                    $barcode_array[$product_sku_barcode_attribute] = $this->getProductAttributeValue($product, $attributeName);
                } else {
                    $barcode_array[$product_sku_barcode_attribute] = '';
                }
            }
            else
            {
                switch ($product_sku_barcode_attribute) {
                    case 'sku':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['sku_print'];
                        break;
                    case 'name':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['display_name'];
                        break;
                    case $shelving_real_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving_real'];
                        break;
                    case $shelving_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving'];
                        break;
                    case $shelving_2_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving2'];
                        break;
                    case 'category':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['%category%'];
                        break;
                    case 'product_id':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['product_id'];
                        break;
                    default:
                        $attributeName = $product_sku_barcode_attribute;
                        $product_id = $product_build_value['product_id'];
                        $product = Mage::helper('pickpack')->getProduct($product_id);
                        if ($product->getData($attributeName)) {
                            $barcode_array[$product_sku_barcode_attribute] = $this->getProductAttributeValue($product, $attributeName);
                        } else {
                            $barcode_array[$product_sku_barcode_attribute] = '';
                        }
                        break;
                }
            }
             if($barcode_array[$product_sku_barcode_attribute])
                $new_product_barcode = $new_product_barcode . $barcode_array[$product_sku_barcode_attribute] . $barcode_array['spacer']. ' ';
        }
        return $new_product_barcode;
    }

    protected function getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,$counter=1,$bundle_children=false,$product_id = null)
    {
        if($counter == 2)
        
        $barcode_array = array();
        $new_product_barcode = '';
        if($counter == 1)
        {
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_1', '', false, $wonder, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_2', '', false, $wonder, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_3', '', false, $wonder, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_4', '', false, $wonder, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_5', '', false, $wonder, $store_id);
        $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_spacer', '', false, $wonder, $store_id);
        }
        else
        {
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_1', '', false, $wonder, $store_id);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_2', '', false, $wonder, $store_id);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_3', '', false, $wonder, $store_id);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_4', '', false, $wonder, $store_id);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_5', '', false, $wonder, $store_id);
            $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_2_spacer', '', false, $wonder, $store_id);
        }
            if ($product_sku_barcode_spacer != '') {
                $barcode_array['spacer'] = $product_sku_barcode_spacer;
            } else
                $barcode_array['spacer'] = '';
            foreach ($product_sku_barcode_attributes as $product_sku_barcode_attribute)
            {
                if($bundle_children == true)
                    $new_product_barcode = $this->getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute,$bundle_children,$product_id);
                else
                    {
                        $new_product_barcode = $this->getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute);
                    }
            }

            
        $new_product_barcode = rtrim($new_product_barcode,$barcode_array['spacer']);
        
        return $new_product_barcode;
    }
    
    protected function getCombineAttribute($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id)
    {
        $barcode_array = array();
        $new_product_barcode = '';
        $product_attributes[] = trim($this->_getConfig('product_attribute_1', '', false, $wonder, $store_id));
        $product_attributes[] = trim($this->_getConfig('product_attribute_2', '', false, $wonder, $store_id));
        $product_attributes[]= trim($this->_getConfig('product_attribute_3', '', false, $wonder, $store_id));
        $product_attributes[] = trim($this->_getConfig('product_attribute_4', '', false, $wonder, $store_id));
        $product_attributes[] = trim($this->_getConfig('product_attribute_5', '', false, $wonder, $store_id));

        $barcode_array['spacer'] = $product_sku_barcode_spacer = ",";
        foreach ($product_attributes as $product_attribute)
            $new_product_barcode = $this->getSkuBarcodeByAttribute2($product_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute);
        $new_product_barcode = trim($new_product_barcode);
        return $new_product_barcode;
    }
    
    private function getTrackingNumber($order){
        $tracking_number = array();
        $tracking_number_string = '';
         $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
        ->setOrderFilter($order)
        ->load();
        
        foreach ($shipmentCollection as $shipment){
            foreach($shipment->getAllTracks() as $tracknum)
                {
                    $tracking_number[]=$tracknum->getNumber();
                }
        }
        $tracking_number_string = implode(',', $tracking_number);
        return $tracking_number_string;
    }
    
    private function drawBarcodeTrackingNumber($page, $order, $barcode_type, $font_family_barcode, $barcode_font_size, $white_color, $addressFooterXY, $tracking_number_barcode_nudge){
        $tracking_number = $this->getTrackingNumber($order);
        if($tracking_number != ''){
            $barcodeString = $this->convertToBarcodeString($tracking_number, $barcode_type);
            $barcode_font_size_action = $barcode_font_size;
            if($barcode_font_size > 18) $barcode_font_size = 15;
            $barcodeWidth = 1.35 * $this->parseString($tracking_number, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
            $page->setFillColor($white_color);
            $page->setLineColor($white_color);
            $page->drawRectangle(($addressFooterXY[0] - 5 + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] - 5 + $tracking_number_barcode_nudge[1] ), ($addressFooterXY[0] + $barcodeWidth + 5 + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 1.4) + $tracking_number_barcode_nudge[1]));
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
            $page->drawText($barcodeString, ($addressFooterXY[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_barcode_nudge[1]), 'CP1252');
            if($barcode_font_size_action > 18)
            {
                if($barcode_font_size_action > 18 && $barcode_font_size_action <= 24) $page->drawText($barcodeString, ($addressFooterXY[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                if($barcode_font_size_action >24 && $barcode_font_size_action <= 36){
                    $page->drawText($barcodeString, ($addressFooterXY[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                    $page->drawText($barcodeString, ($addressFooterXY[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_barcode_nudge[1] + 38), 'CP1252');
                }
            }
        }
    }
    
    private function drawTrackingNumber($page,$order, $tracking_number_fontsize, $white_color, $addressFooterXY, $tracking_number_nudge, $tracking_number_barcode_nudge){
        $tracking_number = $this->getTrackingNumber($order);
        if($tracking_number != '')
            $page->drawText($tracking_number, ($addressFooterXY[0] + $tracking_number_nudge[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_nudge[1]+ $tracking_number_barcode_nudge[1] - $tracking_number_fontsize), 'CP1252');
    }
    
    private function getSkuArr($itemCollection){
        $sku_array = array();
        foreach($itemCollection as $item){
            $sku_array[] = $item->getSku();
        }
        return $sku_array;
    }
    private function getAmasAttribute(){
        $amas_attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $amas_attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $amas_attributes->addFieldToFilter('include_pdf', 1);
        $amas_attributes->getSelect()->order('checkout_step');
        $amas_attributes->getSelect()->order('sorting_order');
        return $amas_attributes;
    }
    private function getValueOrderAttribute($amas_attributes, $filter_custom_attributes_array, $order){
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
        $list = array();
        foreach ($amas_attributes as $attribute) {
            $check_label = $attribute->getData('attribute_code');
            if ((in_array($check_label, $filter_custom_attributes_array, true))) {
                continue;
            } else {
                $currentStore = $order->getStoreId();
                $storeIds = explode(',', $attribute->getData('store_ids'));
                if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds)) {
                    continue;
                }

                $value = '';

                switch ($attribute->getFrontendInput()) {
                    case 'select':
                        $options = $attribute->getSource()->getAllOptions(true, true);
                        foreach ($options as $option) {
                            if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode())) {
                                $value = $option['label'];
                                break;
                            }
                        }

                        break;
                    case 'date':
                        $value = $orderAttributes->getData($attribute->getAttributeCode());
                        $format = Mage::app()->getLocale()->getDateTimeFormat(
                            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                        );
                        if ('time' == $attribute->getNote()) {
                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
                        } else {
                            $format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));
                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString($format);
                        }
                        break;
                    case 'checkboxes':
                        $options = $attribute->getSource()->getAllOptions(true, true);
                        $checkboxValues = explode(',', $orderAttributes->getData($attribute->getAttributeCode()));
                        foreach ($options as $option) {
                            if (in_array($option['value'], $checkboxValues)) {
                                $value[] = $option['label'];
                            }
                        }
                        $value = implode(', ', $value);
                        break;
                    case 'boolean':
                        $value = $orderAttributes->getData($attribute->getAttributeCode()) ? 'Yes' : 'No';
                        $value = Mage::helper('catalog')->__($value);
                        break;
                    case 'textarea':
                        $text = $orderAttributes->getData($attribute->getAttributeCode());
                        $text = str_replace(array("\r\n", "\n", "\r"), '~~~', $text);
                        $value = array();
                        foreach (explode('~~~', $text) as $str) {
                            foreach (Mage::helper('core/string')->str_split($str, 99, true, true) as $part) {
                                if (empty($part)) {
                                    continue;
                                }
                                $value[] = $part;
                            }
                        }
                        break;
                    default:
                        $value = $orderAttributes->getData($attribute->getAttributeCode());
                        break;
                }

                if (is_array($value)) {
                    $list[$attribute->getFrontendLabel()] = $value;
                } else {
                    $list[$attribute->getFrontendLabel()] = str_replace('$', '\$', $value);
                }
            }
        }
        return $list;
    }
    private function getEbaySaleNumber($order){
        $result = '';
        if(Mage::helper('pickpack')->isInstalled('Ess_M2ePro')){
            $m2eproOrder = Mage::getModel('M2ePro/Order')->load($order->getId(), 'magento_order_id');
            if ($m2eproOrder->getId() && $m2eproOrder->getComponentMode() == 'ebay') {
                $result .= "\n" . '(SM #' . $m2eproOrder->getChildObject()->getSellingManagerId() . ')';
            }
        }
        $result = trim($result);
        return $result;
    }
    private function getMarketPlaceId($order){
        $ebay_order_id =''; 
        if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
            if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))){                 
                    $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
                    $collection->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
                    $collection->setCurPage(1) // 2nd page
                            ->setPageSize(1);
                    $collection_data = $collection->getData();

                     if(is_array($collection_data) && isset($collection_data[0]['ebay_order_id']))      
                        $ebay_order_id = $collection_data[0]['ebay_order_id'];           
                    else
                        $ebay_order_id ='';
            }

        }
        
        $amazon_order_id ='';   
        if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
            if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))){                 
                    $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
                    $collection->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
                    $collection->setCurPage(1) // 2nd page
                            ->setPageSize(1);
                    if(($collection->getData('amazon_order_id')))
                    {
//                             $amazon_order_id = $collection->getData()[0]['amazon_order_id'];    
                        $collection_data = $collection->getData();
                        if(is_array($collection_data))                        
                            $amazon_order_id = $collection_data[0]['amazon_order_id'];           
                        else
                            $amazon_order_id ='';             
                    }
                           
            }

        }
        if($ebay_order_id != ''){
            $marketPlaceId = $ebay_order_id;
        }
        elseif($amazon_order_id != ''){
            $marketPlaceId = $amazon_order_id;
        }else
            $marketPlaceId = $order->getRealOrderId();
        return $marketPlaceId;
    }
    public function getPdfDefault($orders = array(), $from_shipment = 'order', $invoice_or_pack = 'pack', $order_invoice_id = '', $shipment_ids = '',$order_items_arr = array(),$split_multiple='no')
    {
        /** @var Moogento_Pickpack_Helper_Data $helper */
        $helper = Mage::helper('pickpack');
        $magentoVersion = Mage::getVersion();
        
         if (!function_exists('show_error')) {
            function show_error($error_code, $source_path, $target_path)
                                            {

                                                // if there was an error, let's see what the error is about
                                                switch ($error_code) {

                                                    case 1:
                                                        echo 'Source file "' . $source_path . '" could not be found!';
                                                        break;
                                                    case 2:
                                                        echo 'Source file "' . $source_path . '" is not readable!';
                                                        break;
                                                    case 3:
                                                        echo 'Could not write target file "' . $source_path . '"!';
                                                        break;
                                                    case 4:
                                                        echo $source_path . '" is an unsupported source file format!';
                                                        break;
                                                    case 5:
                                                        echo $target_path . '" is an unsupported target file format!';
                                                        break;
                                                    case 6:
                                                        echo 'GD library version does not support target file format!';
                                                        break;
                                                    case 7:
                                                        echo 'GD library is not installed!';
                                                        break;
                                                    case 8:
                                                        echo '"chmod" command is disabled via configuration!';
                                                        break;

                                                }
                                                exit;

                                            }
                                            
        }
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Zebra_Image.php';
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/qrcode/qrlib.php';
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
        $image_simple = new SimpleImage();
        $PNG_TEMP_DIR = Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'qrcode'.DS;
        
        
        if (!file_exists($PNG_TEMP_DIR))
            mkdir($PNG_TEMP_DIR,0777,true);
        
        $image_zebra = new Zebra_Image();
        if (!function_exists('clearUTF')) {
            function clearUTF($s)
            {
                $r = '';
                $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
                for ($i = 0; $i < strlen($s1); $i++) {
                    $ch1 = $s1[$i];
                    $ch2 = mb_substr($s, $i, 1);

                    $r .= $ch1 == '?' ? $ch2 : $ch1;
                }
                return $r;
            }
        }

        $shipments = explode('|', $from_shipment);
        if ($shipments[0] == 'shipment') {
            unset($from_shipment);
            $from_shipment = 'shipment';
            unset($orders);
            $orders = explode(',', $shipments[1]);
        }


        $wonder = 'wonder';
        $this->_wonder = 'wonder';
        $minY = array();
        if ($invoice_or_pack == 'invoice'){
            $wonder = 'wonder_invoice';
            $this->_wonder = 'wonder_invoice';
        }

        $total_price_taxed = 0;
        $storeId = Mage::app()->getStore()->getId();

        //TODO @NAMDG check effect of transper config from value to object
        /*************************** BEGIN PDF GENERAL CONFIG *******************************/
        $this->setGeneralConfig($storeId);
        /*************************** END PDF GLOBAL PAGE CONFIG *******************************/

        /*************************** BEGIN PDF PACKING-SHEET/INVOICE PAGE CONFIG *******************************/
        $this->setPickPackInvoiceConfig($storeId);
        /*************************** END PDF PACKING-SHEET/INVOICE PAGE CONFIG *******************************/

        $sku_supplier_item_action = array();
        $sku_supplier_item_action_master = array();
        $config_first_run = true;
        $countstore = 0;
        if($wonder =='wonder')
            $supplier_key = 'pack';
        else
            $supplier_key = 'invoice';

        $split_supplier_yn_default = 0;
        $supplier_attribute_default = 'supplier';
        $supplier_options_default = 'filter';
        $supplier_login_default = '';
        $tickbox_default = 'no';
        $supplier_ubermaster = array();

        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false, 'general');
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general');
        $split_supplier_options = explode(',',$split_supplier_options_temp);
        $split_supplier_yn = 'no';
        if ($split_supplier_yn_temp == 1) {
            if(in_array($supplier_key,$split_supplier_options))
                $split_supplier_yn = 'pickpack';
            else
                $split_supplier_yn = 'no';
            
        }
        // this means only picklists should be separated
        if ($split_supplier_yn == 'pick') $split_supplier_yn = 'no';

        $supplier_attribute = $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false, 'general');
        $supplier_options = $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false, 'general');

        $userId = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
        $user = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
        $username = (!empty($user['username'])) ? $user['username'] : '';

        $supplier_login_pre = $this->_getConfig('pickpack_supplier_login', '', false, 'general', $storeId);
        $supplier_login_pre = str_replace(array("\n", ','), ';', $supplier_login_pre);
        $supplier_login_pre = explode(';', $supplier_login_pre);
        foreach ($supplier_login_pre as $key => $value) {
            $supplier_login_single = explode(':', $value);
            if (preg_match('~' . $username . '~i', $supplier_login_single[0])) {
                if (isset($supplier_login_single[1]) && $supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
                else $supplier_login = '';
            }
        }
        /*********** OPTIMIZE SUGGESTION 1: Re-use orderCollection ***********************/
        $number_pages =0;
        $start_page_for_order = 0;
        foreach ($orders as $orderSingle) {
            unset($minY);
            $minY = array();
            //Check shipment_ids or order_id here
            if ($shipments[0] == 'shipment') {
                $order = $helper->getOrderByShipment($orderSingle);
            } else {
                $order = $helper->getOrder($orderSingle);
            }
            $order_id = $order->getRealOrderId();
            $order_storeId = $order->getStore()->getId();
            $get_store = $order->getStore();
            $config_first_run = false;
            $storeId = $order_storeId;

            /***************************
             * CONFIGURATIONS
             ***********************************************************/

            //$page_size = $this->_getConfig('page_size', 'a4', false, $wonder, $order_storeId);
			$custom_round_yn = $this->_getConfig('custom_round_yn', 0, false, $wonder, $order_storeId);

            $company_address_x_nudge_default = '0';
            $return_logo_dimensionDefault = '100';

            /*************************** PAGE SIZE SETTING ***************************/
            if ($this->_packingsheet['page_size'] == 'letter') {
                $page_top = 770;
                $padded_right = 587;
                $padded_left = 20;

                $columnYNudgeDefault = 720;
                $barcodeXYDefault = '465,755';
                $title2XYDefault = '465,733';
                $orderDateXYDefault = '160,695';
                $orderIdXYDefault = '30,695';
                $customerIdXYDefault = '390,655';
                $addressXYDefault = '40,645';
                $addressFooterXYDefault = '50,110';
                $this->_addressFooterXYDefault = '50,110';
                $addressFooterXYDefault_xtra = '325,175';
                $returnAddressFooterXYDefault = '320,90';
                $customerNameXYDefault = '0,0';

                $giftMessageXYDefault = '0,90';
                $notesXYDefault = '25,90';
                $packedByXYDefault = '520,20';
                $supplierXYDefault = '465,755';
                $return_logo_XYDefault = '320,40';
                $return_logo2_XYDefault = '20,40';
            } 
            elseif ($this->_packingsheet['page_size'] == 'a4') {
                $page_top = 820;
                $padded_right = 570;
                $padded_left = 20;

                $columnYNudgeDefault = 720;
                $barcodeXYDefault = '465,805';
                $title2XYDefault = '465,783';
                $orderDateXYDefault = '160,745';
                $orderIdXYDefault = '30,745';
                $customerIdXYDefault = '390,705';
                $addressXYDefault = '40,695';
                $addressFooterXYDefault = '50,140';
                $this->_addressFooterXYDefault = '50,140';
                $addressFooterXYDefault_xtra = '325,205';
                $returnAddressFooterXYDefault = '320,120';

                $customerNameXYDefault = '0,0';

                $giftMessageXYDefault = '0,140';
                $notesXYDefault = '25,140';
                $packedByXYDefault = '520,20';
                $supplierXYDefault = '465,805';
                $return_logo_XYDefault = '320,70'; 
                $return_logo2_XYDefault = '20,70';
            } 
            elseif ($this->_packingsheet['page_size'] == 'a5-landscape') {
                //$page_top = 395;$padded_right = 573;
                /*
                    Letter        612x792 587x770 (-25 -22)
                    A4             595x842 570x820 (-25 -22)
                    A5             420x595 395x573
                    A5(L)        595x420 573x395
                    */
                $page_top = 395;
                $padded_right = 573;
                $padded_left = 20;

                $columnYNudgeDefault = 720;
                $barcodeXYDefault = '465,379';
                $title2XYDefault = '465,358';
                $orderDateXYDefault = '160,320';
                $orderIdXYDefault = '30,320';
                $customerIdXYDefault = '390,280';
                $addressXYDefault = '40,270';
                $addressFooterXYDefault = '50,100';
                $this->_addressFooterXYDefault = '50,100';
                $addressFooterXYDefault_xtra = '325,165';
                $returnAddressFooterXYDefault = '320,80';
                $customerNameXYDefault = '0,0';
                $giftMessageXYDefault = '0,80';
                $notesXYDefault = '25,80';
                $packedByXYDefault = '520,20';
                $supplierXYDefault = '465,379';
                $return_logo_XYDefault = '320,70'; //'320,140'; (minus the image size [180x120] from return address coords)
                $return_logo2_XYDefault = '20,70';
            } elseif ($this->_packingsheet['page_size'] == 'a5-portrait') {
                //$page_top = 395;$padded_right = 573;
                /*
                    Letter        612x792 587x770 (-25 -22)
                    A4            595x842 570x820 (-25 -22)
                    A5-portrait   420x595 _ 395x573 _
                    A5-landscape  595x420 573x395
                    */
                $page_top = 573;
                $padded_right = 395;
                $padded_left = 20;

                $columnYNudgeDefault = 520;
                $barcodeXYDefault = '325,505';
                $title2XYDefault = '325,545';
                $orderDateXYDefault = '80,445';
                $orderIdXYDefault = '30,445';
                $customerIdXYDefault = '390,405';
                $addressXYDefault = '40,595';
                $addressFooterXYDefault = '50,140';
                $this->_addressFooterXYDefault = '50,140';
                $addressFooterXYDefault_xtra = '305,205';
                $returnAddressFooterXYDefault = '300,120';
                $company_address_x_nudge_default = '-100';

                $customerNameXYDefault = '0,0';

                $giftMessageXYDefault = '0,100';
                $notesXYDefault = '25,100';
                $packedByXYDefault = '320,20';
                $supplierXYDefault = '325,505';
                $return_logo_XYDefault = '320,70';
                $return_logo2_XYDefault = '20,70';
            }

            /*************************** DEFAULT VALUE *******************************/
            $cutoff_noDefault = 20;
            $subheader_start = 0;
            $last_print_top = 0;
            $subheader_start_left = 0;
            $boldLastAddressLineYNDefault = 'Y';
            $fontColorGrey = 0.6;
            $fontColorReturnAddressFooter = 0.2;
            $boxBkgColorDefault = 0.7;
            $vertical_spacing = 12;

            $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
            $grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.7);
            $dk_grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.3); //darkCyan
            $dk_cyan_bkg_color = new Zend_Pdf_Color_Html('darkCyan'); //darkOliveGreen
            $dk_og_bkg_color = new Zend_Pdf_Color_Html('darkOliveGreen'); //darkOliveGreen
            $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
            $red_color = new Zend_Pdf_Color_Html('darkRed');
            $grey_color = new Zend_Pdf_Color_GrayScale(0.3);
            $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
            $white_color = new Zend_Pdf_Color_GrayScale(1);
            $grayout_color = "#888888";
            /*************************** END DEFAULT VALUE *******************************/

            $wsa_pickup_location_model_default = 'ncr_location/location';
            $split_supplier_yn_default = 0;
            $supplier_attribute_default = 'supplier';
            $supplier_options_default = 'filter';
            $supplier_login_default = '';
            $tickbox_default = 'no';
            $order_id_master = array();
            $magik_product_str = array();

            $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false, 'general', $order_storeId);
            $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general', $order_storeId);
            $split_supplier_options = explode(',',$split_supplier_options_temp);
            
            $split_supplier_yn = 'no';
            
                

            if ($split_supplier_yn_temp == 1) {
                if(in_array($supplier_key,$split_supplier_options))
                    $split_supplier_yn = 'pickpack';
                else
                    $split_supplier_yn = 'no';
            }
            // this means only picklists should be separated
            
            if ($split_supplier_yn != 'no') {
                $supplier_attribute = $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false, 'general', $order_storeId);
                $supplier_options = $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false, 'general', $order_storeId);
            }
            $item_count = 0;
            $total_item_count = 0;
            $itemsCollection = $order->getAllVisibleItems();
            $suppiler_all = $this->getAllSupplier($order, $supplier_attribute);
            $is_warehouse_supplier = 0;
            if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
            {
                if($supplier_attribute == 'warehouse')
                {
                    $is_warehouse_supplier = 1;
                }
            }
            
            foreach ($itemsCollection as $item) {
                $total_item_count += $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                $product = $this->_getProductFromItem($item);
                $sku = $product->getSku();
                $product_id = $product->getId();

                $sku_productid[$sku] = $product_id;

                $shelving_real_attribute = $this->_getConfig('shelving_real', 'shelf', false, $wonder, $order_storeId);
                $shelving_attribute = $this->_getConfig('shelving', '', false, $wonder, $order_storeId);
				
                $supplier = '';
                $sku_supplier_item_action_master[$order_id] = 'keep';
                $supplier_order_ids[$order_id] = array();
                $loop_supplier = 0;
                if ($split_supplier_yn == 'pickpack') {
                    if($is_warehouse_supplier == 1)
                    {
                        $warehouse_title = $item->getWarehouseTitle();
                        $warehouse = $item->getWarehouse();
                        $warehouse_code = $warehouse->getData('code');
                        $supplier = $warehouse_code;
                    }
                    else
                        $supplier = $this->getProductAttributeValue($product, $supplier_attribute);
                    if (is_array($supplier)) $supplier = implode(',', $supplier);
                    if (!$supplier) $supplier = '~Not Set~';
                    $supplier = trim(strtoupper($supplier));
                    $supplier_order_ids[$order_id] = $suppiler_all;
                    if (isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ',' . $supplier;
                    else $sku_supplier[$sku] = $supplier;
                    $sku_supplier[$sku] = preg_replace('~,$~', '', $sku_supplier[$sku]);

                    if (!isset($supplier_master[$supplier])) {
                        $supplier_master[$supplier] = $supplier;
                        if (array_search($supplier, $supplier_ubermaster) === false)
                            $supplier_ubermaster[] = $supplier;
                    }

                    if (isset($order_id_master[$order_id])) $order_id_master[$order_id] .= ',' . $supplier;
                    else $order_id_master[$order_id] = $supplier;

                    $sku_supplier_item_action[$supplier][$sku] = 'keep';
                    // if set to filter and a name and this is the name, then print
                    foreach ($suppiler_all as $supplier) {
                        if ($supplier_options == 'filter' && isset($supplier_login) && ($sku_supplier[$sku] == strtoupper($supplier_login)) && ($sku_supplier[$sku] == strtoupper($supplier))) //grey //split
                        {
                            $sku_supplier_item_action[$supplier][$sku] = 'keep';
                        } elseif ($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && ($sku_supplier[$sku] != strtoupper($supplier_login))) //grey //split
                        {
                            $sku_supplier_item_action[$supplier][$sku] = 'hide';
                        } elseif ($supplier_options == 'grey' && isset($supplier_login) && ($sku_supplier[$sku] == strtoupper($supplier_login))) //grey //split
                        {
                            $sku_supplier_item_action[$supplier][$sku] = 'keep';
                        } elseif ($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && ($sku_supplier[$sku] != strtoupper($supplier_login))) //grey //split
                        {
                            $sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                        } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && ($sku_supplier[$sku] != strtoupper($supplier))) {
                            $sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                        } elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && ($sku_supplier[$sku] != strtoupper($supplier))) {
                            $sku_supplier_item_action[$supplier][$sku] = 'hide';
                            if(strpos($sku_supplier[$sku], ','))
                            {
                                $temp_arr = explode(',',$sku_supplier[$sku]);
                                if (in_array(strtoupper($supplier), $temp_arr)) {
                                    $sku_supplier_item_action[$supplier][$sku] = 'keep';
                                }
                                unset($temp_arr);
                            }   
                        } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && ($sku_supplier[$sku] == strtoupper($supplier))) {
                            $sku_supplier_item_action[$supplier][$sku] = 'keep';
                        } elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && ($sku_supplier[$sku] == strtoupper($supplier))) {
                            $sku_supplier_item_action[$supplier][$sku] = 'keep';
                        } elseif ($supplier_options == 'grey') {
                            $sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                        } elseif ($supplier_options == 'filter') {
                            $sku_supplier_item_action[$supplier][$sku] = 'hide';
                        }
                        if ($split_supplier_yn == 'no') $sku_supplier_item_action[$supplier][$sku] = 'keep';

                        if (($sku_supplier_item_action_master[$order_id] != 'keep') && ($sku_supplier_item_action_master[$order_id] != 'keepGrey') && (($sku_supplier_item_action[$supplier][$sku] == 'keepGrey') || ($sku_supplier_item_action[$supplier][$sku] == 'keep')))
                            $sku_supplier_item_action_master[$order_id] = 'keep';
                        $loop_supplier = 1;
                    }
                }

            }
        }
        if (($split_supplier_yn != 'no') || ($split_supplier_yn == 'no')) {
            $this->_beforeGetPdf();
            $this->_initRenderer('invoices');
            $pdf = new Zend_Pdf();
            $this->_setPdf($pdf);
            $style = new Zend_Pdf_Style();
            if ($this->_packingsheet['page_size'] == 'letter') {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
                $page_top = 770;
                $padded_right = 587;
            } elseif ($this->_packingsheet['page_size'] == 'a4') {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $page_top = 820;
                $padded_right = 570;
            } elseif ($this->_packingsheet['page_size'] == 'a5-landscape') {
                $page = $pdf->newPage('596:421');
                $page_top = 395;
                $padded_right = 573;
            } elseif ($this->_packingsheet['page_size'] == 'a5-portrait') {
                $page = $pdf->newPage('421:596');
                $page_top = 573;
                $padded_right = 395;
            }

            $pdf->pages[] = $page;
            $this->y = $page_top;
            $number_pages ++;
        }

        $s = 0;
        $first_page_yn = 'y';
        $page_count = 1;
        $shelving_attribute = '';
        $subtotal_order = array();
        $filter_items_by_status = $this->_getConfig('filter_items_by_status', 0, false, $wonder, $storeId);
        $new_pdf_per_name_yn = $this->_getConfig('new_pdf_per_name_yn', 0, false, $wonder, $storeId);
        do {
            if ($split_supplier_yn != 'no' && $supplier_ubermaster[$s] != "") $supplier = $supplier_ubermaster[$s];
            /*************************** USING OPTIMIZATION 1: orderCollection ****************************/
            $supplier_lower = strtolower($supplier);

            foreach ($orders as $orderSingle) {
                if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
                    $notice_obj = Mage::getModel('sarp/notice')->load($orderSingle, 'order_id');
                    $notice = nl2br($notice_obj->getNotice());
                }

                //Check shipment_ids or order_id here
                if ($shipments[0] == 'shipment') {
                    $shipment_model = Mage::getModel('sales/order_shipment')->load($orderSingle);
                    $order = $helper->getOrder($shipment_model->getOrderId());
                    $shiped_items = $shipment_model->getItemsCollection();
                    $shiped_items_qty = array();
                    foreach ($shiped_items as $shiped_item) {
                        $shiped_items_qty[$shiped_item->getData('product_id')] = $shiped_item->getData('qty');
                    }
                } else {
                    $order = $helper->getOrder($orderSingle);
                }
                $order_id = $order->getRealOrderId();
                $order_storeId = $order->getStore()->getId();
                //TODO maybe we should replace $store_id by $order_storeId.
                $store_id = $order->getStore()->getId();
                $date_format = $this->_getConfig('date_format', 'M. j, Y', false, 'general', $store_id);
                $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $date_format);
                $itemsCollection = $order->getAllVisibleItems();
                $gift_card_array = array();
                $count_item = 0;
                if($new_pdf_per_name_yn == 1)
                    $count_item = count($itemsCollection);
                $sku_array = $this->getSkuArr($itemsCollection);
                /**
                 * config inputs
                 */
                //$second_page_start = $this->_getConfig('second_page_start', 'top', false, 'general', $store_id); // top or asfirst
                $bundle_children_yn = $this->_getConfig('bundle_children_yn', 1, false, $wonder, $store_id);
                if($bundle_children_yn == 1)
                    $shift_bundle_children_xpos = $this->_getConfig('shift_bundle_children_xpos', 0, false, $wonder, $store_id);
                $numbered_product_list_yn = $this->_getConfig('numbered_product_list_yn', 0, false, $wonder, $order_storeId);
                $numbered_product_list_X = $this->_getConfig('numbered_product_list_X', 17, false, $wonder, $order_storeId);
                $numbered_product_list_bundle_children_yn = $this->_getConfig('numbered_product_list_bundle_children_yn', 0, false, $wonder, $order_storeId);
                $numbered_product_list_bundle_children_X = $this->_getConfig('numbered_product_list_bundle_children_X', 21, false, $wonder, $order_storeId);
                $fill_bars_options = $this->_getConfig('fill_bars_subtitles', 1, false, 'general', $store_id);
                if ($fill_bars_options == 1) {
                    $fillbar_padding = explode(",", $this->_getConfig('fillbar_padding', '0,0', false, 'general', $store_id));
                    $line_widths = explode(",", $this->_getConfig('bottom_line_width', '1,2', false, 'general', $store_id));

                }
                $numbered_list_suffix = '.';
                if (($numbered_product_list_yn == 0) || ($bundle_children_yn == 0)) {
                    $numbered_product_list_bundle_children_yn = 0;
                }
                $trim_names_yn = $this->_getConfig('trim_names_yn', 1, false, 'general', $store_id);
                $prices_yn = $this->_getConfig('prices_yn', 0, false, $wonder, $store_id);
                if($prices_yn == 1 ){
                    $multi_prices_yn = $this->_getConfig('multi_prices_yn', 0, false, $wonder, $store_id);
                    if($multi_prices_yn == 1)
                        $multiplier_attribute = $this->_getConfig('multiplier_attribute', '', false, $wonder, $store_id);
                }
                $total_paid_yn_subtotal = $this->_getConfig('total_paid_yn_subtotal', 0, false, $wonder, $store_id);
                $total_due_yn_subtotal = $this->_getConfig('total_due_yn_subtotal', 0, false, $wonder, $store_id);
                $prices_hideforgift_yn = $this->_getConfig('prices_hideforgift_yn', 0, false, $wonder, $store_id);
                $list_total_by_tax_class = $this->_getConfig('list_total_by_tax_class', 0, false, $wonder, $store_id);
                $tax_yn = $this->_getConfig('tax_yn', 'no', false, $wonder, $store_id);
                $tax_col_yn = 0;
                $tax_col_method = '';
                $tax_bands_yn = 0;
                $tax_displayed_in_shipping_yn = 0;
                $remove_shipping_tax_from_subtotal_yn = 0;
                $remove_shipping_tax_from_tax_subtotal_yn = 0;
                $address_pad = array();

                /*************************** DESCRIPTION 1 *******************************
                 * tax_yn :
                 * 'no'       ('No, include it in the product price')),
                 * single_price = with tax
                 * row_price  = with tax
                 * subtotal   = with tax
                 * tax_subtotal = n/a
                 * shipping_subtotal = with tax
                 * 'noboth'   ('No, include it in the product price, and also show a tax subtotal')),
                 * single_price = with tax
                 * row_price  = with tax
                 * subtotal   = with (product) tax
                 * shipping_subtotal:
                 * 'tax_displayed_in_shipping_yn' = 0
                 * =>     shipping_subtotal   = without tax
                 * =>     tax_subtotal        = all tax, including shipping tax
                 * 'tax_displayed_in_shipping_yn' = 1
                 * =>     shipping_subtotal   = with tax
                 * =>     tax_subtotal        = all tax, not including shipping tax
                 * 'yescol'   ('Yes, in a Tax column')),
                 * single_price = without tax
                 * row_price  = with tax
                 * tax_col        = total qty_x_tax
                 * subtotal   = with (product) tax
                 * tax_subtotal = n/a
                 * shipping_subtotal = with tax
                 * 'yessubtotal'('Yes, in a Tax subtotal')),
                 * ???? (same as 'noboth'?)
                 * 'yesboth'  ('Yes, both in a Tax column and a Tax subtotal'))
                 * single_price = without tax
                 * row_price  = with tax
                 * tax_col        = total qty_x_tax
                 * subtotal   = without (product) tax
                 * shipping_subtotal:
                 * 'tax_displayed_in_shipping_yn' = 0
                 * =>     shipping_subtotal   = without tax
                 * =>     tax_subtotal        = all tax, including shipping tax
                 * 'tax_displayed_in_shipping_yn' = 1
                 * =>     shipping_subtotal   = with tax
                 * =>     tax_subtotal        = all tax, not including shipping tax
                 *************************** END DESCRIPTION 1 *******************************/

                /*************************** PDF PAGE CONFIG *******************************/
                if ($tax_yn == 'yesboth' || $tax_yn == 'yescol') {
                    $tax_col_yn = 1;
                    if ($tax_yn == 'yescol') $tax_col_method = $this->_getConfig('tax_method_yescol', 'b', false, $wonder, $store_id);
                    elseif ($tax_yn == 'yesboth') $tax_col_method = $this->_getConfig('tax_method_yesboth', 'b', false, $wonder, $store_id);
                }
                if ($prices_yn == 0) {
                    $tax_yn = 'no';
                    $total_paid_yn_subtotal = 0;
                    $total_due_yn_subtotal = 0;
                }
                if ($tax_yn == 'no') {
                    $tax_col_yn = 0;

                    $tax_col_method = '';
                    $subtotal_order = explode(',', $this->_getConfig('subtotal_order_no', '10,20,40', false, $wonder, $store_id));
                    /*
                     subtotal_order[0]Subtotal
                     [1]Discounts
                     [2]Tax
                     [3]Shipping
                     */
                    $subtotal_order[3] = 0;
                    if (isset($subtotal_order[2])) $subtotal_order[3] = $subtotal_order[2];
                    $subtotal_order[2] = 0;
                    $tax_displayed_in_shipping_yn = $this->_getConfig('tax_displayed_in_shipping_yn_no', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_subtotal_yn_no', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_tax_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_tax_subtotal_yn_no', 0, false, $wonder, $store_id);
                }
                if ($tax_yn == 'yessubtotal') {
                    $tax_bands_yn = $this->_getConfig('tax_bands_yn_subtotal', 1, false, $wonder, $store_id);
                    $tax_displayed_in_shipping_yn = $this->_getConfig('tax_displayed_in_shipping_yn_subtotal', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_subtotal_yn_subtotal', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_tax_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_tax_subtotal_yn_subtotal', 0, false, $wonder, $store_id);

                    $show_bracket_tax = $this->_getConfig('show_bracket_tax', 0, false, $wonder, $store_id);

                    $subtotal_order = explode(',', $this->_getConfig('subtotal_order_yessubtotal', '10,20,30,40', false, $wonder, $store_id));
                } elseif ($tax_yn == 'yesboth') {
                    $tax_bands_yn = $this->_getConfig('tax_bands_yn_subtotal', 1, false, $wonder, $store_id);
                    $tax_displayed_in_shipping_yn = $this->_getConfig('tax_displayed_in_shipping_yn', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_subtotal_yn', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_tax_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_tax_subtotal_yn', 0, false, $wonder, $store_id);
                    $show_bracket_tax = $this->_getConfig('show_bracket_tax', 0, false, $wonder, $store_id);

                    $subtotal_order = explode(',', $this->_getConfig('subtotal_order_yesboth', '10,20,30,40', false, $wonder, $store_id));
                } elseif ($tax_yn == 'noboth') {
                    $tax_bands_yn = $this->_getConfig('tax_bands_yn_subtotal', 1, false, $wonder, $store_id);
                    $subtotal_order = explode(',', $this->_getConfig('subtotal_order_noboth', '10,20,30,40', false, $wonder, $store_id));
                    $tax_displayed_in_shipping_yn = $this->_getConfig('tax_displayed_in_shipping_yn_noboth', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_subtotal_yn_noboth', 0, false, $wonder, $store_id);
                    $remove_shipping_tax_from_tax_subtotal_yn = $this->_getConfig('remove_shipping_tax_from_tax_subtotal_yn_noboth', 0, false, $wonder, $store_id);
                    $show_bracket_tax = $this->_getConfig('show_bracket_tax', 0, false, $wonder, $store_id);
                } elseif ($tax_yn == 'yescol') {
                    $subtotal_order = explode(',', $this->_getConfig('subtotal_order_yescol', '10,20,40', false, $wonder, $store_id));
                    $subtotal_order[3] = $subtotal_order[2];
                    $subtotal_order[2] = 0;
                }
                $fix_subtotal = $this->_getConfig('fix_subtotal_page', 0, false, $wonder, $store_id);
                $tax_label = trim($this->_getConfig('tax_label', '', false, $wonder, $store_id));
                $discount_line_or_subtotal = $this->_getConfig('discount_line_or_subtotal', '', false, $wonder, $store_id);
                $date_format = $this->_getConfig('date_format', 'M. j, Y', false, 'general', $store_id);
                $doubleline_yn = $this->_getConfig('doubleline_yn', 1, false, $wonder, $store_id);
                $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $store_id);
                $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $store_id));
                if ($shelving_real_attribute == '') $shelving_real_yn = 0;
                $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $store_id));
                $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);
                $sku_title = $this->_getConfig('sku_title', '', false, $wonder, $store_id);
                $sku_barcode_title = $this->_getConfig('sku_barcode_title', '', false, $wonder, $store_id);
                $sku_barcode_2_title = $this->_getConfig('sku_barcode_2_title', '', false, $wonder, $store_id);
                $product_stock_qty_title = $this->_getConfig('product_stock_qty_title', '', false, $wonder, $store_id);
                $items_title = $this->_getConfig('items_title', '', false, $wonder, $store_id);
                $order_or_invoice = $this->_getConfig('orderorinvoice', 'order', false, $wonder, $store_id);
                $order_or_invoice_date = $this->_getConfig('orderorinvoicedate', 'order', false, $wonder, $store_id);
                $beta_boxes_yn = $this->_getConfig('beta_boxes_yn', 0, false, $wonder, $store_id);
                if ($beta_boxes_yn == 0) {
                    $beta_box_1_yn = 0;
                    $beta_box_2_yn = 0;
                    $beta_box_3_yn = 0;
                }
                //TODO Moo Image turn off
                $product_images_yn = $this->_getConfig('product_images_yn', 0, false, $wonder, $store_id);
                //$product_images_yn = 0;
                if ($product_images_yn == 1) {
                    $image_y_nudge = $this->_getConfig('product_images_y_nudge', 0, false, $wonder, $store_id);
                }
                $product_images_source = $this->_getConfig('product_images_source', 'thumbnail', false, $wonder, $store_id);
                $product_images_parent_yn = $this->_getConfig('parent_image_yn', 0, false, $wonder, $store_id);
                $product_sku_yn = $this->_getConfig('product_sku_yn', 1, false, $wonder, $store_id);
                $product_sku_simple_configurable = 'simple';
                if ($product_sku_yn == 'configurable') {
                    $product_sku_yn = 1;
                    $product_sku_simple_configurable = 'configurable';
                }
                if ($product_sku_yn == 'fullsku') {
                    $product_sku_yn = 1;
                    $product_full_sku = 'fullsku';
                }

                $show_allowance_yn = $this->_getConfig('show_allowance_yn', 0, false, $wonder, $store_id);
                $show_allowance_multiple = 1;
                if($show_allowance_yn == 1){
                    $show_allowance_multiple = $this->_getConfig('show_allowance_multiple', '1', false, $wonder, $store_id);
                    $show_allowance_title = $this->_getConfig('show_allowance_title', 'Allowance', false, $wonder, $store_id);
                    $show_allowance_xpos = $this->_getConfig('show_allowance_xpos', '500', false, $wonder, $store_id);
                }

                $product_sku_barcode_yn =  $this->_getConfig('product_sku_barcode_yn', 0, false, $wonder, $store_id);
                $product_sku_barcode_2_yn = $this->_getConfig('product_sku_barcode_2_yn', 0, false, $wonder, $store_id);
                $product_stock_qty_yn = $this->_getConfig('product_stock_qty_yn', 0, false, $wonder, $store_id);
                $trim_invoice_title = false;
                $invoice_title = $this->_getConfig('pickpack_title_pattern', 0, false, $wonder, $store_id, $trim_invoice_title);
                $page_title_nuge = explode(',', trim($this->_getConfig('title_pattern_nudge', '0,0', false, $wonder, $store_id)));
                $invoice_title_2_yn = $this->_getConfig('pickpack_title_2_yn', 0, false, $wonder, $store_id);
                $show_top_logo_yn = $this->_getConfig('pickpack_packlogo', 0, false, $wonder, $store_id);
                $logo_position = $this->_getConfig('pickpack_logo_position', 'left', false, $wonder, $store_id);
                $show_shipping_logo_yn = 0; //$this->_getConfig('pickpack_returnlogo_shipping', '', false, $wonder, $store_id);
                $shipping_logo_XY = explode(",", $this->_getConfig('pickpack_nudgelogo_shipping', '40,50', true, $wonder, $store_id));
                $address_pad = explode(",", $this->_getConfig('address_pad', '0,0,0', true, $wonder, $store_id));
                $address_pad_billing = explode(",", $this->_getConfig('address_pad_billing', '0,0,0', true, $wonder, $store_id));
                $address_pad[0] = ($address_pad[0] * -1);
                $address_pad[1] = ($address_pad[1] * -1);                
                $shipping_detail_pad = explode(",", $this->_getConfig('shipping_detail_pad', '0,0', false, $wonder, $store_id));
                $shipping_detail_pad[0] = ($shipping_detail_pad[0] * -1);
                $page_template = $this->_getConfig('page_template', 0, false, $wonder, $store_id);
                $shipping_billing_title_position = $this->_getConfig('shipping_billing_title_position', 'above', false, $wonder, $store_id);
                $title_date_xpos = trim($this->_getConfig('title_date_xpos', 'auto', false, $wonder, $store_id));
                $custom_message_image_line_yn = 0; //$this->_getConfig('custom_message_image_line_yn', 0, false,$wonder,$store_id);
                $page_pad = explode(',', trim($this->_getConfig('page_pad', '0,0,0', false, $wonder, $store_id)));

                $fill_product_header_yn = 1; //$this->_getConfig('fill_product_header_yn', 1,false,$wonder,$store_id);
                $title_invert_color = $this->_getConfig('title_invert_color', 0, false, $wonder, $store_id);
                $mailer_padding = array(0, 0, 0);
                if ($page_template == 'bringup') {
                    $title_invert_color = $this->_getConfig('title_invert_color_bringup', 1, false, $wonder, $store_id);
                    $page_pad = explode(',', trim($this->_getConfig('page_pad_bringup', '0,0', false, $wonder, $store_id)));
                    $fill_product_header_yn = $this->_getConfig('fill_product_header_yn_bringup', 0, false, $wonder, $store_id);
                    $logo_position = 'right';
                    $mailer_padding = explode(',', trim($this->_getConfig('mailer_padding_bringup', '0,0,0', false, $wonder, $store_id)));
                } elseif ($page_template == 'mailer') {
                    $title_invert_color = $this->_getConfig('title_invert_color_mailer', 1, false, $wonder, $store_id);
                    $page_pad = explode(',', trim($this->_getConfig('page_pad_mailer', '0,0', false, $wonder, $store_id)));
                    $fill_product_header_yn = $this->_getConfig('fill_product_header_yn_mailer', 0, false, $wonder, $store_id);
                    $mailer_padding = explode(',', trim($this->_getConfig('mailer_padding', '0,0,0', false, $wonder, $store_id)));
                } else {
                    $custom_message_image_line_yn = 0;
                }

                $page_pad_leftright = $page_pad[0];
                $page_pad_topbottom = $page_pad[1];

                if ($this->_packingsheet['page_size'] == 'letter') {
                    // 792 x 612
                    $full_page_width = 612;
                    $page_top = (770 - $page_pad_topbottom);
                    $page_bottom = $page_pad[2];
                    $padded_right = (594 - $page_pad_leftright);
                    $padded_left = (20 + $page_pad_leftright); //562 usable
                } elseif ($this->_packingsheet['page_size'] == 'a4') {
                    // 595 pt x 842 pt
                    $full_page_width = 595;
                    $page_top = (820 - $page_pad_topbottom);
                    $padded_right = (577 - $page_pad_leftright);
                    $padded_left = (20 + $page_pad_leftright);
                    $page_bottom = $page_pad[2];
                } elseif ($this->_packingsheet['page_size'] == 'a5-landscape') {
                    // 420 pt x 595 pt (flipped)
                    $full_page_width = 577;
                    $page_bottom = $page_pad[2];
                    $page_top = (395 - $page_pad_topbottom);
                    $padded_right = (577 - $page_pad_leftright);
                    $padded_left = (20 + $page_pad_leftright);
                } elseif ($this->_packingsheet['page_size'] == 'a5-portrait') {
                    // 595 pt x 420 pt (flipped)
                    $full_page_width = 395;
                    $page_bottom = $page_pad[2];
                    $page_top = (577 - $page_pad_topbottom);
                    $padded_right = (395 - $page_pad_leftright);
                    $padded_left = (20 + $page_pad_leftright);
                }

                $invoice_title_2 = trim($this->_getConfig('pickpack_title_2', '', false, $wonder, $store_id));
                if ($invoice_title_2_yn == 0) $invoice_title_2 = '';
                $title2XY = explode(",", $this->_getConfig('pickpack_nudge_title', $title2XYDefault, true, $wonder, $store_id));
                $this->_general['non_standard_characters'] = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);
                $first_item_title_shift = 0;
                if ($prices_yn != '0') {
                    $first_item_title_shift = -13;
                }
                $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $store_id);
                $billing_address_with_gift_yn = $this->_getConfig('billing_address_with_gift_yn', 0, false, $wonder, $store_id);
                $billing_details_yn = $this->_getConfig('billing_details_yn', 0, false, $wonder, $store_id);
				
                if ($gift_message_yn != 'no' || $prices_hideforgift_yn == 1 || $billing_address_with_gift_yn == 1) {
                    // This check CE for gift messages
					$gift_message_item = Mage::getModel('giftmessage/message');
                    $gift_message_id = $order->getGiftMessageId();

					// Check EE for Gift receipt ZZ
                    $is_gift_receipt = "0";
                    if($this->isMageEnterprise() === true) {
                        $is_gift_receipt = $order->getData('gw_allow_gift_receipt');
                    }

                    if (!is_null($gift_message_id) || ($is_gift_receipt == "1")) {
                        // hide prices if set to hide prices on gift order
                        if ($prices_hideforgift_yn == 1) $prices_yn = 0;
						// hide billing address with gift orders (if set)
                        if ($billing_address_with_gift_yn == 1) $billing_details_yn = 0;
                    }
                }
                $giftwrap_yn = $this->_getConfig('gift_wrap_yn', 'no', false, $wonder, $store_id);
                $giftwrap_style_yn = $this->_getConfig('gift_wrap_style_yn', 'yesshipping', false, $wonder, $store_id);

                /*************************** GIFTWRAP MESSAGE*******************************/
                $giftWrap_info = array();
                $giftWrap_info['wrapping_paper'] = NULL;
                $giftWrap_info['message'] = NULL;

                if (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') || Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                    if (Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                        $quoteId = $order->getQuoteId();
                        $selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
                        $giftwrapCollection = array();
                        if ($quoteId) {
                            $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
                            foreach ($giftwrapCollection as $info_collection) {
                                $giftWrap_info['message'] .= "\n" . $info_collection['giftwrap_message'];
                                $style_gift = Mage::getModel('giftwrap/giftwrap')->load($info_collection['styleId']);
                                if ($giftwrap_style_yn == 'yesbox') {
                                    $giftWrap_info['wrapping_paper'] .= $style_gift->getData('title');
                                } else
                                    if ($giftwrap_style_yn == 'yesshipping') {
                                        $giftWrap_info['style'] .= $style_gift->getData('title');
                                    }
                            }
                        }


                        $giftWrapInfos = Mage::getModel('giftwrap/giftwrap')
                            ->getCollection()
                            ->addFieldToFilter('store_id', '0');

                        foreach ($giftWrapInfos as $info) {
                            $giftWrap_info['message'] .= $info->getData('message');
                            $giftWrap_info['wrapping_paper'] .= str_ireplace(array('.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('image'));
                        }


                        /*
                         [giftcard_id] => 1
                         [status] => 1
                         [name] => Test Gift Card
                         [image] => Test gift image.png
                         [price] => 1.5000
                         [store_id] => 0
                         [message] =>
                         [character] => 200
                         [option_id] => 1
                         [default_name] => 1
                         [default_price] => 1
                         [default_image] => 1
                         [default_sort_order] => 1
                         [default_message] => 1
                         [default_status] => 1
                         [default_character] => 1
                         */
                    } elseif (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') && (Mage::getModel('giftwrap/order'))) {
                        /*
                         ["entity_id"]=>"2"
                         ["order_id"]=>"181"
                         ["message"]=>"happy birthday"
                         ["items"]=>"961"
                         ["fee"]=>"0"
                         ["giftbox_image"]=>"xmage_giftwrap/Screen shot 2011-09-06 at 2.33.42 PM.png"
                         ["giftcard_image"]=>"xmage_giftwrap/gift_card/giftwrap2.jpg"
                         ["giftcard_html"]=>"<div style="position: relative;"><p>Test content</p><div id="gift-textbox" class="drsElement drsMoveHandle" style="visibility: visible;position: absolute;width:100px; height:100px; top:10px; left:10px;"><div id="gift-content">happy birthday</div></div></div>"
                         */
                        $orderId = $order->getId();
                        $giftWrapInfos = Mage::getModel('giftwrap/order')->getCollection()->addFieldToFilter('order_id', $orderId);
                        foreach ($giftWrapInfos as $info) {
                            $giftWrap_info['message'] .= $info->getData('message');
                            if (isset($giftWrap_info['wrapping_paper'])) $giftWrap_info['wrapping_paper'] .= ' | ';
                            $giftWrap_info['wrapping_paper'] .= trim(str_ireplace(array('xmage_giftwrap/', '.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('giftbox_image')));
                        }
                    }

                    unset($giftWrapInfos);
                    if (trim($giftWrap_info['wrapping_paper']) != '' && $prices_hideforgift_yn == 1) $prices_yn = 0;
                }
                $giftMessageXY = explode(",", $this->_getConfig('gift_message_nudge', $giftMessageXYDefault, true, $wonder, $store_id));
                $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $store_id);
                $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $store_id);
                $notes_title = $this->_getConfig('notes_title', '', false, $wonder, $store_id);
                $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $store_id);
                if ($notes_yn == 0) $notes_position = 'no';
                $notes_filter_options = $this->_getConfig('notes_filter_options', '', false, $wonder, $store_id);
                $notes_filter = trim(strtolower($this->_getConfig('notes_filter', '', false, $wonder, $store_id)));
                // replace single or double quotes and ensure that they must match:
                $notes_filter = preg_replace('/^([\'"])(.*)\\1$/', '\\2', $notes_filter);
                if ($notes_filter_options != 'yestext') $notes_filter = '';
                $notesXY = explode(",", $this->_getConfig('notes_nudge', $notesXYDefault, true, $wonder, $store_id));
                $order_gift_message_yn = $this->_getConfig('order_gift_message_yn', 'no', false, $wonder, $store_id);
                $product_gift_message_yn = $this->_getConfig('product_gift_message_yn', 'no', false, $wonder, $store_id);
                $message_title_tofrom_yn = $this->_getConfig('message_title_tofrom_yn', 'yes', false, $wonder, $store_id);
                $check_comments_for_gift_message_yn = $this->_getConfig('check_comments_for_gift_message_yn', 'no', false, $wonder, $store_id);
                $positional_message_box_fixed_position = explode(",", $this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $store_id));

                $repeat_gift_message_yn = $this->_getConfig('repeat_gift_message_yn', 'no', false, $wonder, $store_id);
                if($repeat_gift_message_yn == 1)
                    $positional_remessage_box_fixed_position = explode(",", $this->_getConfig('positional_remessage_box_fixed_position', '20,200', false, $wonder, $store_id));

                $gift_message_array = array();
                $gift_message_array_pos = array();
                $packedByXY = array(0,0);
                $packed_by_text = '';
                $packed_by_yn = $this->_getConfig('packed_by_yn', 0, false, $wonder, $store_id);
                if ($packed_by_yn == 1) {
                    $packed_by_text = trim($this->_getConfig('packed_by_text', '', false, $wonder, $store_id));
                    $packedByXY = explode(",", $this->_getConfig('packed_by_nudge', $packedByXYDefault, true, $wonder, $store_id));
                    $minY[] = $packedByXY[1] + 10;
                }
                
                if($split_supplier_yn == 'no')
                    $supplier_attribute_yn = 0;
                else
                $supplier_attribute_yn = $this->_getConfig('supplier_attribute_show_option', 0, false, "general", $store_id);
                if ($supplier_attribute_yn == 1) {
                    $supplier_attributeXY = explode(",", $this->_getConfig('supplier_attribute_xpos', $supplierXYDefault, true, "general", $store_id));
                    $font_size_supplier_attribute = $this->_getConfig('supplier_font_size_options', 22, false, "general", $store_id);
                }
                $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 0, false, $wonder, $store_id);
                $product_qty_underlined = 0;
                $product_qty_red = 0;
                $product_qty_rectangle = 0;

                if ($product_qty_upsize_yn == 'u' || $product_qty_upsize_yn == 'c' || $product_qty_upsize_yn == 'b') {
                    if ($product_qty_upsize_yn == 'c') $product_qty_red = 1;
                    if ($product_qty_upsize_yn == 'b') $product_qty_rectangle = 1;
                    $product_qty_upsize_yn = 1;
                    $product_qty_underlined = 1;
                }
                $boldLastAddressLineYN = $boldLastAddressLineYNDefault;
                $font_size_returnaddresstop = 8;
                $font_size_titles = 14;
                $boxBkgColor = $boxBkgColorDefault;
                $font_style_shipping_billing_title = $this->_getConfig('font_style_shipping_billing_title', 'bold', false, 'general', $store_id);
                $font_family_header = $this->_getConfig('font_family_header', 'helvetica', false, 'general', $store_id);
                $font_style_header = $this->_getConfig('font_style_header', 'regular', false, 'general', $store_id);
                $font_size_header = $this->_getConfig('font_size_header', 16, false, 'general', $store_id);
                $font_color_header = trim($this->_getConfig('font_color_header', 'darkOliveGreen', false, 'general', $store_id));
                //$font_family_subtitles = $this->_getConfig('font_family_subtitles', 'helvetica', false, 'general', $store_id);
                //$font_style_subtitles = $this->_getConfig('font_style_subtitles', 'regular', false, 'general', $store_id);
                //$font_size_subtitles = $this->_getConfig('font_size_subtitles', 15, false, 'general', $store_id);
                //$font_color_subtitles = trim($this->_getConfig('font_color_subtitles', '#222222', false, 'general', $store_id));
                $background_color_subtitles = trim($this->_getConfig('background_color_subtitles', '#5BA638', false, 'general', $store_id));
                $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);
                if ($this->_general['font_family_subtitles'] == 'custom') {
                    $font_filename = $this->_getConfig('font_custom_subtitles', '', false, 'general', $store_id);
                    $sub_folder = 'custom_font';
                    $option_group = 'general';
                    if ($font_filename) {
                        $font_path = Mage::getStoreConfig('system/filesystem/media', $store_id) . '/moogento/pickpack/' . $sub_folder . '/' . $font_filename;
                        if (is_file($font_path)) {
                            // gonna pass the font file path through the style attribute
                            $this->_general['font_style_subtitles'] = $font_path;
                        } else $this->_general['font_family_subtitles'] = 'helvetica';
                    }
                } else $font_custom_subtitles = '';
                $font_family_company = $this->_getConfig('font_family_company', 'helvetica', false, 'general', $store_id);
                $font_style_company = $this->_getConfig('font_style_company', 'regular', false, 'general', $store_id);
                $font_size_company = $this->_getConfig('font_size_company', 8, false, 'general', $store_id);
                $font_color_company = trim($this->_getConfig('font_color_company', '#222222', false, 'general', $store_id));
                if ($font_family_company == 'custom') {
                    $font_filename = $this->_getConfig('font_custom_company', '', false, 'general', $store_id);
                    $sub_folder = 'custom_font';
                    $option_group = 'general';
                    if ($font_filename) {
                        $font_path = Mage::getStoreConfig('system/filesystem/media', $store_id) . '/moogento/pickpack/' . $sub_folder . '/' . $font_filename;
                        if (is_file($font_path)) {
                            // gonna pass the font file path through the style attribute
                            $font_style_company = $font_path;
                        } else $font_family_company = 'helvetica';
                    }
                } else $font_custom_company = '';

                //Message UNDER SHIPPING ADDRESS
                $font_family_message = $this->_getConfig('font_family_message', 'helvetica', false, 'general', $store_id);
                $font_style_message = $this->_getConfig('font_style_message', 'italic', false, 'general', $store_id);
                $font_size_message = $this->_getConfig('font_size_message', 10, false, 'general', $store_id);
                $font_color_message = trim($this->_getConfig('font_color_message', '#222222', false, 'general', $store_id));
                $background_color_message = trim($this->_getConfig('background_color_message', '#5BA638', false, 'general', $store_id));
                $background_color_message_zend = new Zend_Pdf_Color_Html($background_color_message);
                //End message UNDER SHIPPING ADDRESS

                //Positional box
                $font_family_comments = $this->_getConfig('font_family_comments', 'helvetica', false, 'general', $store_id);
                $font_style_comments = $this->_getConfig('font_style_comments', 'regular', false, 'general', $store_id);
                $font_size_comments = $this->_getConfig('font_size_comments', 9, false, 'general', $store_id);
                $font_color_comments = trim($this->_getConfig('font_color_comments', '#222222', false, 'general', $store_id));
                $background_color_comments_pre = trim($this->_getConfig('background_color_comments', 'skyblue', false, 'general', $store_id));
                $fill_background_color_comments = $this->_getConfig('fill_background_color_comments', 0, false, 'general', $store_id);
                $background_color_comments = new Zend_Pdf_Color_Html($background_color_comments_pre);
                //End positional box

                //Under product item
                $font_family_gift_message = $this->_getConfig('font_family_gift_message', 'helvetica', false, 'general', $store_id);
                $font_style_gift_message = $this->_getConfig('font_style_gift_message', 'italic', false, 'general', $store_id);
                $font_size_gift_message = $this->_getConfig('font_size_gift_message', 12, false, 'general', $store_id);
                $font_color_gift_message = trim($this->_getConfig('font_color_gift_message', '#222222', false, 'general', $store_id));
                $background_color_gift_message = trim($this->_getConfig('background_color_gift_message', '#5BA638', false, 'general', $store_id));
                $background_color_gift_message_zend = new Zend_Pdf_Color_Html('' . $background_color_gift_message . '');
                //End under product item

                //$font_family_body = $this->_getConfig('font_family_body', 'helvetica', false, 'general', $store_id);
                //$font_style_body = $this->_getConfig('font_style_body', 'regular', false, 'general', $store_id);
                //$font_size_body = $this->_getConfig('font_size_body', 10, false, 'general', $store_id);
                //$font_color_body = trim($this->_getConfig('font_color_body', 'Black', false, 'general', $store_id));
                $fill_background_color_comments_under_product = $this->_getConfig('fill_background_color_comments_under_product', 0, false, 'general', $store_id);
                $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $store_id);
                // for width calculations, assuming helvetica...
                $font_helvetica = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                switch ($barcode_type) {
                    case 'code128':
                        $font_family_barcode = 'Code128bWin.ttf';
                        break;

                    case 'code39':
                        $font_family_barcode = 'CODE39.ttf';
                        break;

                    case 'code39x':
                        $font_family_barcode = 'CODE39X.ttf';
                        break;

                    default:
                        $font_family_barcode = 'Code128bWin.ttf';
                        break;
                }
                $font_size_options = $this->_getConfig('font_size_options', 8, false, 'general', $store_id);

                if ($this->_general['non_standard_characters'] == 'msgothic') {
                    $this->_general['font_family_body'] = 'msgothic';
                    $font_family_header = 'msgothic';
                    $font_family_gift_message = 'msgothic';
                    $font_family_message = 'msgothic';
                    $font_family_comments = 'msgothic';
                    $font_family_company = 'msgothic';
                    $this->_general['font_family_subtitles'] = 'msgothic';
                    $this->_general['non_standard_characters'] = 1;
                } elseif ($this->_general['non_standard_characters'] == 'tahoma') {
                    $this->_general['font_family_body'] = 'tahoma';
                    $font_family_header = 'tahoma';
                    $font_family_gift_message = 'tahoma';
                    $font_family_comments = 'tahoma';
                    $font_family_message = 'tahoma';
                    $font_family_company = 'tahoma';
                    $this->_general['font_family_subtitles'] = 'tahoma';
                    $this->_general['non_standard_characters'] = 1;
                } elseif ($this->_general['non_standard_characters'] == 'garuda') {
                    $this->_general['font_family_body'] = 'garuda';
                    $font_family_header = 'garuda';
                    $font_family_gift_message = 'garuda';
                    $font_family_comments = 'garuda';
                    $font_family_message = 'garuda';
                    $font_family_company = 'garuda';
                    $this->_general['font_family_subtitles'] = 'garuda';
                    $this->_general['non_standard_characters'] = 1;
                } elseif ($this->_general['non_standard_characters'] == 'sawasdee') {
                    $this->_general['font_family_body'] = 'sawasdee';
                    $font_family_header = 'sawasdee';
                    $font_family_gift_message = 'sawasdee';
                    $font_family_comments = 'sawasdee';
                    $font_family_message = 'sawasdee';
                    $font_family_company = 'sawasdee';
                    $this->_general['font_family_subtitles'] = 'sawasdee';
                    $this->_general['non_standard_characters'] = 1;
                } elseif ($this->_general['non_standard_characters'] == 'kinnari') {
                    $this->_general['font_family_body'] = 'kinnari';
                    $font_family_header = 'kinnari';
                    $font_family_gift_message = 'kinnari';
                    $font_family_comments = 'kinnari';
                    $font_family_message = 'kinnari';
                    $font_family_company = 'kinnari';
                    $this->_general['font_family_subtitles'] = 'kinnari';
                    $this->_general['non_standard_characters'] = 1;
                } elseif ($this->_general['non_standard_characters'] == 'purisa') {
                    $this->_general['font_family_body'] = 'purisa';
                    $font_family_header = 'purisa';
                    $font_family_gift_message = 'purisa';
                    $font_family_comments = 'purisa';
                    $font_family_message = 'purisa';
                    $font_family_company = 'purisa';
                    $this->_general['font_family_subtitles'] = 'purisa';
                    $this->_general['non_standard_characters'] = 1;
                }elseif ($this->_general['non_standard_characters'] == 'traditional_chinese') {
                    $this->_general['font_family_body'] = 'traditional_chinese';
                    $font_family_header = 'traditional_chinese';
                    $font_family_gift_message = 'traditional_chinese';
                    $font_family_comments = 'traditional_chinese';
                    $font_family_message = 'traditional_chinese';
                    $font_family_company = 'traditional_chinese';
                    $this->_general['font_family_subtitles'] = 'traditional_chinese';
                    $this->_general['non_standard_characters'] = 1;
                }elseif ($this->_general['non_standard_characters'] == 'simplified_chinese') {
                    $this->_general['font_family_body'] = 'simplified_chinese';
                    $font_family_header = 'simplified_chinese';
                    $font_family_gift_message = 'simplified_chinese';
                    $font_family_comments = 'simplified_chinese';
                    $font_family_message = 'simplified_chinese';
                    $font_family_company = 'simplified_chinese';
                    $this->_general['font_family_subtitles'] = 'simplified_chinese';
                    $this->_general['non_standard_characters'] = 1;
                }elseif ($this->_general['non_standard_characters'] == 'hebrew') {
                    $this->_general['font_family_body'] = 'hebrew';
                    $font_family_header = 'hebrew';
                    $font_family_gift_message = 'hebrew';
                    $font_family_comments = 'hebrew';
                    $font_family_message = 'hebrew';
                    $font_family_company = 'hebrew';
                    $this->_general['font_family_subtitles'] = 'hebrew';
                    $this->_general['non_standard_characters'] = 1;
                }
                elseif ($this->_general['non_standard_characters'] == 'yes') {
                    $this->_general['non_standard_characters'] = 2;
                }

                $this->_pageFonts['font_family_body'] = $this->_general['font_family_body'];
                $this->_pageFonts['font_style_body'] = $this->_general['font_style_body'];
                $this->_pageFonts['font_size_body'] = $this->_general['font_size_body'];
                $this->_pageFonts['font_color_body'] = $this->_general['font_color_body'];
                $this->_pageFonts['non_standard_characters'] = $this->_general['non_standard_characters'];

                $background_color_product_temp = trim($this->_getConfig('background_color_product', '#FFFFFF', false, 'general', $store_id));
                $background_color_product = new Zend_Pdf_Color_Html($background_color_product_temp);

                $background_color_vert_product_temp = '#FFFFFF';
                $background_color_vert_product = new Zend_Pdf_Color_Html($background_color_vert_product_temp);

                $product_images_line_nudge = trim($this->_getConfig('product_images_line_nudge', 0, false, $wonder, $store_id));
                if ($product_images_line_nudge > 0) $product_images_line_nudge = -abs($product_images_line_nudge);
                if ($product_images_yn == 0) $product_images_line_nudge = 0;
                $product_images_border_color_temp = strtoupper(trim($this->_getConfig('product_images_border_color', '#FFFFFF', false, $wonder, $store_id)));
                $product_images_border_color = new Zend_Pdf_Color_Html($product_images_border_color_temp);
                $product_images_maxdimensions = explode(',', str_ireplace('null', '', $this->_getConfig('product_images_maxdimensions', '50,50', false, $wonder, $store_id)));
                if ($product_images_maxdimensions[0] == '' || $product_images_maxdimensions[1] == '') {
                    if ($product_images_maxdimensions[0] == '') $product_images_maxdimensions[0] = NULL;
                    if ($product_images_maxdimensions[1] == '') $product_images_maxdimensions[1] = NULL;
                    if ($product_images_maxdimensions[0] == NULL && $product_images_maxdimensions[1] == NULL)
                    {
                        $product_images_maxdimensions[0] = 50;
                        $product_images_maxdimensions[1] = 50;
                }
                }

                // attr #2
                $shelving_2_attribute ='';
                if ($shelving_real_yn == 1) {
                    $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $store_id);
                    $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $store_id));
                    if ($shelving_attribute == '') $shelving_yn = 0;
                    if ($shelving_yn == 0) $shelving_attribute = null;
                    $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $store_id);
                    $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
                } else $shelving_yn = 0;
                // attr #3
                if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
                    $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $store_id);
                    $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $store_id));
                    if ($shelving_2_attribute == '') $shelving_2_yn = 0;
                    $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $store_id);
                    $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
                } else $shelving_2_yn = 0;

                if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
                    $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $store_id);
                    $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $store_id));
                    if ($shelving_3_attribute == '') $shelving_3_yn = 0;
                    $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $store_id);
                    $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
                } else $shelving_3_yn = 0;

                //combine custom attribute
                if ($shelving_real_yn == 1) {
                    $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $store_id);
                    $combine_custom_attribute_title = $this->_getConfig('combine_custom_attribute_title', '', false, $wonder, $store_id);
                    $combine_custom_attribute_title = trim(str_ireplace(array('blank', "'"), '', $combine_custom_attribute_title));
                    $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $store_id);
                    $combine_custom_attribute_title_each = $this->_getConfig('combine_custom_attribute_title_each', 10, false, $wonder, $store_id);
                } else $combine_custom_attribute_yn = 0;

                $has_shipping_address = false;
                $has_billing_address = false;
                foreach ($order->getAddressesCollection() as $address) {
                    if ($address->getAddressType() == 'shipping' && !$address->isDeleted()) {
                        $has_shipping_address = true;
                    } elseif ($address->getAddressType() == 'billing' && !$address->isDeleted()) {
                        $has_billing_address = true;
                    }
                }

                $shipment_details_yn = $this->_getConfig('shipment_details_yn', 0, false, $wonder, $store_id);
                $shipment_details_nudge = explode(",", $this->_getConfig('shipment_details_nudge', '0,0', true, $wonder, $store_id));
                $shipment_details_boxes_yn = $this->_getConfig('shipment_details_boxes_yn', 0, false, $wonder, $store_id);
                $shipment_details_boxes_weightsplit = $this->_getConfig('shipment_details_boxes_weightsplit', 20, false, $wonder, $store_id);

                $shipment_details_weight = $this->_getConfig('shipment_details_weight', 0, false, $wonder, $store_id);
                $shipment_details_weight_unit = $this->_getConfig('shipment_details_weight_unit', 'kg', false, $wonder, $store_id);
                $shipment_details_carrier = $this->_getConfig('shipment_details_carrier', 0, false, $wonder, $store_id);
                $shipment_details_tracking_number = $this->_getConfig('shipment_details_tracking_number',0, false, $wonder, $store_id);
                if ($shipment_details_carrier == 'filtered_by_pallet') {
                    $shipment_details_pallet_weight = trim($this->_getConfig('shipment_details_pallet_weight', 500, false, $wonder, $store_id));
                } else $shipment_details_pallet_weight = 0;

                if(Mage::helper('pickpack')->isInstalled('Magalter_Customshipping'))
                {
                    $shipment_details_shipping_options = $this->_getConfig('shipment_details_shipping_options_yn',0, false, $wonder, $store_id);
                    $shipment_details_shipping_options_filter = explode(',',$this->_getConfig('shipment_details_shipping_options_filter','', false, $wonder, $store_id));
                }
                $show_aitoc_checkout_field_yn = $this->_getConfig('show_aitoc_checkout_field_yn', 0, false, $wonder, $store_id);
                $show_aitoc_checkout_field = $this->_getConfig('show_aitoc_checkout_field', '', false, $wonder, $store_id);
                //for bottom
                $show_aitoc_checkout_field_bottom_yn = $this->_getConfig('show_aitoc_checkout_field_bottom_yn', 0, false, $wonder, $store_id);
                $show_aitoc_checkout_field_bottom = $this->_getConfig('show_aitoc_checkout_field_bottom', '', false, $wonder, $store_id);

                $show_shipping_method_bottom_yn = $this->_getConfig('show_shipping_method_bottom_yn', 0, false, $wonder, $store_id);
                $show_shipping_method_bottom_nugde = explode(",", $this->_getConfig('show_shipping_method_bottom_nugde', '0,0', true, $wonder, $store_id));

                $shipment_temando_comment_yn = $this->_getConfig('shipment_temando_comment_yn', 1, false, $wonder, $store_id);
                $shipment_details_payment = $this->_getConfig('shipment_details_payment', 0, false, $wonder, $store_id);
                $shipment_details_cardinfo = $this->_getConfig('shipment_details_cardinfo', 0, false, $wonder, $store_id);
                $pickpack_show_full_payment_yn = $this->_getConfig('pickpack_show_full_payment_yn', 0, false, $wonder, $store_id);
                $shipment_details_purchase_order = $this->_getConfig('shipment_details_purchase_order', 0, false, $wonder, $store_id);
                if ($shipment_details_payment == 0) {
                    $shipment_details_purchase_order = 0;
                    $shipment_details_cardinfo = 0;
                }

                $shipment_details_custgroup = $this->_getConfig('shipment_details_custgroup', 0, false, $wonder, $store_id);
                $shipment_details_customer_id = $this->_getConfig('shipment_details_customer_id', 0, false, $wonder, $store_id);
                $shipment_details_customer_email = $this->_getConfig('shipment_details_customer_email', 0, false, $wonder, $store_id);
                $shipment_details_customer_vat = $this->_getConfig('shipment_details_customer_vat', 0, false, $wonder, $store_id);
                $shipment_details_order_id = $this->_getConfig('shipment_details_order_id', 0, false, $wonder, $store_id);
                $shipment_details_invoice_id = $this->_getConfig('shipment_details_invoice_id', 0, false, $wonder, $store_id);
                $shipment_details_order_date = $this->_getConfig('shipment_details_order_date', 0, false, $wonder, $store_id);
                $shipment_details_bold_label = $this->_getConfig('shipment_details_bold_label', 0, false, $wonder, $store_id);
                $shipment_details_shipp_date = $this->_getConfig('shipment_details_shipp_date', 0, false, $wonder, $store_id);
                $shipment_details_paid_date = $this->_getConfig('shipment_details_paid_date', 0, false, $wonder, $store_id);
                $shipment_details_order_source = $this->_getConfig('shipment_details_order_source', 0, false, $wonder, $store_id);
                $shipment_details_fixed_text = $this->_getConfig('shipment_details_fixed_text', 0, false, $wonder, $store_id);
                $shipment_details_fixed_title = $this->_getConfig('shipment_details_fixed_title', 0, false, $wonder, $store_id);
                $shipment_details_fixed_value = $this->_getConfig('shipment_details_fixed_value', 0, false, $wonder, $store_id);
                $show_mageworx_multifees = $this->_getConfig('show_mageworx_multifees', 0, false, $wonder, $store_id);
                $show_wsa_storepickup = $this->_getConfig('show_wsa_storepickup', 1, false, $wonder, $store_id);
				if(!Mage::helper('pickpack')->isInstalled('Webshopapps_Wsacommon')) $show_wsa_storepickup = 0;
                $store_pickup_hide_shipping_yn = $this->_getConfig('store_pickup_hide_shipping_yn', 1, false, $wonder, $store_id);
				if($show_wsa_storepickup == 0) $store_pickup_hide_shipping_yn = 0;
				if($store_pickup_hide_shipping_yn == 1) $shipment_details_carrier = 0;

                $customer_custom_attribute_yn = $this->_getConfig('customer_custom_attribute_yn', 0, false, $wonder, $store_id);
                $customer_custom_attribute = '';
                if ($customer_custom_attribute_yn == 1) {
                    $customer_custom_attribute = $this->_getConfig('customer_custom_attribute', '', false, $wonder, $store_id);
                }
                $shipment_details_count = $this->_getConfig('shipment_details_count', 0, false, $wonder, $store_id);
                $customer_group_filter = $this->_getConfig('shipment_details_custgroup_filter', 0, false, $wonder, $store_id);
                $customer_group_filter = trim(strtolower($customer_group_filter));
                $shipment_details_custom_attribute_yn = $this->_getConfig('shipment_details_custom_attribute_yn', 0, false, $wonder, $store_id);
                $shipment_details_custom_attribute = trim($this->_getConfig('shipment_details_custom_attribute', '', false, $wonder, $store_id));
                if ($shipment_details_custom_attribute == '') $shipment_details_custom_attribute_yn = 0;
                $shipment_details_custom_attribute_2_yn = $this->_getConfig('shipment_details_custom_attribute_2_yn', 0, false, $wonder, $store_id);
                $shipment_details_custom_attribute_2 = trim($this->_getConfig('shipment_details_custom_attribute_2', '', false, $wonder, $store_id));
                if ($shipment_details_custom_attribute_2 == '') $shipment_details_custom_attribute_2_yn = 0;
                if ($shipment_details_custom_attribute_yn == 0) {
                    $shipment_details_custom_attribute = '';
                    $shipment_details_custom_attribute_2_yn = 0;
                    $shipment_details_custom_attribute_2 = '';
                }
                if ($shipment_details_custom_attribute_2_yn == 0) {
                    $shipment_details_custom_attribute_2 = '';
                }
                $shipment_details_deadline_yn = $this->_getConfig('shipment_details_deadline_yn', 0, false, $wonder, $store_id);
                $shipment_details_deadline_text = trim($this->_getConfig('shipment_details_deadline_text', '', false, $wonder, $store_id));
                $shipment_details_deadline_days = trim($this->_getConfig('shipment_details_deadline_days', 0, false, $wonder, $store_id));
                if ($shipment_details_deadline_yn == 0) {
                    $shipment_details_deadline_text = '';
                    $shipment_details_deadline_days = 0;
                }

                $shipment_details_pickup_time_yn = $this->_getConfig('shipment_details_pickup_time_yn', 0, false, $wonder, $store_id);

                $configurable_names = $this->_getConfig('pack_configname', 'simple', false, $wonder, $store_id); //col/sku
                $columnYNudge = $columnYNudgeDefault;
                $showBarCode = $this->_getConfig('pickpack_packbarcode', 0, false, $wonder, $store_id);
                $showOrderId = $this->_getConfig('show_order_id', 0, false, $wonder, $store_id);
                $orderId_font_size = $this->_getConfig('font_size_orderid', 14, false, $wonder, $store_id);
                $shipaddress_packbarcode_yn = $this->_getConfig('shipaddress_packbarcode_yn', 0, false, $wonder, $store_id);
                $bottom_barcode_nudge = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $wonder, $store_id));
                $shipaddress_packbarcode2_yn = $this->_getConfig('shipaddress_packbarcode2_yn', 0, false, $wonder, $store_id);
                $bottom_barcode2_nudge = explode(",", $this->_getConfig('bottom_barcode2_nudge', '0,0', true, $wonder, $store_id));

                //$shipaddress_title = $this->_getConfig('shipaddress_title', '', false, $wonder, $store_id);
                $barcode_nudge = explode(",", $this->_getConfig('barcode_nudge', '0,0', true, $wonder, $store_id));
                $order_id_nudge = explode(",", $this->_getConfig('order_id_nudge', '0,0', true, $wonder, $store_id));
                $orderDateXY = explode(',', $orderDateXYDefault);
                $orderIdXY = explode(',', $orderIdXYDefault);
                $addressXY = explode(",", $addressXYDefault);
                $addressFooterXY = explode(",", $this->_getConfig('pickpack_shipaddress', $addressFooterXYDefault, true, $wonder, $store_id));
                $address2ndFooterXY = explode(",", $this->_getConfig('pickpack_second_shipaddress', $addressFooterXYDefault, true, $wonder, $store_id));
                $addressFooterXY_xtra = explode(",", $this->_getConfig('pickpack_shipaddress_xtra', $addressFooterXYDefault_xtra, true, $wonder, $store_id));
                $font_size_shipaddress_xtra = $this->_getConfig('pickpack_shipfont_xtra', 8, false, $wonder, $store_id);
                $flat_address_margin_rt_xtra = $this->_getConfig('flat_address_margin_rt_xtra', 0, true, $wonder, $store_id);
                $show_1st_qrcode =  $this->_getConfig('pickpack_show_first_qrcode', 0, false, $wonder, $store_id);
                $qrcode_pattern = $this->_getConfig('pickpack_show_qrcode_pattern','{{order_id}}', false, $wonder, $store_id);
                $qrcode_1st_nudge = explode(",", $this->_getConfig('qrcode_1st_nudge', '0,0', false, $wonder, $store_id));
                $show_2nd_qrcode =  $this->_getConfig('show_2nd_qrcode', 0, false, $wonder, $store_id);
                $qrcode_2nd_nudge = explode(",", $this->_getConfig('qrcode_2nd_nudge', '0,0', false, $wonder, $store_id));
                $orderIdXY[1] = ($page_top - 5 - 41 - 32);
                if ($background_color_subtitles == '#FFFFFF') {
                    $orderIdXY[0] -= 11;
                    $orderIdXY[1] += 11;
                    $addressXY[0] -= 15;
                    $addressXY[1] += 10;
                }
                $datebar_start_y = $orderIdXY[1];
                $shipping_title = trim($this->_getConfig('shipping_title', '', false, $wonder, $store_id));
                $product_options_title = trim($this->_getConfig('product_options_title', '', false, $wonder, $store_id));
                $images_title = trim($this->_getConfig('images_title', '', false, $wonder, $store_id));
                $qty_title = trim($this->_getConfig('qty_title', '', false, $wonder, $store_id));
                $price_title = trim($this->_getConfig('price_title', '', false, $wonder, $store_id));
                $tax_title = trim($this->_getConfig('tax_title', '', false, $wonder, $store_id));
                $total_title = trim($this->_getConfig('total_title', '', false, $wonder, $store_id));
                if ($tax_yn == 'yesboth') {
                    $tax_title = trim($this->_getConfig('tax_title_both', '', false, $wonder, $store_id));
                    $tax_label = trim($this->_getConfig('tax_label_both', 'VAT', false, $wonder, $store_id));
                    $taxEachX = $this->_getConfig('pricesT_item_taxX_both', 475, false, $wonder, $store_id);
                } elseif ($tax_yn == 'yessubtotal') {
                    $tax_label = trim($this->_getConfig('tax_label_subtotal', 'VAT', false, $wonder, $store_id));
                }
                if (!isset($taxEachX))
                    $taxEachX = $this->_getConfig('pricesT_item_taxX', 475, false, $wonder, $store_id);


                $product_options_yn = $this->_getConfig('product_options_yn', 'no', false, $wonder, $store_id);
                // added above
				// $billing_details_yn = $this->_getConfig('billing_details_yn', 0, false, $wonder, $store_id);
                $billing_phone_yn = $this->_getConfig('billing_phone_yn', 0, false, $wonder, $store_id);
                $shipping_details_yn = $this->_getConfig('shipping_details_yn', 1, false, $wonder, $store_id);
                $billing_tax_details_yn = $this->_getConfig('billing_tax_details_yn', '', false, $wonder, $store_id);
                $billing_details_position = $this->_getConfig('billing_details_position', 0, false, $wonder, $store_id);
                $billing_title = trim($this->_getConfig('billing_title', '', false, $wonder, $store_id));
                $billing_phone_yn_in_shipping_details = $this->_getConfig('billing_phone_yn_in_shipping_details', 0, false, $wonder, $store_id);

                if ($shipping_details_yn == 0) $shipping_title = null;
                if ($has_billing_address === false) $billing_details_yn = 0;
                if ($billing_details_yn == 0) {
                    $billing_tax_details_yn = 0;
                    $billing_details_position = 0;
                    $billing_title = '';
                }
                // if billing address set to yes, shipping set to no, and billing address set to be right-side, show on left
                if (($billing_details_yn == 1) && ($shipping_details_yn == 0)) $billing_details_position = 1;
                if (($billing_details_yn == 0) && ($shipping_details_yn == 1)) $billing_details_position = 0;
                if (($shipping_billing_title_position == 'beside') && ($title_date_xpos < 100) && ($billing_details_yn == 1)) {
                    $title_date_xpos = 350;
                }
                $cutoff_no = $cutoff_noDefault;
                $tickbox_yn = 0;//$this->_getConfig('tickbox_yn', 1, false, $wonder, $store_id);
                $tickbox_2_yn = 0;//$this->_getConfig('tickbox_2_yn', 1, false, $wonder, $store_id);
                $show_name_yn = $this->_getConfig('show_product_name', 0, false, $wonder, $store_id);
                $productX = $this->_getConfig('pricesN_productX', 10, false, $wonder, $store_id);

                if(Mage::getEdition() == 'Enterprise'){
                    $show_gift_wrap_yn = $this->_getConfig('show_gift_wrap', 0, false, $wonder, $store_id);
                    $gift_wrap_title = $this->_getConfig('show_gift_wrap_title', '', false, $wonder, $store_id);
                    $gift_wrap_xpos = $this->_getConfig('show_gift_wrap_xpos', 560, false, $wonder, $store_id);
                    $show_gift_wrap_icon = $this->_getConfig('show_gift_wrap_icon', 0, false, $wonder, $store_id);
                    $show_gift_wrap_top_right = $this->_getConfig('show_gift_wrap_top_right', 1, false, $wonder, $store_id);
                    $show_top_right_gift_icon = false;
                    $show_gift_wrap_top_right_xpos = $this->_getConfig('show_gift_wrap_top_right_xpos', 0, false, $wonder, $store_id);
                    $show_gift_wrap_top_right_ypos = $this->_getConfig('show_gift_wrap_top_right_ypos', 0, false, $wonder, $store_id);
                }else{
                    $show_gift_wrap_yn = 0;
                }
                if ($tickbox_yn == 0) $tickbox_2_yn = 0;
                $tickboxX = $this->_getConfig('tickboxX', 27, false, $wonder, $store_id);
                $tickbox2X = $this->_getConfig('tickbox2X', 54, false, $wonder, $store_id);
                $combine_custom_attribute_under_product = $this->_getConfig('combine_custom_attribute_under_product', 54, false, $wonder, $store_id);
                $show_qty_options = $this->_getConfig('show_qty_options', 1, false, $wonder, $store_id);
                $show_zero_qty_options = $this->_getConfig('show_zero_qty_options', 1, false, $wonder, $store_id);
                $center_value_qty = $this->_getConfig('center_value_qty', 1, false, $wonder, $store_id);
                if ($show_qty_options == 1)
                    $show_subtotal_options = $this->_getConfig('show_subtotal_options', 1, false, $wonder, $store_id);
                $skuX = $this->_getConfig('pricesN_skuX', 10, false, $wonder, $store_id);
                $sku_barcodeX = $this->_getConfig('pricesN_barcodeX', 10, false, $wonder, $store_id);
                $sku_barcodeX_2 = $this->_getConfig('pricesN_barcodeX_2', 30, false, $wonder, $store_id);
                $stockqtyX = $this->_getConfig('pricesN_stockqtyX', 10, false, $wonder, $store_id);

                $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $store_id);
                $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $store_id);
                $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $store_id);
                $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $store_id);

                $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $store_id);
                $qtyX = $this->_getConfig('pricesN_qty_priceX', 10, false, $wonder, $store_id);
                $imagesX = $this->_getConfig('pricesN_images_priceX', 50, false, $wonder, $store_id);
                $priceX = $this->_getConfig('pricesY_priceX', 10, false, $wonder, $store_id);
                $priceEachX = $this->_getConfig('pricesY_item_priceX', 10, false, $wonder, $store_id);

                $product_qty_backordered_yn = $this->_getConfig('product_qty_backordered_yn', 0, false, $wonder, $store_id);
                $prices_qtybackorderedX = $this->_getConfig('prices_qtybackorderedX', 400, false, $wonder, $store_id);
                $product_qty_backordered_title = $this->_getConfig('product_qty_backordered_title', '', false, $wonder, $store_id);

                $product_warehouse_yn = $this->_getConfig('product_warehouse_yn', 0, false, $wonder, $store_id);
                $prices_warehouseX = $this->_getConfig('prices_warehouseX', 400, false, $wonder, $store_id);
                $product_warehouse_title = $this->_getConfig('product_warehouse_title', '', false, $wonder, $store_id);
                $supplier_hide_attribute_column = $this->_getConfig('supplier_hide_attribute_column',0, false, $wonder, $store_id);

                $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $store_id);
                if($serial_code_yn == 1){
                    $serial_code_title = $this->_getConfig('serial_code_title', 'serial_code', false, $wonder, $store_id);
                    $serial_codeX = $this->_getConfig('serial_code_pos', 350, false, $wonder, $store_id);
                }

                $tickbox_yn = $this->_getConfig('tickbox_yn', 1, false, $wonder, $store_id);
                $tickbox_2_yn = $this->_getConfig('tickbox_2_yn', 1, false, $wonder, $store_id);
                if ($tickbox_yn == 0) $tickbox_2_yn = 0;


                if ($tickbox_yn == 1) $this->columns_xpos_array['tickboxX'] = $tickboxX;
                if ($tickbox_2_yn == 1) $this->columns_xpos_array['tickbox2X'] = $tickbox2X;
                if ($product_sku_yn == 1) $this->columns_xpos_array['skuX'] = $skuX;
                if ($product_sku_barcode_yn != 0)
                {
                    $this->columns_xpos_array['sku_barcodeX'] = $sku_barcodeX;
                    if ($product_sku_barcode_2_yn != 0) $this->columns_xpos_array['sku_barcodeX_2'] = $sku_barcodeX_2;
                }

                if ($product_stock_qty_yn == 1) $this->columns_xpos_array['stockqtyX'] = $stockqtyX;
                if ($show_name_yn == 1) $this->columns_xpos_array['productX'] = $productX;
                if ($serial_code_yn == 1) $this->columns_xpos_array['serial_codeX'] = $serial_codeX;
                if ($shelving_real_yn == 1) $this->columns_xpos_array['shelfX'] = $shelfX;
                if ($shelving_yn == 1) $this->columns_xpos_array['shelf2X'] = $shelf2X;
                if ($shelving_2_yn == 1) $this->columns_xpos_array['shelf3X'] = $shelf3X;
                if ($shelving_3_yn == 1) $this->columns_xpos_array['shelf4X'] = $shelf4X;
                if ($combine_custom_attribute_yn == 1) $this->columns_xpos_array['combine_custom_attribute_Xpos'] = $combine_custom_attribute_Xpos;

                $this->columns_xpos_array['optionsX'] = $optionsX;
                $this->columns_xpos_array['qtyX'] = $qtyX;
                if ($product_images_yn == 1) $this->columns_xpos_array['imagesX'] = $imagesX;
                if ($prices_yn == 1) {
                    $this->columns_xpos_array['priceX'] = $priceX;
                    $this->columns_xpos_array['priceEachX'] = $priceEachX;
                }
                if ($tax_col_yn == 1) $this->columns_xpos_array['taxEachX'] = $taxEachX;
                if ($product_qty_backordered_yn == 1) $this->columns_xpos_array['backorderedX'] = $prices_qtybackorderedX;
                if($show_allowance_yn == 1) $this->columns_xpos_array['allowance'] = $show_allowance_xpos;
                if($supplier_hide_attribute_column ==0)
                if ($product_warehouse_yn == 1) $this->columns_xpos_array['warehouseX'] = $prices_warehouseX;

                asort($this->columns_xpos_array);
                $orderdetailsX = 304;
                if ($shipping_billing_title_position == 'beside' && $title_date_xpos != 'auto') $orderdetailsX = $title_date_xpos;


                $override_address_format_yn = $this->_getConfig('override_address_format_yn', 0, false, 'general', $store_id);
                $default_address_format = Mage::getStoreConfig('customer/address_templates/pdf');
                $default_address_format = str_replace(array("depend", 'var ', '{{', '}}'), array("if", '', '{', '}'), $default_address_format);
                $custom_address_format = $this->_getConfig('address_format', '', false, 'general', $store_id); //col/sku

                if ($override_address_format_yn == 1)
                    $address_format = $custom_address_format;
                else
                    $address_format = $default_address_format;

                $customer_email_yn = $this->_getConfig('customer_email_yn', 0, false, 'general', $store_id);
                // if(($customer_email_yn == 'yes') || ($customer_email_yn == 'yesdetails'))
//                 {
//                  $customer_email_yn = 'yes';
//                 }
//                 else
//                  $customer_email_yn = 'no';

                $customer_phone_yn = $this->_getConfig('customer_phone_yn', 0, false, 'general', $store_id);
                $address_countryskip = trim(strtolower($this->_getConfig('address_countryskip', 0, false, 'general', $store_id)));
                $bottom_shipping_address_yn = $this->_getConfig('pickpack_bottom_shipping_address_yn', 0, false, $wonder, $store_id);
                if($bottom_shipping_address_yn ==1){
                    $tracking_number_barcode_yn = $this->_getConfig('tracking_number_barcode_yn', 0, false, $wonder, $store_id);
                    $tracking_number_yn = $this->_getConfig('tracking_number_yn', 1, false, $wonder, $store_id);
                }
                $bottom_2nd_shipping_address_yn = $this->_getConfig('pickpack_second_bottom_shipping_address_yn', 0, false, $wonder, $store_id);
                $bottom_shipping_address_yn_xtra = $this->_getConfig('pickpack_bottom_shipping_address_yn_xtra', 0, false, $wonder, $store_id);
				if($bottom_shipping_address_yn_xtra == 2){
	                $addressFooterXY_xtra = explode(",", $this->_getConfig('pickpack_shipaddress_xtra_2', $addressFooterXYDefault_xtra, true, $wonder, $store_id));
	                $font_size_shipaddress_xtra = $this->_getConfig('pickpack_shipfont_xtra_2', 8, false, $wonder, $store_id);
	                $flat_address_margin_rt_xtra = $this->_getConfig('flat_address_margin_rt_xtra_2', 0, true, $wonder, $store_id);
				}
                $bottom_shipping_address_id_yn = $this->_getConfig('pickpack_bottom_shipping_address_id_yn', 0, false, $wonder, $store_id);
                $bottom_shipping_address_id_2_yn = $this->_getConfig('pickpack_bottom_shipping_address_id_2_yn', 0, false, $wonder, $store_id);

                //$return_address_yn = $this->_getConfig('pickpack_return_address_yn', 0, false, $wonder, $store_id);
                $show_bundle_parent_yn = $this->_getConfig('show_bundle_parent', "no", false, $wonder);
                if ($this->_packingsheet['pickpack_return_address_yn'] == 0) {
                    $show_return_logo_yn = '0';
                }
                if ($bottom_shipping_address_yn == 0) {
                    $shipaddress_packbarcode_yn = 0;
                    //$shipaddress_title = '';
                    $show_shipping_logo_yn = 0;
                    $bottom_shipping_address_id_yn = 0;
                }

                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') {
                    $return_address_group1 = $this->_getConfig('pickpack_return_address_group1', '', false, $wonder, $store_id);
                    $return_address_group2 = $this->_getConfig('pickpack_return_address_group2', '', false, $wonder, $store_id);
                    $return_address_group3 = $this->_getConfig('pickpack_return_address_group3', '', false, $wonder, $store_id);
                    $return_address = $this->_getConfig('pickpack_return_address_group_default', '', false, $wonder, $store_id);

                    $font_size_returnaddress = $this->_getConfig('pickpack_returnfont_group', 9, false, $wonder, $store_id);
                    $font_size_shipaddress = $this->_getConfig('pickpack_shipfont_group', 15, false, $wonder, $store_id);
                    $show_return_logo_yn = $this->_getConfig('pickpack_returnlogo_group', '', false, $wonder, $store_id);
                    $returnAddressFooterXY = explode(",", $this->_getConfig('pickpack_returnaddress_group', $returnAddressFooterXYDefault, true, $wonder, $store_id));
                    $return_logo_XY = explode(",", $this->_getConfig('pickpack_nudgelogo_group', $return_logo_XYDefault, true, $wonder, $store_id));
                } else {
                    $return_address = $this->_getConfig('pickpack_return_address', '', false, $wonder, $store_id);
                    $return_address_group1 = '';
                    $return_address_group2 = '';
                    $return_address_group3 = '';

                    $font_size_returnaddress = $this->_getConfig('pickpack_returnfont', 9, false, $wonder, $store_id);
                    $font_size_shipaddress = $this->_getConfig('pickpack_shipfont', 15, false, $wonder, $store_id);
                    $show_return_logo_yn = $this->_getConfig('pickpack_returnlogo', 0, false, $wonder, $store_id);
                    $returnAddressFooterXY = explode(",", $this->_getConfig('pickpack_returnaddress', $returnAddressFooterXYDefault, true, $wonder, $store_id));
                    $return_logo_dimension = $this->_getConfig('pickpack_logo_dimension', 0 , true, $wonder, $store_id);
                    $return_logo_XY = explode(",", $this->_getConfig('pickpack_nudgelogo', $return_logo_XYDefault, true, $wonder, $store_id));
                    $show_return_logo2_yn = $this->_getConfig('pickpack_returnlogo2', 0, false, $wonder, $store_id);
                    $return_logo2_XY = explode(",", $this->_getConfig('pickpack_nudgelogo2', $return_logo2_XYDefault, true, $wonder, $store_id));
                }

                $company_address_yn = $this->_getConfig('pickpack_company_address_yn', 0, false, $wonder, $store_id);
                $company_address_x_nudge = $this->_getConfig('company_address_x_nudge', $company_address_x_nudge_default, true, $wonder, $store_id);

                if ($company_address_yn == 'yesgroup') {
                    $company_address_group1 = $this->_getConfig('pickpack_company_address_group1', '', false, $wonder, $store_id);
                    $company_address_group2 = $this->_getConfig('pickpack_company_address_group2', '', false, $wonder, $store_id);
                    $company_address_group3 = $this->_getConfig('pickpack_company_address_group3', '', false, $wonder, $store_id);
                    $company_address = $this->_getConfig('pickpack_company_address_group_default', '', false, $wonder, $store_id);
                } else {
                    if ($show_top_logo_yn == 1) {
                        $company_address = $this->_getConfig('pickpack_company_address', '', false, $wonder, $store_id);
                    } else {
                        $company_address = $this->_getConfig('pickpack_company_address_no_logo', '', false, $wonder, $store_id);
                    }
                    $company_address_group1 = '';
                    $company_address_group2 = '';
                    $company_address_group3 = '';
                }
                $logo_maxdimensions = explode(',', '269,41');
                if ($logo_position == 'fullwidth') {
                    $company_address_yn = 0;
                    if ($this->_packingsheet['page_size'] == "letter")
                        $logo_maxdimensions = explode(',', '612,41');
                    elseif ($this->_packingsheet['page_size'] == "a4")
                        $logo_maxdimensions = explode(',', '595,41');
                    else
                        $logo_maxdimensions = explode(',', '556,41');
                }
                if ($logo_maxdimensions[0] == '' || $logo_maxdimensions[1] == '') {
                    if ($logo_maxdimensions[0] == '') $logo_maxdimensions[0] = NULL;
                    if ($logo_maxdimensions[1] == '') $logo_maxdimensions[1] = NULL;
                    if ($logo_maxdimensions[0] == NULL && $logo_maxdimensions[1] == NULL) $logo_maxdimensions[0] = 269;
                }

                $float_top_address_yn = 0;
                if ($logo_position == 'right') {
                    $company_address = $pickpack_company_address_logoright = $this->_getConfig('pickpack_company_address_logoright', '', false, $wonder, $store_id);
                    if ($page_template == 'bringup') {
                        $float_top_address_yn = $this->_getConfig('float_top_address_yn', 0, false, $wonder, $store_id);
                    }
                } else {
                    $pickpack_company_address_logoright = '';
                    $float_top_address_yn = 0;
                }

                if ($company_address_yn === 0)
                {
                    $company_address = '';
                }

                if (($float_top_address_yn == 0) && ($page_template == 'bringup')) $mailer_padding = array(0, 0, 0);

                $page_1_products_y_cutoff = $this->_getConfig('page_1_products_y_cutoff', 0, false, $wonder, $store_id);

                $capitalize_label_yn = $this->_getConfig('capitalize_label_yn', 0, false, $wonder, $store_id); // o,usonly,1
                $capitalize_label2_yn = $this->_getConfig('capitalize_label2_yn', 0, false, $wonder, $store_id); // o,usonly,1
                $message_yn = $this->_getConfig('custom_message_yn', '', false, $wonder, $store_id);
                $custom_message_fixed = $this->_getConfig('custom_message_fixed', 0, false, $wonder, $store_id);
                $custom_message_image_locked_yn = $this->_getConfig('custom_message_image_locked_yn', 0, false, $wonder, $store_id);
                $custom_message_image_nudge = explode(',', trim($this->_getConfig('custom_message_image_nudge', '0,0', false, $wonder, $store_id)));

                if ($message_yn != 'yesimage') {
                    $custom_message_image_locked_yn = 0;
                    $custom_message_image_nudge = null;
                }
                $message_filter = trim($this->_getConfig('custom_message_filter', '', false, $wonder, $store_id));
                $message = trim($this->_getConfig('custom_message', '', false, $wonder, $store_id));
                $custom_message_position = array();
                if ($message_yn == 'yesbox') {
                    $message = trim($this->_getConfig('custom_message_yesbox', '', false, $wonder, $store_id));
                    $custom_message_position = explode(',', trim($this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $store_id)));
                }
                $messageA = trim($this->_getConfig('custom_messageA', '', false, $wonder, $store_id));
                $messageB = trim($this->_getConfig('custom_messageB', '', false, $wonder, $store_id));
                if ($message_yn == 'yes2') $message = $messageA;
                if ($message_yn == 'no' || $message_yn == 'yesimage') {
                    $message = null;
                    $messageA = null;
                    $messageB = null;
                }

                $sort_packing_yn = $this->_getConfig('sort_packing_yn', 1, false, 'general', $store_id);
                $sort_packing = $this->_getConfig('sort_packing', 'sku', false, 'general', $store_id);
                $sortorder_packing = $this->_getConfig('sort_packing_order', 'ascending', false, 'general', $store_id);
                $sort_packing_attribute = null;
                if ($sort_packing == 'attribute') {
                    $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, 'general', $store_id));
                    if ($sort_packing_attribute != '') $sort_packing = $sort_packing_attribute;
                    else $sort_packing = 'sku';
                }
                /*****************Config for background image***************/
                $page_background_image_yn = $this->_getConfig('page_background_image_yn', 1, false, $wonder, $store_id);
                //$page_background_image = $this->_getConfig('page_background_image', 1, false, $wonder, $store_id);
                $page_background_position = $this->_getConfig('page_background_position', 1, false, $wonder, $store_id);
                $page_background_resize = $this->_getConfig('page_background_resize', 1, false, $wonder, $store_id);
                $page_background_nudge = explode(',', $this->_getConfig('page_background_nudge', '0,0', false, $wonder, $store_id));

                $sort_packing_secondary = $this->_getConfig('sort_packing_secondary', 'sku', false, 'general', $store_id);
                $sortorder_packing_secondary = $this->_getConfig('sort_packing_secondary_order', 'ascending', false, 'general', $store_id);
                $sort_packing_secondary_attribute = null;
                if ($sort_packing_secondary == 'attribute') {
                    $sort_packing_secondary_attribute = trim($this->_getConfig('sort_packing_secondary_attribute', '', false, 'general', $store_id));
                    if ($sort_packing_secondary_attribute != '') $sort_packing_secondary = $sort_packing_secondary_attribute;
                    else $sort_packing_secondary = 'sku';
                }

                if ($sort_packing_yn == 0){
                    $sortorder_packing = 'none';
                    $sortorder_packing_secondary = 'none';
                }

                /*************************** END PDF PAGE CONFIG *******************************/
                $logo_nudge = explode(',', $this->_getConfig('page_logo_nudge', '0,0', false, $wonder, $store_id));
                /*************************** BEGIN PDF GLOBAL PAGE CONFIG *******************************/
                $this->setGlobalPageConfig($order->getStore()->getId());
                /*************************** END PDF GLOBAL PAGE CONFIG *******************************/

            /*************************** New PDF PER Item *******************************/
            do{
                /*************************** BEGIN TO PRINT ************************************/
                $min_bottom_y = array();
                $keep_supplier_order = false;
                $keep_supplier_login = true;
                if (array_search($supplier, $supplier_order_ids[$order_id]) !== false)
                    $keep_supplier_order = true;
                if (isset($supplier_login) && ($supplier_login != "") && $supplier_login != strtolower($supplier))
                    $keep_supplier_login = false;
                if ((isset($sku_supplier_item_action_master[$order_id]) && $sku_supplier_item_action_master[$order_id] == 'keep' && $keep_supplier_order && $keep_supplier_login) || ($split_supplier_yn == 'no')) {
                    if ($first_page_yn == 'n') {
                        $page = $this->nooPage($this->_packingsheet['page_size']);
                        $number_pages ++;
                    } else $first_page_yn = 'n';
                    $padded_left -= 2;
                    if (($logo_position == 'left' ) && ($this->_packingsheet['page_size'] == 'letter')) {
                        $x1 = $padded_left;
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1]);
                        $x2 = ($padded_left + $logo_maxdimensions[0]);
                        $y2 = ($page_top - 5);
                    } elseif (($logo_position == 'left' ) && (($this->_packingsheet['page_size'] == 'a4') || ($this->_packingsheet['page_size'] == 'a5-landscape'))) {
                        $x1 = $padded_left;
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1]);
                        $x2 = ($padded_left + $logo_maxdimensions[0]);
                        $y2 = ($page_top - 5);
                    } elseif (($logo_position == 'left'  ) && ($this->_packingsheet['page_size'] == 'a5-portrait')) {
                        $x1 = $padded_left;
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1]);
                        $x2 = ($padded_left + $logo_maxdimensions[0]);
                        $y2 = ($page_top - 5);
                    } elseif (($logo_position == 'right') && ($this->_packingsheet['page_size'] == 'letter')) {
                        $x1 = ($padded_right - $logo_maxdimensions[0]);
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1]);
                        $x2 = $padded_right;
                        $y2 = ($page_top - 5);
                    } elseif (($logo_position == 'right') && (($this->_packingsheet['page_size'] == 'a4') || ($this->_packingsheet['page_size'] == 'a5-landscape'))) {
                        $x1 = ($padded_right - 289 + $logo_nudge[0]);
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1] + $logo_nudge[1]);
                        $x2 = $padded_right;
                        $y2 = ($page_top - 5);
                    } elseif ($logo_position == 'fullwidth') {
                        $x1 = 0;
                        $y1 = ($page_top - 5 - $logo_maxdimensions[1]); //784; 41?
                        $x2 = $logo_maxdimensions[0];
                        $y2 = ($page_top - 5); //825;
                        $logo_maxdimensions[2] = 'fullwidth';
                    }


                    /***************PRINTING BACKGROUND PAGE****************/
                    if ($page_background_image_yn == 1) {
                        $sub_folder = 'background_pack';
                        $option_group = 'wonder';
                        if ($wonder != 'wonder') {
                            $sub_folder = 'background_invoice';
                            $option_group = 'wonder_invoice';
                        }
                        $suffix_group = 'page_background_image';
                        $this->printBackGroundImage($page, $store_id, $page_background_image_yn, $page_top, $full_page_width, $page_background_position,$sub_folder, $option_group, $suffix_group, $padded_left, $page_top - 5, $page_background_nudge, $page_background_resize);
                    }
                    $case_rotate = $this->_getConfig('case_rotate_address_label',0, false, $wonder, $store_id);
                    $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $store_id));

                    $pickpack_headerbar_yn = trim($this->_getConfig('pickpack_headerbar_yn', '1', false, $wonder, $store_id));
                    $items_header_top_firstpage = $orderIdXY[1];
                    /******Set language*******/
                    $choose_language_display = $this->_getConfig('choose_language_display', 'l_login', false, "general", $store_id);
                    if($choose_language_display == "l_store"){
                        $locale = Mage::getStoreConfig('general/locale/code', $order_storeId);
                        Mage::app()->getLocale()->setLocaleCode($locale);
                        Mage::getSingleton('core/translate')->setLocale($locale)->init('adminhtml', true);
                    }
                    # BARCODE
                    if ($showBarCode) {

                        $config_values['barcode_type'] = $barcode_type;
                        $config_values['font_family_barcode'] = $font_family_barcode;
                        $config_values['barcode_nudge'] = $barcode_nudge;
                        $config_values['black_color'] = $black_color;
                        $config_values['padded_right'] = $padded_right;
                        $config_values['font_size_body'] = $this->_general['font_size_body'];
                        $barcode_text = '';
                        if($showBarCode == 1)
                            $barcode_text = $order->getRealOrderId();
                        else
                        if($showBarCode == 2)
                        {
                            if ($order->hasInvoices()) {
                                $invIncrementIDs = array();
                                foreach ($order->getInvoiceCollection() as $inv) {
                                    $invIncrementIDs[] = $inv->getIncrementId();
                                }
                                $barcode_text = implode(',',$invIncrementIDs);
                            }
                        }
                        else
                            if($showBarCode == 3)
                                $barcode_text  = $this->getMarketPlaceId($order);
                        if($barcode_text != '')
                            $this->showTopBarcode($page,$barcode_text,$config_values,$y2,$padded_right);
                        $page->setFillColor($black_color);
                        $this->_setFontRegular($page, $this->_general['font_size_body']);
                    }
                    if ($invoice_title_2_yn == 1 && $invoice_title_2 != '') {
                        $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        $page->drawText($invoice_title_2, $title2XY[0], $title2XY[1], 'UTF-8');
                    }
                    $store = null;

                    if ($order->getStoreId()) {
                        $store = $order->getStoreId();
                    }

                    //this value use to get index for header page to print gift warp icon
                    $current_header_page_index = array_search($page, $pdf->pages);

                    /***************************PRINTING 1 HEADER LOGO *******************************/
                    $start_page_for_order = count($pdf->pages) -1;
                    if ($show_top_logo_yn == 1) {
                        $sub_folder = 'logo_pack';
                        $option_group = 'wonder';
                        if ($wonder != 'wonder') {
                            $sub_folder = 'logo_invoice';
                            $option_group = 'wonder_invoice';
                        }

                        /*************************** PRINT HEADER LOGO *******************************/
                        $sub_folder = 'logo_pack';
                        $option_group = 'wonder';

                        if ($wonder != 'wonder') {
                            $sub_folder = 'logo_invoice';
                            $option_group = 'wonder_invoice';
                        }
                        $suffix_group = '/pack_logo';
                        $y1 = $this->printHeaderLogo($page, $store_id, $show_top_logo_yn, $page_top, $logo_maxdimensions, $sub_folder, $option_group, $suffix_group, $x1, $y2);
                        if($show_top_logo_yn == 1 && $y1 < $page_top + 5 - $logo_maxdimensions[1]){
                            $datebar_start_y = ($y1 - 5);
                            if ($datebar_start_y < $orderIdXY[1]) $orderIdXY[1] = $datebar_start_y;
                        }
                        $orderIdXY[1] += 5;
                        /*************************** END PRINT HEADER LOGO ***************************/
                    }else
                        $orderIdXY[1] = $page_top;

                    $check_extra_space_header_bar = $orderIdXY[1];
                    /*************************** END HEADER LOGO *******************************/

                    $show_custom_declaration_nudge = explode(',',$this->_getConfig('show_custom_declaration_nudge','280,300', true, $wonder, $store_id));

                    $case_rotate = $this->_getConfig('case_rotate_address_label',0, false, $wonder, $store_id);
                    $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $store_id));

                    $pickpack_headerbar_yn = trim($this->_getConfig('pickpack_headerbar_yn', '1', false, $wonder, $store_id));
                    $items_header_top_firstpage = $orderIdXY[1];
                    /******Set language*******/
                    $choose_language_display = $this->_getConfig('choose_language_display', 'l_login', false, "general", $store_id);
                    if($choose_language_display == "l_store"){
                        $locale = Mage::getStoreConfig('general/locale/code', $order->getStore()->getId());
                        Mage::app()->getLocale()->setLocaleCode($locale);
                        Mage::getSingleton('core/translate')->setLocale($locale)->init('adminhtml', true);
                    }
                    /*************************** PRINTING 2 HEADER STORE ADDRESS *******************************/
                    $company_address_nudge = explode(',', $this->_getConfig('company_address_nudge', '0,0', false, $wonder, $store_id));
                    $company_vert_line = true;
                    $company_vert_line_width = 3;
                    $this->y = $y2 - $font_size_company;
                    if (($company_address_yn == 1) || ($company_address_yn == 'yesgroup') || ($this->_packingsheet['pickpack_return_address_yn'] == 1) || ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup')) {
                        //New TODO Moo: company address
                        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy') && is_object($order->getShippingAddress()) && $order->getShippingAddress()->getCountryId()) {
                            $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));

                            $us_array = array('usa', 'u.s.a.', 'united states', 'united states of america');
                            $eu_array = array('uk', 'united kingdom', 'england', 'great britain', 'belgium', 'bulgaria', 'czech republic', 'denmark', 'germany', 'estonia', 'ireland', 'greece', 'spain', 'france', 'italy', 'cyprus', 'latvia', 'lithuania', 'luxembourg', 'hungary', 'malta', 'netherlands', 'austria', 'poland', 'portugal', 'romania', 'slovenia', 'slovakia', 'finland', 'sweden');
                            $non_eu_array = array('albania', 'andorra', 'armenia', 'azerbaijan', 'belarus', 'bosnia and herzegovina', 'georgia', 'liechtenstein', 'moldova', 'monaco', 'norway', 'russia', 'san marino', 'serbia', 'switzerland', 'ukraine', 'vatican', 'vatican city state');

                            if (in_array(strtolower($customer_country), $eu_array)) {
                                if ($company_address_yn == 'yesgroup') $company_address = $company_address_group2; //EU
                                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $return_address = $return_address_group2; //EU
                            } elseif (in_array(strtolower($customer_country), $non_eu_array)) {
                                if ($company_address_yn == 'yesgroup') $company_address = $company_address_group2; // non_eu
                                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $return_address = $return_address_group2; // non_eu
                            } elseif (in_array(strtolower($customer_country), $us_array)) {
                                if ($company_address_yn == 'yesgroup') $company_address = $company_address_group1; // USA
                                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $return_address = $return_address_group1; // USA
                            } elseif (stripos('australia', $customer_country) !== FALSE) {
                                if ($company_address_yn == 'yesgroup') $company_address = $company_address_group3; //AUS
                                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $return_address = $return_address_group3; //AUS
                            }
                        }

                        if (($company_address != '') && (($company_address_yn == 1) || ($company_address_yn == 'yesgroup'))) {
                            $this->_setFont($page, $font_style_company, $font_size_company, $font_family_company, $this->_general['non_standard_characters'], $font_color_company);

                            $line_height = 0;
                            $company_x = (320 + $company_address_x_nudge);
                            if (($page_template == 'mailer')) {
                                $company_x = $padded_left;
                                $company_vert_line = false;
                            }
                            else
                                if(($logo_position == 'right'))
                                {
                                    $company_x = $padded_left + $company_address_nudge[0];

                                }

                            $y_temp_2 = $this->y;
                            foreach (explode("\n", $company_address) as $value) {
                                $page->drawText(trim(strip_tags($value)), $company_x, ($this->y - $line_height + $company_address_nudge[1]), 'UTF-8');
                                $line_height = ($line_height + $font_size_company);
                            }
                            $y_temp_1 = $y_temp_2 - $line_height + $font_size_company - 2;
                            $y_temp_2 = $y_temp_2 + $font_size_company;

                            if ($y_temp_1 > $y1 + 20)
                                $y_temp_1 = $y1 + 20;

                            if ($y_temp_2 < $y2)
                                $y_temp_2 = $y2;

                            $address_top_y = null;
                            if ($float_top_address_yn == 1) $float_top_address_y = ($this->y - ($line_height - ($this->_general['font_size_body'] * 2.5)));
                            if ($company_vert_line === true && (strtoupper($background_color_subtitles) != '#FFFFFF') ) {
                                if($logo_position == 'left')
                                {
                                    $company_vert_line_x1 = 304 + $company_address_nudge[0];
                                    $company_vert_line_x2 = 304 + $company_address_nudge[0] + $company_vert_line_width;
                                    $company_vert_line_y1 = $y_temp_1 + $company_address_nudge[1];
                                    $company_vert_line_y2 = $y_temp_2 + $company_address_nudge[1];

                                }
                                else
                                if($logo_position == 'right')
                                {
                                    $company_vert_line_x1 = $x1-10-$company_vert_line_width;
                                    $company_vert_line_x2 = $x1-10;
                                    $company_vert_line_y1 = $y_temp_1 + $company_address_nudge[1];
                                    $company_vert_line_y2 = $y_temp_2 + $company_address_nudge[1];

                                }
                                $page->setFillColor($background_color_subtitles_zend);
                                $page->setLineColor($background_color_subtitles_zend);
                                $page->setLineWidth(0.5);

                                $page->drawRectangle($company_vert_line_x1, $company_vert_line_y1, $company_vert_line_x2, $company_vert_line_y2);
                                if($check_extra_space_header_bar > $company_vert_line_y1)
                                    $check_extra_space_header_bar = $company_vert_line_y1;
                            }
                            $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        }
                    } else $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                    if(isset($check_extra_space_header_bar))
                        if(($orderIdXY[1] + $this->_general['font_size_subtitles'] + 2 )> $check_extra_space_header_bar -10)
                            $orderIdXY[1] = $check_extra_space_header_bar - 10;

                    /*************************** END HEADER STORE ADDRESS *******************************/
                    $invoice_title_temp = $invoice_title;

                    $invoice_title_temp = explode("\n", $invoice_title_temp);

                    $invoice_title_linebreak = count($invoice_title_temp);

                    /****************************PRINTING 3 HEADER TITLE BAR BEFORE SHIPPING ADDRESS*****************************/
                    if ($pickpack_headerbar_yn == 1) {
                        if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                            $page->setFillColor($background_color_subtitles_zend);
                            $page->setLineColor($background_color_subtitles_zend);
                            $page->setLineWidth(0.5);
                            if ($fill_product_header_yn == 1) {

                                switch ($fill_bars_options) {
                                    case 0:
                                        $page->drawRectangle($padded_left, ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ceil($orderIdXY[1] + $this->_general['font_size_subtitles'] + 2));
                                        break;
                                    case 1:
                                        if ($invoice_title_linebreak <= 1) {
                                            $bottom_fillbar = ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2)) - $fillbar_padding[1];
                                            $top_fillbar = ceil($orderIdXY[1] + $this->_general['font_size_subtitles'] + 2) + $fillbar_padding[0];
                                            if(isset($line_widths[0]) && $line_widths[0] > 0){
                                                //$page->setLineWidth($line_widths[0]);
                                                $page->setLineWidth(0.5);
                                                $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                            }
                                            if(isset($line_widths[1]) && $line_widths[1] > 0){
                                                //$page->setLineWidth($line_widths[1]);
                                                $page->setLineWidth(0.5);
                                                $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                            }
                                        }
                                        break;
                                    case 2:
                                        break;
                                }

                            } else {
                                switch ($fill_bars_options) {
                                    case 1:
                                        $page->drawRectangle($padded_left, ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2) - 3), $padded_right, ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2) - 3));
                                        break;
                                    case 2:
                                        if ($invoice_title_linebreak <= 1) {
                                            $bottom_fillbar = ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2) - 3) - $fillbar_padding[1];
                                            $top_fillbar = ceil($orderIdXY[1] - ($this->_general['font_size_subtitles'] / 2) - 3) + $fillbar_padding[0];
                                            if($line_widths[0] > 0){
                                                //$page->setLineWidth($line_widths[0]);
                                                $page->setLineWidth(0.5);
                                                $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                            }
                                            if($line_widths[1] > 0){
                                                //$page->setLineWidth($line_widths[1]);
                                                $page->setLineWidth(0.5);
                                                $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                            }
                                        }
                                        break;
                                    case 3:
                                        break;
                                }
                            }

                        }


                        /**DATE    */
                        // header
                        $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $date_format);
                        $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                        $invoice_number_display = '';
                        $order_number_display = '';

                        foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                            if ($_tmpInvoice->getIncrementId()) {
                                if ($invoice_number_display != '') $invoice_number_display .= ',';
                                $invoice_number_display .= $_tmpInvoice->getIncrementId();
                            }
                            break;
                        }

                        if ($order_or_invoice == 'order') $order_number_display = $order->getRealOrderId();
                        elseif ($order_or_invoice == 'invoice' && $invoice_number_display != '') {
                            $order_number_display = $invoice_number_display;
                        }

                        $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        $orderIdXY[1] -= $mailer_padding[1];

                        if ($split_supplier_yn == 'pickpack') {
                            $order_date .= '      Supplier: ' . $supplier;
                            $title_date_xpos -= 50;
                        }

                        $date_y = null;
                        if ($title_date_xpos == 'auto' && $page_template != 'mailer') {
                            $order_number_display .= '   ' . $order_date;
                        } elseif ($page_template != 'mailer') {
                            $date_y = $orderIdXY[1];
                        }

                        if ($page_template == 'mailer') {
                            $orderIdXY[1] += ($font_size_company * 2);
                            $orderIdXY[0] = $padded_left;
                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] * 1.4), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        }
//                         $page_title_nuge[0] = 425;

                        if ($invoice_title != '') {
                            // If small logo, make sure Invoice Title/Date start below height of raised address (if address has been brought up)
                            if ($float_top_address_yn == 1 && (($has_billing_address == 1) || ($has_shipping_address == 1)) && ($orderIdXY[1] > ($page_top - ($this->_general['font_size_body'] * 15)))) $orderIdXY[1] = ($page_top - ($this->_general['font_size_body'] * 15));
                            $title_start_X = $orderIdXY[0] + $page_title_nuge[0];
                            $title_start_Y = $orderIdXY[1] + $page_title_nuge[1];
                            $date_y = $orderIdXY[1];
                            if ($title_invert_color != 1) {
                                ////Order date. n/a if empty
                                $order_date_title = 'n/a';
                                $dated_title = $order->getCreatedAt();
                                $dated_timestamp = strtotime($dated_title);

                                if ($dated_title != '') {
                                    $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                                    $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);

                                } else {
                                    //This field is empty.
                                    $from_date = "{{if order_date}}";
                                    $end_date = "{{endif order_date}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }

                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);

                                }
                                //////////// Invoice date  n/a if empty
                                if ($order->getCreatedAtStoreDate()) {
                                    $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $date_format);
                                    $invoice_title = str_replace("{{if invoice_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_date}}", '', $invoice_title);
                                } else {
                                    //This field is empty.
                                    $from_date = "{{if invoice_date}}";
                                    $end_date = "{{endif invoice_date}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }
                                    $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);
                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);
                                }

                                if ($invoice_number_display == '') {
                                    //This field is empty.
                                    $from_date = "{{if invoice_id}}";
                                    $end_date = "{{endif invoice_id}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }
                                    $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);
                                } else {
                                    $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                                }

                                /*****  Get Warehouse information ****/
                                if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                                    $warehouse_helper = Mage::helper('warehouse');
                                    $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                                    $resource = Mage::getSingleton('core/resource');
                                    /**
                                     * Retrieve the read connection
                                     */
                                    $readConnection = $resource->getConnection('core_read');
                                    $query = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse") . ' WHERE entity_id=' . $order->getData('entity_id');
                                    $warehouse_stock_id = $readConnection->fetchOne($query);
                                    if ($warehouse_stock_id) {
                                        $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                                        $warehouse_title = ($warehouse->getData('title'));
                                    } else {
                                        $warehouse_title = '';
                                    }
                                } else {
                                    $warehouse_title = '';
                                }

                                $from_date = "{{if warehouse}}";
                                $end_date = "{{endif warehouse}}";
                                $from_date_pos = strpos($invoice_title, $from_date);
                                if ($from_date_pos !== false) {
                                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                    $date_length = $end_date_pos - $from_date_pos;
                                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                    $invoice_title = str_replace($date_str, '', $invoice_title);
                                } else {
                                    $invoice_title = str_replace("{{if warehouse}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif warehouse}}", '', $invoice_title);
                                }
                                unset($from_date);
                                unset($end_date);
                                unset($from_date_pos);
                                unset($end_date_pos);
                                unset($date_length);
                                unset($date_str);
                                /*****  Get Warehouse information ****/
                                if ($date_format_strftime !== true) $printing_date_title = date($date_format, Mage::getModel('core/date')->timestamp(time()));
                                else $printing_date_title = strftime($date_format, Mage::getModel('core/date')->timestamp(time()));
                                if ($printing_date_title != '') {
                                    $invoice_title = str_replace("{{if printing_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif printing_date}}", '', $invoice_title);
                                }

                                $order_number_display_title = $order->getRealOrderId();
                                if ($order_number_display_title != '') {
                                    $invoice_title = str_replace("{{if order_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_id}}", '', $invoice_title);
                                }

                                //market place order ID
                                $marketPlaceOrderId = $this->getMarketPlaceId($order);
                                if($marketPlaceOrderId != ''){
                                    $invoice_title = str_replace("{{if marketplace_order_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif marketplace_order_id}}", '', $invoice_title);
                                }
                                //ebay sale number
                                $ebay_sale_number = $this->getEbaySaleNumber($order);
                                if($ebay_sale_number != ''){
                                    $invoice_title = str_replace("{{if ebay_sales_number}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif ebay_sales_number}}", '', $invoice_title);
                                }
                                $fastwayColour = !empty($order->getFastwayColour()) ? $order->getFastwayColour() :'';
                               /* if($fastwayColour !== ''){
                                    $invoice_title = str_replace("{{if fastway_colour}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif fastway_colour}}", '', $invoice_title);
                                } */
                                
                                $arr_1 = array('{{order_date}}', '{{invoice_date}}', '{{printing_date}}', '{{order_id}}', '{{invoice_id}}', '{{marketplace_order_id}}', '{{ebay_sales_number}}','{{fastway_colour}}');

                                $arr_2 = array($order_date_title, $invoice_date_title, $printing_date_title, $order_number_display_title, $invoice_number_display, $marketPlaceOrderId, $ebay_sale_number,$fastwayColour);

                                $invoice_title_print = str_replace($arr_1, $arr_2, $invoice_title);

                                $order_number_display = $invoice_title_print;

                                $invoice_title_temp = $order_number_display;
                                $invoice_title_temp = explode("\n", $invoice_title_temp);
                                $title_line_count = 0;
                                foreach ($invoice_title_temp as $title_line) {
                                    $page->drawText(trim($title_line), $title_start_X, $title_start_Y - $this->_general['font_size_subtitles'] * $title_line_count, 'UTF-8');
                                    $title_line_count++;
                                }

                            } elseif ($title_invert_color == 1) {
                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], '#FFFFFF');
                                $page->drawText($invoice_title, $title_start_X, $orderIdXY[1], 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                $page->drawText($order_number_display, ($title_start_X + (($this->_general['font_size_subtitles'] / 2) * strlen($invoice_title))), $orderIdXY[1], 'UTF-8');
                            }
                        }
                        else {
                            $page->drawText($order_number_display, $orderIdXY[0], $orderIdXY[1], 'UTF-8');
                        }


                    }
                    /***************************END HEADER TITLE BAR BEFORE SHIPPING ADDRESS*****************************/

                    /***************** CUSTOM TO PRINT SHIPPING ADDRESS BACKGROUND********************/
                    if($case_rotate > 0)
                        $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                    $shipping_method_raw_temp = '';
                    $shipping_method_temp = '';
                    $scale = $this->_getConfig('top_shipping_address_background_yn_scale', 0, false, $wonder, $store_id);
                    $this->showShippingAddresBackground($order, $page_top, $wonder, $store_id, $page, $padded_left, $scale);
                    if($case_rotate > 0)
                        $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                    /*****************END CUSTOM TO PRINT SHIPPING ADDRESS BACKGROUND********************/

                    $show_custom_declaration_dimension = explode(',',$this->_getConfig('show_custom_declaration_dimension','279,245', false, $wonder, $store_id));




                    /*************************** PRINTING SHIPPING AND BILLING ADDRESS *******************************/
                    $customer_email = '';
                    $shipping_taxvat = '';
                    $customer_phone = '';
                    $customer_fax = '';
                    $customer_company = '';
                    $customer_name = '';
                    $customer_firstname = '';
                    $customer_lastname = '';
                    $customer_name = '';
                    $customer_city = '';
                    $customer_postcode = '';
                    $customer_region = '';
                    $customer_region_code = '';
                    $customer_prefix = '';
                    $customer_suffix = '';
                    $customer_country = '';
                    $customer_street1 = '';
                    $customer_street2 = '';
                    $customer_street3 = '';
                    $customer_street4 = '';
                    $customer_street5 = '';
                    $customer_street6 = '';
                    $customer_street7 = '';
                    $customer_street8 = '';
                    $billing_taxvat = '';

                    if ($has_shipping_address !== false) {
                        if ($order->getShippingAddress()->getFax()) $customer_fax = trim($order->getShippingAddress()->getFax());
                        if($billing_phone_yn_in_shipping_details && !$billing_details_yn){
                            $customer_phone = trim($order->getBillingAddress()->getTelephone());
                        }
                        else{
                            if ($order->getShippingAddress()->getTelephone())
                                $customer_phone = trim($order->getShippingAddress()->getTelephone());
                        }
                        if ($order->getShippingAddress()->getCompany()) $customer_company = trim($order->getShippingAddress()->getCompany());
                        if ($order->getShippingAddress()->getName()) $customer_name = trim($order->getShippingAddress()->getName());
                        if ($order->getShippingAddress()->getFirstname()) $customer_firstname = trim($order->getShippingAddress()->getFirstname());
                        if ($order->getShippingAddress()->getMiddlename()) $customer_middlename = trim($order->getShippingAddress()->getMiddlename());
                        if ($order->getShippingAddress()->getLastname()) $customer_lastname = trim($order->getShippingAddress()->getLastname());
                        if ($order->getShippingAddress()->getCity()) $customer_city = trim($order->getShippingAddress()->getCity());
                        if ($order->getShippingAddress()->getPostcode()) $customer_postcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
                        if ($order->getShippingAddress()->getRegion()) $customer_region = trim($order->getShippingAddress()->getRegion());
                        if ($order->getShippingAddress()->getRegionCode()) $customer_region_code = trim($order->getShippingAddress()->getRegionCode());
                        if ($order->getShippingAddress()->getPrefix()) $customer_prefix = trim($order->getShippingAddress()->getPrefix());
                        if ($order->getShippingAddress()->getSuffix()) $customer_suffix = trim($order->getShippingAddress()->getSuffix());
                        if ($order->getShippingAddress()->getStreet1()) $customer_street1 = trim($order->getShippingAddress()->getStreet1());
                        if ($order->getShippingAddress()->getStreet2()) $customer_street2 = trim($order->getShippingAddress()->getStreet2());
                        if ($order->getShippingAddress()->getStreet3()) $customer_street3 = trim($order->getShippingAddress()->getStreet3());
                        if ($order->getShippingAddress()->getStreet4()) $customer_street4 = trim($order->getShippingAddress()->getStreet4());
                        if ($order->getShippingAddress()->getStreet5()) $customer_street5 = trim($order->getShippingAddress()->getStreet5());
                        if ($order->getShippingAddress()->getStreet5()) $customer_street6 = trim($order->getShippingAddress()->getStreet6());
                        if ($order->getShippingAddress()->getStreet5()) $customer_street7 = trim($order->getShippingAddress()->getStreet7());
                        if ($order->getShippingAddress()->getStreet5()) $customer_street8 = trim($order->getShippingAddress()->getStreet8());

                        if (Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId())) {
                            $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));
                        }
                    }
                    if ($order->getCustomerEmail()) $customer_email = trim($order->getCustomerEmail());

                    $billing_email = '';
                    $billing_phone = '';
                    $billing_company = '';
                    $billing_name = '';
                    $billing_firstname = '';
                    $billing_lastname = '';
                    $billing_city = '';
                    $billing_postcode = '';
                    $billing_region = '';
                    $billing_region_code = '';
                    $billing_prefix = '';
                    $billing_suffix = '';
                    $billing_country = '';
                    $billing_street1 = '';
                    $billing_street2 = '';
                    $billing_street3 = '';
                    $billing_street4 = '';
                    $billing_street5 = '';

                    if ($billing_details_yn == 1) {
                        $billingaddress = $order->getBillingAddress();
                        if ($billing_tax_details_yn == 1) {
                            if ($billingaddress->getData('vat_id')) {
                                $billing_tax_details_title = $this->_getConfig('billing_tax_details_title', '', false, $wonder, $store_id); //no trim so can be positioned
                                $billing_taxvat = $billing_tax_details_title . ' ' . trim($billingaddress->getData('vat_id'));
                            }
                        }

                        $billing_middlename = '';
                        if ($billingaddress->getTelephone()) $billing_phone = trim($billingaddress->getTelephone());
                        if ($billingaddress->getCompany()) $billing_company = trim($billingaddress->getCompany());
                        if ($billingaddress->getName()) $billing_name = trim($billingaddress->getName());
                        if ($billingaddress->getFirstname()) $billing_firstname = trim($billingaddress->getFirstname());
                        if ($billingaddress->getMiddlename()) $billing_middlename = trim($billingaddress->getMiddlename());
                        if ($billingaddress->getLastname()) $billing_lastname = trim($billingaddress->getLastname());
                        if ($billingaddress->getCity()) $billing_city = trim($billingaddress->getCity());
                        if ($billingaddress->getPostcode()) $billing_postcode = trim(strtoupper($billingaddress->getPostcode()));
                        if ($billingaddress->getRegion()) $billing_region = trim($billingaddress->getRegion());
                        if ($billingaddress->getRegionCode()) $billing_region_code = trim($billingaddress->getRegionCode());
                        if ($billingaddress->getPrefix()) $billing_prefix = trim($billingaddress->getPrefix());
                        if ($billingaddress->getSuffix()) $billing_suffix = trim($billingaddress->getSuffix());
                        if ($billingaddress->getStreet1()) $billing_street1 = trim($billingaddress->getStreet1());
                        if ($billingaddress->getStreet2()) $billing_street2 = trim($billingaddress->getStreet2());
                        if ($billingaddress->getStreet3()) $billing_street3 = trim($billingaddress->getStreet3());
                        if ($billingaddress->getStreet4()) $billing_street4 = trim($billingaddress->getStreet4());
                        if ($billingaddress->getStreet5()) $billing_street5 = trim($billingaddress->getStreet5());
                        if ($countryTranslation = Mage::app()->getLocale()->getCountryTranslation($billingaddress->getCountryId())) {
                            $billing_country = trim($countryTranslation);
                        }

                        $billing_address = array();
                        $if_contents = array();
                        $billing_address['street'] = '';
                        $billing_address['street1'] = $billing_street1;
                        $billing_address['street2'] = $billing_street2;
                        $billing_address['street3'] = $billing_street3;
                        $billing_address['street4'] = $billing_street4;
                        $billing_address['street5'] = $billing_street5;
                        $billing_address['company'] = $billing_company;
                        $billing_address['name'] = $billing_name;
                        $billing_address['firstname'] = $billing_firstname;
                        $billing_address['middlename'] = $billing_middlename;
                        $billing_address['lastname'] = $billing_lastname;
                        $billing_address['name'] = $billing_name;
                        $billing_address['name'] = trim(preg_replace('~^' . $billing_address['company'] . '~i', '', $billing_address['name']));
                        $billing_address['city'] = $billing_city;
                        $billing_address['postcode'] = $billing_postcode;
                        $billing_address['region_full'] = $billing_region;
                        $billing_address['region_code'] = $billing_region_code;

                        if ($billing_region_code != '') {
                            $billing_address['region'] = $billing_region_code;
                        } else {
                            $billing_address['region'] = $billing_region;
                        }
                        $billing_address['prefix'] = $billing_prefix;
                        $billing_address['suffix'] = $billing_suffix;
                        $billing_address['country'] = $billing_country;
                        if ($address_countryskip != '') {
                           $address_billing_countryskip = array();
                           foreach( explode(',',$address_countryskip) as $skip_country ){
                                if ($skip_country == 'usa' || $skip_country == 'united states' || $skip_country == 'united states of america') {
                                    $address_billing_countryskip = array('usa', 'united states of america', 'united states');
                                    break;
                                }
                                if( strtolower($skip_country) == strtolower($billing_address['country']) ){
                                    $address_billing_countryskip = array($skip_country);
                                    break;
                                }
                                /*TODO filter city if country = singapore or monaco*/
                                if (!is_array($skip_country) && (strtolower($skip_country) == "singapore" || strtolower($skip_country) == "monaco")) {
                                    $billing_address['city'] = str_ireplace($skip_country, '', $billing_address['city']);
                                }
                          }
                          $billing_address['country'] = str_ireplace($address_billing_countryskip, '', $billing_address['country']);
                        }
                        $i = 0;
                        while ($i < 10) {
                            if ($order->getBillingAddress()->getStreet($i) && !is_array($order->getBillingAddress()->getStreet($i))) {
                                $value = trim($order->getBillingAddress()->getStreet($i));
                                $max_chars = 20;
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($this->_general['font_size_body'] * 0.8);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / 10;
                                $max_chars = round(($orderdetailsX - 160) / $char_width);
                                // wordwrap characters
                                $value = wordwrap($value, $max_chars, "\n", false);
                                $token = strtok($value, "\n");
                                while ($token != false) {
                                    if (trim(str_replace(',', '', $token)) != '') {
                                        $billing_address['street'] .= trim($token) . "\n";
                                    }
                                    $token = strtok("\n");
                                }
                            }
                            $i++;
                        }

                        $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format);
                        $address_format_set = $this->getArrayShippingAddress($billing_address, $capitalize_label_yn, $address_format_set);

                        if ($billing_tax_details_yn == 1 && $billing_taxvat != '') $address_format_set .= '|||||' . $billing_taxvat;
                        $address_format_set = trim(str_replace(array('||', '|'), "\n", trim($address_format_set)));
                        $address_format_set = str_replace("\n\n", "\n", $address_format_set);

                        $billingAddressArray = explode("\n", $address_format_set);
                        if($billing_phone_yn == 1)
                            array_push($billingAddressArray, ("T: " . $billing_phone));
                        $billing_line_count = (count($billingAddressArray) - 1);
                    }

                    $shipping_address = array();
                    $if_contents = array();
                    $shipping_address['company'] = $customer_company;
                    $shipping_address['firstname'] = $customer_firstname;
                    if (isset($customer_middlename) && (strlen($customer_middlename) > 0))
                        $shipping_address['middlename'] = $customer_middlename;
                    else
                        $shipping_address['middlename'] = '';
                    $shipping_address['lastname'] = $customer_lastname;
                    $shipping_address['name'] = $customer_name;
                    $shipping_address['name'] = trim(preg_replace('~^' . $shipping_address['company'] . '~i', '', $shipping_address['name']));
                    $shipping_address['city'] = $customer_city;
                    $shipping_address['postcode'] = $customer_postcode;
                    $shipping_address['region_full'] = $customer_region;
                    $shipping_address['region_code'] = $customer_region_code;
                    if ($customer_region_code != '') {
                        $shipping_address['region'] = $customer_region_code;
                    } else {
                        $shipping_address['region'] = $customer_region;
                    }
                    $shipping_address['prefix'] = $customer_prefix;
                    $shipping_address['suffix'] = $customer_suffix;
                    $shipping_address['country'] = $customer_country;
                    $shipping_address['street'] = '';
                    $shipping_address['street1'] = $customer_street1;
                    $shipping_address['street2'] = $customer_street2;
                    $shipping_address['street3'] = $customer_street3;
                    $shipping_address['street4'] = $customer_street4;
                    $shipping_address['street5'] = $customer_street5;
                    $shipping_address['street6'] = $customer_street6;
                    $shipping_address['street7'] = $customer_street7;
                    $shipping_address['street8'] = $customer_street8;


                    if ($address_countryskip != '') {
                       $address_shipping_countryskip = array();
                       foreach( explode(',',$address_countryskip) as $skip_country ){
                            if ($skip_country == 'usa' || $skip_country == 'united states' || $skip_country == 'united states of america') {
                                $address_shipping_countryskip = array('usa', 'united states of america', 'united states');
                                break;
                            }

                            if( strtolower($skip_country) == strtolower($shipping_address['country']) ){
                                $address_shipping_countryskip = array($skip_country);
                                break;
                            }
                            /*TODO filter city if country = singapore or monaco*/
                            if ($skip_country == "singapore" || $skip_country == "monaco") {
                                $shipping_address['city'] = str_ireplace($skip_country, '', $shipping_address['city']);
                                break;
                            }
                      }
                        $shipping_address['country'] = str_ireplace($address_shipping_countryskip, '', $shipping_address['country']);
                    }

                    if ($has_shipping_address !== false) {
                        $i = 0;
                        while ($i < 10) {
                            if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
                                $value = trim($order->getShippingAddress()->getStreet($i));

                                $max_chars = 20;
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($this->_general['font_size_body'] * 0.8);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / 10;
                                $max_chars = round(($orderdetailsX - 160) / $char_width);
                                // wordwrap characters
                                $value = wordwrap($value, $max_chars, "\n", false);
                                $token = strtok($value, "\n");
                                while ($token !== false) {
                                    if (trim(str_replace(',', '', $token)) != '') {
                                        $shipping_address['street'] .= trim($token) . "\n";
                                    }
                                    $token = strtok("\n");
                                }
                            }
                            $i++;
                        }
                    }

                    $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format);
                    $address_format_set_2 = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format);
                    if (strpos($order->getData('shipping_method'),'storepickup') !== false){
                         $address_format_set = '{if name}{name},|{/if}';
                         $address_format_set_2 = '{if name}{name},|{/if}';
                    }
                    $address_format_set = $this->getArrayShippingAddress($shipping_address, $capitalize_label_yn, $address_format_set);
                    $address_format_set_2 = $this->getArrayShippingAddress($shipping_address, $capitalize_label2_yn, $address_format_set_2);//fro bottom shipping address

                    $shippingAddressArray = explode("\n", $address_format_set);
                    $shippingAddressArrayBottom = explode("\n", $address_format_set_2);
                    $last_line_index = count($shippingAddressArrayBottom);
                    $last_line_index_top = count($shippingAddressArray);

                    if (($customer_phone_yn != 'no') && ($customer_phone != '') && (strlen($customer_phone) > 5))
                    {
                    	if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yesdetails')
	                        array_push($shippingAddressArray, ("T: " . $customer_phone));
						if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yeslabel')
                        	array_push($shippingAddressArrayBottom, ("T: " . $customer_phone));
                    }

                    if ($customer_email_yn != 'no' && $customer_email != '') {
                    	if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yesdetails')
							array_push($shippingAddressArray, ("E: " . $customer_email));
                        if (($customer_email_yn == 'yes' || $customer_email_yn == 'yeslabel') && ($shipping_details_yn == 1)) {
                            array_push($shippingAddressArrayBottom, ("E: " . $customer_email));
                         }

                    }

                    $count = (count($shippingAddressArray));
                    $shipping_line_count = $count;
                    if (isset($billing_line_count) && ($billing_line_count > $shipping_line_count) && ($shipping_line_count > 1)) {
                        $shipping_line_count = $billing_line_count;
                    }

                    //Qrcode
                    // http://phpqrcode.sourceforge.net/examples/index.php?example=201
                    $filename = $PNG_TEMP_DIR.$order_id.'.png';
                    $errorCorrectionLevel = 'H';
                    $matrixPointSize = 6;
                    $filename = $PNG_TEMP_DIR.'orderId'.md5($order_id.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
                    $filename = $PNG_TEMP_DIR.'orderId'.$order_id.'.png';
                    //Order id, invoice id, shipping details.
                    $qrcode_string = $this->getQrcodeText($qrcode_pattern,$order);

                    if (!file_exists($filename))
                        QRcode::png($qrcode_string, $filename, $errorCorrectionLevel, $matrixPointSize, 3);

                    $show_barcode_str = 1;
                    if ($show_1st_qrcode == 1) {
                        $image = Zend_Pdf_Image::imageWithPath($filename);
                        $qr_x1 = $qrcode_1st_nudge[0];
                        $qr_x2 = $qrcode_1st_nudge[0] + 50;
                        $qr_y1= $qrcode_1st_nudge[1];
                        $qr_y2 = $qrcode_1st_nudge[1] +50;
                        $page->drawImage($image, $qr_x1 , $qr_y1, $qr_x2, $qr_y2);

                        if ($show_2nd_qrcode == 1) {
                            $qr_x1 = $qrcode_2nd_nudge[0];
                            $qr_x2 = $qrcode_2nd_nudge[0] + 50;
                            $qr_y1= $qrcode_2nd_nudge[1];
                            $qr_y2 = $qrcode_2nd_nudge[1] +50;
                            $page->drawImage($image, $qr_x1 , $qr_y1, $qr_x2, $qr_y2);
                        }
                    }

                    $address_left_x = $addressXY[0];
                    if ($float_top_address_yn == 0) $address_right_x = $orderdetailsX;
                    $email_X = $address_left_x + $address_pad[2];

                    if ($billing_details_position == 1 || $billing_details_position == 2) {
                        if($billing_details_position != 2)
                            $address_left_x = $orderdetailsX;
                        $address_right_x = $addressXY[0];
                    }
                    if ($pickpack_headerbar_yn == 1){
                        //$address_top_y = ($orderIdXY[1] - ($this->_general['font_size_subtitles'] * 2));
                        $address_top_y = ($orderIdXY[1] - $this->_general['font_size_subtitles']/2 - $vertical_spacing + 3);
                    }
                    else
                        $address_top_y = $orderIdXY[1];

                    //Dont need to move more for Top billing and shipping title.
                    if ($shipping_title == '' && ($billing_details_yn == 0 || $billing_title == '')) {
                        $address_top_y -= 10;
                    }

                    $top_y_left_colum = $address_top_y;
                    $top_y_right_colum = $address_top_y;
                    $address_title_left_x = $address_left_x;
                    $address_title_right_x = $address_right_x;

                    if ($shipping_billing_title_position == 'beside') {
                        $address_left_x = ($padded_left + ((strlen($shipping_title)) * $this->_general['font_size_subtitles'] * 0.5));
                        $address_title_left_x = $padded_left;
                        if ($page_template == '0') $address_title_left_x = $orderIdXY[0];

                        $address_right_x = $orderdetailsX;
                        $address_title_right_x = ($address_right_x - ((strlen($billing_title)) * $this->_general['font_size_subtitles'] * 0.5));

                        $address_left_x += 10;

                        $email_X = $address_left_x;

                        if ($billing_details_position == 1 || $billing_details_position == 2) {
                            if($billing_details_position != 2)
                                $address_left_x = $orderdetailsX;
                            $address_title_left_x = ($address_left_x - ((strlen($shipping_title)) * $this->_general['font_size_subtitles'] * 0.5));

                            if ($float_top_address_yn == 0) $address_right_x = ($padded_left + ((strlen($billing_title)) * $this->_general['font_size_subtitles'] * 0.5));
                            $address_title_right_x = $padded_left;
                            if ($page_template == '0') $address_title_right_x = $orderIdXY[0];
                        }
                    }

                    $this->_setFont($page, $font_style_shipping_billing_title, ($this->_general['font_size_body'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                    if ($page_template == 'mailer' || $page_template == 'bringup') {
                        $address_top_y -= $mailer_padding[0];
                        $address_left_x += $mailer_padding[2];
                    }
                    if ($shipping_title == '' && ($billing_details_yn == 0 || $billing_title == '') && ($float_top_address_yn == 0)) {
                        $address_top_y = ($address_top_y + ($this->_general['font_size_body'] + 2));
                    } elseif ($float_top_address_yn == 1) {
                        $address_top_y = $float_top_address_y;
                        $address_title_right_x = $padded_left;
                        $address_right_x = $padded_left;

                        if ($page_template == 'bringup') {
                            $address_top_y -= $mailer_padding[0];
                            $address_left_x += $mailer_padding[2];
                            $address_right_x += $mailer_padding[2];
                            $address_title_left_x += $mailer_padding[2];
                            $address_title_right_x += $mailer_padding[2];
                            $email_X += $mailer_padding[2] - ($this->_general['font_size_body'] / 2);
                        }
                    } else {
                        $address_top_y -= $this->_general['font_size_body'];
                    }

                    if($case_rotate > 0)
                        $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);

                    /*****set page temp first ****/
                    if($page_count == 1)
                        $page_temp_first = $page;

                    /*************************** PRINTING SHIPPING BACKGROUND BEHIND *******************************/
                    $addon_billing_y_updown_title = 0;
                    if($billing_details_position == 2){
                        $addon_billing_y_updown_title = $shipping_line_count * $this->_general['font_size_body'] + 80;
                    }
                    if (($shipping_title != '') && ($shipping_line_count > 1)) {
                        $page->drawText($shipping_title, $address_title_left_x + $address_pad[2], $address_top_y  + $address_pad[0], 'UTF-8');
                    }

                    if (($billing_details_yn == 1) && ($billing_title != '') && ($has_billing_address === true) && ($billing_line_count > 1)) {
                        $page->drawText($billing_title, $address_title_right_x + $address_pad_billing[2], $address_top_y - $addon_billing_y_updown_title + $address_pad_billing[0], 'UTF-8');
                    }

                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                    $line_height = 0;
                    $addressLine = '';
                    $line_height_top = ($this->_general['font_size_body'] + 2);
                    $line_height_bottom = (1.05 * $font_size_shipaddress);
                    $i_space = -0.5;
                    if ($shipping_billing_title_position == 'beside') {
                        $i_space = -1;
                    }
                    $show_this_billing_line = array();
                    $show_this_shipping_line = array();
                    $show_this_shipping_line_bottom = array();
                    $skip = 0;
                    $line_addon = 0;
                    $line_bold = 0;

                    $show_this_shipping_line = $this->getAddressLines($shippingAddressArray, $show_this_shipping_line);
                    $show_this_shipping_line_bottom = $this->getAddressLines($shippingAddressArrayBottom, $show_this_shipping_line_bottom);
                    if (($billing_details_yn == 1) && ($has_billing_address === true))
                        $show_this_billing_line = $this->getAddressLines($billingAddressArray, $show_this_billing_line);
                    $count_ship = (count($show_this_shipping_line));
                    $count_bill = (count($show_this_billing_line));
                    $shipping_line_count = $count_ship;
                    $billing_line_count = $count_bill;
                    if (isset($billing_line_count) && ($billing_line_count > $shipping_line_count) && ($shipping_line_count > 1)) {
                        $shipping_line_count = $billing_line_count;
                    }
                    /*************************** END SHIPPING AND BILLING ADDRESS *******************************/

                    /**PRINTING MOVABLE ORDER ID**/
                    #TOP : Show movable Order ID
                    if ($showOrderId == 1) {
                        $this->_setFont($page, $this->_general['font_style_body'], $orderId_font_size, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $order_id_nudge[0] = trim((int)$order_id_nudge[0]);
                        $order_id_nudge[1] = trim((int)$order_id_nudge[1]);
                         if(isset($barcodeWidth))
                            $order_id_X = ($padded_right - $barcodeWidth) + $order_id_nudge[0];
                        else
                            $order_id_X = $padded_right + $order_id_nudge[0];
                        $order_id_Y = ($y2 - 20 - $orderId_font_size) + $order_id_nudge[1];
                        $order_Id = $order->getRealOrderId();
                        $page->drawText($order_Id, $order_id_X, $order_id_Y, 'UTF-8');
                    }
                    /**END PRINTING MOVABLE ORDER ID**/

                    //TODO Trolley
                    foreach($order_items_arr as $trolley_item_data)
                    {
                        if($trolley_item_data['db_order_id'] == $orderSingle)
                        {

                          $order_trolley_data =$trolley_item_data;
                          break;
                        }
                    }
                    if(isset($order_trolley_data))
                    {
                        $storeID_trolley = $order_trolley_data['store_id'];
                        $trolley_text_nudge = explode(",", $this->_getConfigTrolley('pickpack_title_position', '30,810', false, 'trolleybox_picklist', $storeID_trolley));
                        $showTrolleyText = 1;

                        if ($showTrolleyText == 1) {
                            $trolley_color = new Zend_Pdf_Color_GrayScale(1.0);
                            $page->setFillColor($trolley_color);
                            $page->setLineColor($black_color);
                            $page->setLineWidth(1.2);
                            $trolley_text_nudge[0] = trim((int)$trolley_text_nudge[0]);
                            $trolley_text_nudge[1] = trim((int)$trolley_text_nudge[1]);
                            $trolley_id = $order_trolley_data['trolleybox_trolley_id'];
                            $extra_space = 45;

                            if($trolley_id >= 100)
                            {
                                $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 60, $trolley_text_nudge[1]+40);
                                $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 57, $trolley_text_nudge[1]+37);
                                $this->_setFont($page, $this->_general['font_style_body'], $orderId_font_size*2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($trolley_id, $trolley_text_nudge[0]+6, $trolley_text_nudge[1]+10, 'UTF-8');
                                $extra_space = 70;
                            }
                            else
                            {
                                if($trolley_id >= 10)
                                {
                                    $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 45, $trolley_text_nudge[1]+40);
                                    $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 42, $trolley_text_nudge[1]+37);
                                    $this->_setFont($page, $this->_general['font_style_body'], $orderId_font_size*2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $page->drawText($trolley_id, $trolley_text_nudge[0]+6, $trolley_text_nudge[1]+10, 'UTF-8');
                                    $extra_space = 55;
                                }
                                else
                                {
                                    $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 40, $trolley_text_nudge[1]+40);
                                    $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 37, $trolley_text_nudge[1]+37);
                                    $this->_setFont($page, $this->_general['font_style_body'], $orderId_font_size*2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $page->drawText($trolley_id, $trolley_text_nudge[0]+12, $trolley_text_nudge[1]+10, 'UTF-8');
                                    $extra_space = 55;
                                }
                            }


                            $page->setLineWidth(2.5);
                            $page->setFillColor($trolley_color);
                            $page->drawRectangle($trolley_text_nudge[0]+$extra_space, $trolley_text_nudge[1], $trolley_text_nudge[0] + $extra_space + 38, $trolley_text_nudge[1]+25);
                            $page->setLineColor($trolley_color);
                            $page->drawRectangle($trolley_text_nudge[0]+$extra_space-3, $trolley_text_nudge[1]+8, $trolley_text_nudge[0] + $extra_space + 38 +3, $trolley_text_nudge[1]+28);
                            $box_id = $order_trolley_data['trolleybox_box_id'];
                            $this->_setFont($page, $this->_general['font_style_body'], $orderId_font_size*1.7, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            if($box_id < 10)
                                $page->drawText($box_id, $trolley_text_nudge[0] +$extra_space +12, $trolley_text_nudge[1]+8, 'UTF-8');
                            else
                                $page->drawText($box_id, $trolley_text_nudge[0] +$extra_space +7, $trolley_text_nudge[1]+8, 'UTF-8');
                        }
                    }
                    /**END PRINTING Trolley Title**/
                    // Start to caculate min_product_y

                    /***************************PRINTING BOTTOM SHIPPING ADDRESS BARCODE *******************************/
                    if ($shipaddress_packbarcode_yn == 1) {
                        $barcode_font_size = 16;
                        $left_down = 0;

                        if ($barcode_type !== 'code128') {
                            $barcode_font_size += 12;
                            $left_down = 12;
                        }

                        $bottom_barcode_nudge[0] = trim((int)$bottom_barcode_nudge[0]);
                        $bottom_barcode_nudge[1] = trim((int)$bottom_barcode_nudge[1]);
                        $barcodeString = $this->convertToBarcodeString($order_id, $barcode_type);
                        $barcodeWidth = 1.35 * $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                        $page->setFillColor($white_color);
                        $page->setLineColor($white_color);
                        $page->drawRectangle(($addressFooterXY[0] - 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size) - 5 + $bottom_barcode_nudge[1]), ($addressFooterXY[0] + $barcodeWidth + 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 2.4) + $bottom_barcode_nudge[1] + 5));
                        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
                        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                        $page->drawText($barcodeString, ($addressFooterXY[0] - $left_down + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + $barcode_font_size - 12 - $left_down + $bottom_barcode_nudge[1] + 5 ), 'CP1252');
                        //TODO Moo cont. 4
                       //  $this->_setFont($page, 'bold', ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
//                         $page->drawText('Order Number: ', ($addressFooterXY[0] - $left_down + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 2.1) + $bottom_barcode_nudge[1]) , 'UTF-8');
//                         $page->setFillColor($white_color);
                        $minY[] = $addressFooterXY[1] + ($barcode_font_size) - ($left_down / 4) + $bottom_barcode_nudge[1];
                    }

                    if ($shipaddress_packbarcode2_yn == 1) {
                        $barcode_font_size = 16;
                        $left_down = 0;

                        if ($barcode_type !== 'code128') {
                            $barcode_font_size += 12;
                            $left_down = 12;
                        }

                        $bottom_barcode_nudge[0] = trim((int)$bottom_barcode2_nudge[0]);
                        $bottom_barcode_nudge[1] = trim((int)$bottom_barcode2_nudge[1]);

                        $barcodeString = $this->convertToBarcodeString($order_id, $barcode_type);
                        $barcodeWidth = 1.35 * $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                        $page->setFillColor($white_color);
                        $page->setLineColor($white_color);
                        $page->drawRectangle(($addressFooterXY[0] - 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size) - 5 + $bottom_barcode_nudge[1]), ($addressFooterXY[0] + $barcodeWidth + 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 2.4) + $bottom_barcode_nudge[1] + 5));
                        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
                        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                        $page->drawText($barcodeString, ($addressFooterXY[0] - $left_down + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + $barcode_font_size - 12 - $left_down + $bottom_barcode_nudge[1] + 5), 'CP1252');
                        //TODO Moo cont. 5
                        // $this->_setFont($page, 'bold', ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
//                         $page->drawText('Order Number: ', ($addressFooterXY[0] - $left_down + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 2.1) + $bottom_barcode_nudge[1]) , 'UTF-8');
//                         $page->setFillColor($white_color);
                        $minY[] = $addressFooterXY[1] + ($barcode_font_size) - ($left_down / 4) + $bottom_barcode_nudge[1];
                    }

                    /***************************PRINTING BOTTOM TRACKING NUMBER BARCODE *******************************/
                    if(isset($tracking_number_barcode_yn)  && ($tracking_number_barcode_yn == 1)){
                        $tracking_number_barcode_fontsize = $this->_getConfig('tracking_number_barcode_fontsize', 15, false, $wonder, $store_id);
                        $tracking_number_barcode_nudge = explode(",", $this->_getConfig('tracking_number_barcode_nudge', '0,0', true, $wonder, $store_id));
                        $this->drawBarcodeTrackingNumber($page,$order, $barcode_type, $font_family_barcode, $tracking_number_barcode_fontsize, $white_color, $addressFooterXY, $tracking_number_barcode_nudge);
                    }

                    /***************************PRINTING BOTTOM TRACKING NUMBER *******************************/
                    if(isset($tracking_number_yn) && ($tracking_number_yn == 1)){
                        $tracking_number_fontsize = $this->_getConfig('tracking_number_fontsize', 15, false, $wonder, $store_id);
                        $tracking_number_nudge = explode(",", $this->_getConfig('tracking_number_nudge', '0,0', true, $wonder, $store_id));
                        $this->_setFont($page, $this->_general['font_style_body'], $tracking_number_fontsize, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        if(!isset($tracking_number_barcode_nudge))
                            $tracking_number_barcode_nudge = array(0,0);
                        $this->drawTrackingNumber($page,$order, $tracking_number_fontsize, $white_color, $addressFooterXY, $tracking_number_nudge, $tracking_number_barcode_nudge);
                    }

                    /***************************PRINTING BOTTOM SHIPPING ADDRESS TITLE *******************************/
                    // if ($shipaddress_title != '') {
                    //     $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                    //     $page->drawText($shipaddress_title, ($addressFooterXY[0]), ($addressFooterXY[1] - 4) - 7, 'UTF-8');

                    //     $minY[] = ($addressFooterXY[1] - 4) - 7;
                    //     $i_space++;
                    //     $shipping_line_count++;
                    // }

                    $shipping_address_flat = '';
                    $max_y_custom_message = 20;
                    if (isset($bottom_billing_address_yn) && ($bottom_shipping_address_yn == 1 || $bottom_billing_address_yn == 1)) $max_y_custom_message = $addressFooterXY[1];

                    /***************************PRINTING BOTTOM RETURN ADDRESS IMAGE *******************************/
                    if ($this->_packingsheet['pickpack_return_address_yn'] == 1 && $show_return_logo_yn == 1) {
                        $sub_folder = 'bottom_return_address_logo_pack';
                        $option_group = 'wonder';
                        if ($wonder != 'wonder') {
                            $sub_folder = 'bottom_return_address_logo_invoice';
                            $option_group = 'wonder_invoice';
                        }

                        if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') {

                            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo_group', $order_storeId);
                        } else $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo', $order_storeId);


                        if ($image) {
                            require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
                            $image_simple = new SimpleImage();
                            $return_image_dimension = explode(",", $this->_getConfig('pickpack_logo_demension','180,120', false, $wonder, $store_id));
                            $filename = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $image;
                            $image_ext = '';
                            $temp_array_image = explode('.', $image);
                            $image_ext = array_pop($temp_array_image);
                            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($filename))) {

                                $imageObj        = Mage::helper('pickpack')->getImageObj($filename);
                                $orig_img_width  = $imageObj->getOriginalWidth();
                                $orig_img_height = $imageObj->getOriginalHeight();

                                $img_height = $imageObj->getOriginalHeight();
                                $img_width  = $imageObj->getOriginalWidth();

                                $logo_shipping_maxdimensions[0] = 250;
                                $logo_shipping_maxdimensions[1] = 300;

                                if ($orig_img_width > ($logo_shipping_maxdimensions[0])) {
                                    $img_height = ceil(($logo_shipping_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                                    $img_width  = $logo_shipping_maxdimensions[0];
                                }

                                if(($orig_img_width > $img_width*300/72) || ($orig_img_height > $img_height*300/72)){
                                    if(!(file_exists($filename))){
                                            $img_width1 = $img_width*300/72;
                                            $img_height1 = $img_height*300/72;
                                            $image_simple->load($filename);
                                            $image_simple->resize($img_width1,$img_height1);
                                            $image_simple->save($filename);
                                    }
                                }

                                if($return_logo_dimension){
                                    $img_height = $img_height*$return_logo_dimension/100;
                                    $img_width = $img_width*$return_logo_dimension/100;
                                }

                                $x1 = $return_logo_XY[0];
                                $x2 = $return_logo_XY[0] + $img_width;
                                $y1 = $return_logo_XY[1] ;
                                $y2 = $return_logo_XY[1] + $img_height;

                                $image = Zend_Pdf_Image::imageWithPath($filename);
                                $page->drawImage($image, $x1, $y1 , $x2, $y2);
                                $minY[] = $return_logo_XY[1];
                                $minY[] = $return_logo_XY[1] + $return_image_dimension[1];
                            }
                            unset($image);
                            unset($image_ext);
                            unset($temp_array_image);
                            unset($image_ext);
                        }

                        if($show_return_logo2_yn == 1)
                        {
                            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo2', $order_storeId);
                            if ($image) {
                            $return_image_dimension = explode(",", $this->_getConfig('pickpack_logo2_demension','180,120', false, $wonder, $store_id));
                            $filename = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $image;
                            $image_ext = '';
                            $temp_array_image = explode('.', $image);
                            $image_ext = array_pop($temp_array_image);
                            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($filename))) {
                                $image = Zend_Pdf_Image::imageWithPath($filename);
                                $page->drawImage($image, $return_logo2_XY[0], $return_logo2_XY[1], ($return_logo2_XY[0] + $return_image_dimension[0]), ($return_logo2_XY[1] + $return_image_dimension[1]));
                                $minY[] = $return_logo2_XY[1];
                                $minY[] = $return_logo2_XY[1] + $return_image_dimension[1];
                            }
                        }
                    }
                    }

                    /***************************END PRINTING BOTTOM RETURN ADDRESS IMAGE ***************************/
                    $this->_setFont($page, $this->_general['font_style_subtitles'], ($font_size_shipaddress - 3), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], '#444444');
                    /***************************PRINTING BOTTOM RETURN ADDRESS *******************************/
                    if (($this->_packingsheet['pickpack_return_address_yn'] == 1 || $this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') && isset($return_address)) {
                        $rotate_return_address    = $this->_getConfig('rotate_return_address', 0, false, $wonder, $store_id);
                        $rotate = $this->getRotateReturnAddress($rotate_return_address);
                        $return_address_lines = explode("\n", $return_address);
                        $i = 0;
                        foreach ($return_address_lines as $index => $line_value) {
                            $line_value = Mage::helper("pickpack/functions")->getVariable($line_value);
                            if(is_array($line_value)){
                                foreach ($line_value as $key => $value) {
                                    $value = ltrim($value, ",");
                                    $value = ltrim($value, ".");
                                    $value = trim($value);
                                    $return_address_lines[$i] = $value;
                                    $i++;
                                }
                            }else{
                                $line_value = ltrim($line_value, ",");
                                $line_value = ltrim($line_value, ".");
                                $line_value = trim($line_value);
                                $return_address_lines[$i] = $line_value;
                                $i++;
                            }
                        }

                        unset($i);
                        $i = 1;
                        $page->rotate($returnAddressFooterXY[0], $returnAddressFooterXY[1], $rotate);
                        $page->setFillColor(new Zend_Pdf_Color_Rgb($fontColorReturnAddressFooter, $fontColorReturnAddressFooter, $fontColorReturnAddressFooter));
                        if (preg_match('~^From~i', $return_address)) {
                            $return_address_title_fontsize = -2;
                            if ($font_size_returnaddress > 10) $return_address_title_fontsize = 2;
                            $this->_setFontRegular($page, ($font_size_returnaddress - $return_address_title_fontsize));

                            $page->drawText($return_address_lines[0], $returnAddressFooterXY[0], $returnAddressFooterXY[1], 'UTF-8');
                            $i = 0;
                        }

                        $this->_setFont($page, $this->_general['font_style_body'], $font_size_returnaddress, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                        $line_height = 20; // note : why set line_height = 20 here?

                        $bottom_return_address_pos = array();
                        $bottom_return_address_pos['x'] = $returnAddressFooterXY[0];
                        $bottom_return_address_pos['y'] = $returnAddressFooterXY[1];

                        //$minY[] = $returnAddressFooterXY[1];

                        $bottom_return_address_pos = preg_replace('~[^.0-9]~', '', $bottom_return_address_pos);
                        if (trim($bottom_return_address_pos['x']) == '') $bottom_return_address_pos['x'] = 0;
                        if (trim($bottom_return_address_pos['y']) == '') $bottom_return_address_pos['y'] = 0;

                        foreach ($return_address_lines as $value) {
                            if ($value !== '' && $i > 0) {
                                $bottom_return_address_pos['y'] = ($returnAddressFooterXY[1] - $line_height);
                                $page->drawText(trim(strip_tags($value)), $bottom_return_address_pos['x'], $bottom_return_address_pos['y'], 'UTF-8');
                                $line_height = ($line_height + ($font_size_returnaddress + 1));
                            }
                            $i++;
                        }
                        $page->rotate($returnAddressFooterXY[0], $returnAddressFooterXY[1], 0-$rotate);
                    }
                    /***************************PRINTING BOTTOM RETURN ADDRESS *******************************/

                    if($case_rotate > 0)
                        $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                    /***************************PRINTING START BILLING ADDRESS, SHIPPING ADDRESS TOP *******************************/
                    $cycle_address_array = array();
                    $cycle_address_array = $show_this_shipping_line;

                    if (count($show_this_shipping_line) < count($show_this_billing_line)) $cycle_address_array = $show_this_billing_line;
                    $last_line_shipping = count($show_this_shipping_line) - 1;
                    $last_line_billing = count($show_this_shipping_line) - 1;
                    $bottom_ispace = 0;
                    $paid_or_due_shown = 0;
                    $line_bold_billing = 0;
                    $line_bold = 0;
                    $line_bold_bottom = 0;
                    $bold_last_line_yn = $this->_getConfig('bold_address_format_yn', 0, false, "general", $store_id);
                    $bold_last_line_yn_top = $this->_getConfig('bold_topaddress_format_yn', 0, false, "general", $store_id);
                    $addressFooterXY[1] += $this->_general['font_size_body'];
                    $i_space = -1;
                    $address_top_y -= ($line_height_top + 2);
                    $addon_billing_y_updown = 0;
                    if($billing_details_position == 2){
                        $addon_billing_y_updown = $shipping_line_count * $this->_general['font_size_body'] + 80;
                    }
                    $string_2nd_shipping_address = "";


                    foreach ($cycle_address_array as $i => $value) {
                        if (isset($show_this_shipping_line[$i]))
                            $value_shipping = trim($show_this_shipping_line[$i]);
                        else
                            $value_shipping = '';
                        $value_shipping = ltrim($value_shipping, ",");
                        $value_shipping = ltrim($value_shipping, ".");
                        $value_shipping = trim($value_shipping);
                        //New TODO update 1
                        // $value_shipping = trim(Mage::helper('pickpack/functions')->clean_method($token, 'pdf'));
                        if($this->_general['non_standard_characters'] == 0){
                            //$value_shipping = preg_replace('/[^ A-Za-z\d.,-]/', '', $value_shipping);
                            $value_shipping = trim(Mage::helper('pickpack/functions')->clean_method($value_shipping, 'pdf'));
                         }
                        $value_shipping = preg_replace('~, ,~', '', $value_shipping);
                        if ($capitalize_label_yn == 1) {
                            if (strtolower($customer_country) == 'united states') $value_shipping = preg_replace('~,$~', '', $value_shipping);
                            $font_size_adjust = 2;
                            $value_shipping = ucfirst($value_shipping);
                        } else
                            if ($capitalize_label_yn == 2) {
                                if (strtolower($customer_country) == 'united states') $value_shipping = preg_replace('~,$~', '', $value_shipping);
                                $font_size_adjust = 2;
                            }
                        $value_billing = '';
                        if (isset($show_this_billing_line[$i])) {
                            $value_billing = trim($show_this_billing_line[$i]);
                            $value_billing = ltrim($value_billing, ",");
                            $value_billing = ltrim($value_billing, ".");
                            $value_billing = trim($value_billing);
                            //New TODO 2
                            $value_billing = trim(Mage::helper('pickpack/functions')->clean_method($value_billing, 'pdf'));
                            $value_billing = preg_replace('~, ,~', '', $value_billing);
                            if ($capitalize_label_yn == 1) {
                                if (strtolower($customer_country) == 'united states') $value_billing = preg_replace('~,$~', '', $value_billing);
                                $font_size_adjust = 2;
                                $value_billing = ucfirst($value_billing);
                            } else
                                if ($capitalize_label_yn == 2) {
                                    if (strtolower($customer_country) == 'united states') $value_billing = preg_replace('~,$~', '', $value_billing);
                                    $font_size_adjust = 2;
                                }
                        }


                        if ($bold_last_line_yn == 1 && $i == ($last_line_index - 1) && ($address_countryskip != $value_shipping)){
                            $line_bold_bottom = 1;
                        }


                        if ($bold_last_line_yn_top == 1) {
                            if ($i == ($last_line_index_top - 1) && ($address_countryskip != $value_shipping)) {
                                $line_bold = 1;
                                $value_shipping = preg_replace('~,$~', '', $value_shipping);
                            }
                            if ($i == ($billing_line_count - 1) && ($address_countryskip != $value_billing)) {
                                $line_bold_billing = 1;
                                $value_billing = preg_replace('~,$~', '', $value_billing);
                            }
                        }

						// $bottom_shipping_address_yn_xtra = 2 == dont show email
                        if ( (($bottom_shipping_address_yn_xtra == 1) && ($show_this_shipping_line[$i] != ''))
						|| ( ($bottom_shipping_address_yn_xtra == 2) && ($show_this_shipping_line[$i] != '') && (strpos($show_this_shipping_line[$i],'@')==false) )) {
                            if ($shipping_address_flat != '') $shipping_address_flat .= ', ';

                            $shipping_address_flat .= $show_this_shipping_line[$i];
                        }

                        $i_space = ($i_space + 1);
                        /**** PRINTING TOP BILLING AND SHIPPING ****/
                        if ($shipping_billing_title_position != 'beside') {
                            if (($shipping_details_yn == 1) && isset($show_this_shipping_line[$i])) {
                                if ($line_bold == 1) {
                                    $this->_setFont($page, "bold", ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $line_bold = 0;
                                } else
                                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                //Print shipping and billing. (check when have titles and without titles)
                                if ($shipping_title != '')
                                    $page->drawText($value_shipping, ($address_left_x + $address_pad[2]), ($address_top_y - ($line_height_top * $i_space) - $line_addon + $address_pad[0]), 'UTF-8');
                                else
                                    $page->drawText($value_shipping, ($address_left_x + $address_pad[2]), (($address_top_y) - ($line_height_top * $i_space) - $line_addon + $address_pad[0]), 'UTF-8');
                            }
                            if (($billing_details_yn == 1) && isset($show_this_billing_line[$i])) {

                                if ($line_bold_billing == 1) {
                                    $this->_setFont($page, "bold", ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $line_bold_billing = 0;
                                } else
                                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                if ($billing_title != '') {
                   $page->drawText($value_billing, ($address_right_x + $address_pad_billing[2]), ($address_top_y - $addon_billing_y_updown_title - ($line_height_top * $i_space) - $line_addon + $address_pad_billing[0]), 'UTF-8');
                                } else
                                    $page->drawText($value_billing, ($address_right_x + $address_pad_billing[2]), (($address_top_y) - $addon_billing_y_updown_title - ($line_height_top * $i_space) - $line_addon + $address_pad_billing[0]), 'UTF-8');
                            }
                        }
                        else {
                            if (($shipping_details_yn == 1) && isset($value_shipping)) {
                                if ($line_bold == 1) {
                                    $this->_setFont($page, "bold", ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $line_bold = 0;
                                } else
                                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($value_shipping, ($address_left_x + $address_pad[2]), ($address_top_y - ($line_height_top * $i_space) - $line_addon + $address_pad[0]), 'UTF-8');
                            }

                            if (($billing_details_yn == 1) && isset($show_this_billing_line[$i])) {
                                if ($line_bold_billing == 1) {
                                    $this->_setFont($page, "bold", ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $line_bold_billing = 0;
                                } else
                                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body']), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($value_billing, ($address_right_x + $address_pad_billing[2]), ($address_top_y - $addon_billing_y_updown_title - ($line_height_top * $i_space) - $line_addon + $address_pad_billing[0]), 'UTF-8');
                            }
                        }
                        /**** END PRINTING TOP BILLING AND SHIPPING ****/
                        $font_size_adjust = 0;
                    }

                    /***************************PRINTING BOTTOM ORDER ID ABOVE BOTTOM SHIPPING ADDRESS *******************************/
                    $this->printBottomOrderId($order,$page,$page_top,$padded_right,$this->_general['font_style_body'],$this->_general['font_size_body'],$this->_general['font_family_body'],$this->_general['font_color_body'],$font_size_shipaddress,$store_id);
                    /***************************END PRINTING BOTTOM ORDER ID ABOVE BOTTOM SHIPPING ADDRESS *******************************/

                    /***PRINTING BOTTOM SHIPPING ADDRESS***/
                    foreach ($cycle_address_array as $i => $value) {
                        $font_size_adjust = 0;
                        if ($bottom_shipping_address_yn == 1 && isset($show_this_shipping_line_bottom[$i])) {
                            $bottomOrderIdY = 0;
                            if($case_rotate > 0)
                                $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                            $value = trim($show_this_shipping_line_bottom[$i]);
                            $value = ltrim($value, ",");
                            $value = ltrim($value, ".");
                            $value = trim($value);
                            $value = trim(Mage::helper('pickpack/functions')->clean_method($value, 'pdf'));
                            $value = preg_replace('~, ,~', '', $value);
                            if ($capitalize_label2_yn == 1) {
                                // $value = strtoupper($value);
                                $value = ucfirst($value);
                                //$value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
                                if (strtolower($customer_country) == 'united states') $value = preg_replace('~,$~', '', $value);
                                $font_size_adjust = 2;
                            } else
                                if ($capitalize_label2_yn == 2) {
                                    //$value = mb_convert_case($value, MB_CASE_UPPER, "UTF-8");
                                    if (strtolower($customer_country) == 'united states') $value = preg_replace('~,$~', '', $value);
                                    $font_size_adjust = 2;
                                }

                            if ($line_bold_bottom == 1 && $i == ($last_line_index - 1) && ($address_countryskip != $value_shipping)) {
                                $this->_setFont($page, 'bold', ($font_size_shipaddress + 2 - $font_size_adjust), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $line_addon = ($font_size_shipaddress * 0.1);
                                $line_bold_bottom = 0;
                            } else $this->_setFont($page, $this->_general['font_style_body'], ($font_size_shipaddress - $font_size_adjust), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                            $string_2nd_shipping_address .= trim($value, ",") . ",";

                            $bottom_shipping_address_pos = array();
                            $bottom_shipping_address_pos['x'] = $addressFooterXY[0];
                            $bottom_shipping_address_pos['y'] = $addressFooterXY[1];
                            $bottom_shipping_address_pos = preg_replace('~[^.0-9]~', '', $bottom_shipping_address_pos);
                            if (trim($bottom_shipping_address_pos['x']) == '') $bottom_shipping_address_pos['x'] = 0;
                            if (trim($bottom_shipping_address_pos['y']) == '') $bottom_shipping_address_pos['y'] = 0;

                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $font_size_temp = $font_size_shipaddress - $font_size_adjust;
                            $line_width = $this->parseString('1234567890', $font_temp, $font_size_temp);
                            $bottom_shipping_address_max_points = $this->_getConfig('pickpack_shipaddress_maxpoints', 250, false, $wonder, $store_id);
                            $char_width_shipping_bottom = $line_width / 11;
                            $max_chars_shipping_bottom = round($bottom_shipping_address_max_points / $char_width_shipping_bottom);
                            $multiline_shipping_bottom = wordwrap($value, $max_chars_shipping_bottom, "\n");

                            $token = strtok($multiline_shipping_bottom, "\n");
                            $multiline_shipping_bottom_array = array();

                            if ($token != false) {
                                while ($token != false) {
                                    $multiline_shipping_bottom_array[] = $token;
                                    $token = strtok("\n");
                                }

                                foreach ($multiline_shipping_bottom_array as $shipping_in_line) {
                                    if ($bottom_ispace == 0)
                                        $bottom_ispace++;
                                    $bottom_ispace++;
                                    $bottom_shipping_address_pos['y'] = ($addressFooterXY[1] - ($line_height_bottom * $bottom_ispace) - $line_addon);
                                    $page->drawText($shipping_in_line, $bottom_shipping_address_pos['x'], $bottom_shipping_address_pos['y'], 'UTF-8');
                                    //$bottomOrderIdY = $bottom_shipping_address_pos['y'];
                                }
                            } else {
                                if ($bottom_ispace == 0)
                                    $bottom_ispace++;
                                $bottom_shipping_address_pos['y'] = ($addressFooterXY[1] - ($line_height_bottom * $bottom_ispace) - $line_addon);
                                $page->drawText($value, $bottom_shipping_address_pos['x'], $bottom_shipping_address_pos['y'], 'UTF-8');
                                //$bottomOrderIdY = $bottom_shipping_address_pos['y'];
                            }
                            $bottomOrderIdY  = ($addressFooterXY[1] - ($line_height_bottom * ($bottom_ispace + 1 )) - $line_addon);
                            $value = '';
                            if($case_rotate > 0)
                                $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                        }
                    }
                    /***END PRINTING BOTTOM SHIPPING ADDRESS***/

                    /***************************PRINTING 2ND BOTTOM ORDER ID BELOW BOTTOM SHIPPING ADDRESS*******************************/
                    if ($bottom_shipping_address_id_2_yn == 1) {
                        if($case_rotate > 0)
                            $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                        $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $bottom_order_id_nudge = explode(",", $this->_getConfig('pickpack_nudge_2_id_bottom_shipping_address', '0, 0', true, $wonder, $store_id));
                        if (!isset($bottom_order_id_nudge[1]))
                            $bottom_order_id_nudge[1] = 0;
                        $page->drawText('#' . $order->getRealOrderId(), $addressFooterXY[0] + $bottom_order_id_nudge[0], $bottom_order_id_nudge[1] + $bottomOrderIdY, 'UTF-8');
                        $minY[] = ($addressFooterXY[1] + ($font_size_shipaddress)) - 7;
                        if($case_rotate > 0)
                        $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);

                    }
                    /***************************PRINTING 2ND BOTTOM ORDER ID BELOW BOTTOM SHIPPING ADDRESS*******************************/

                    if($show_aitoc_checkout_field_bottom_yn == 1 && Mage::helper('pickpack')->isInstalled("Aitoc_Aitcheckoutfields")){
                        $codes = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);
                        $code_fields = explode(',', $show_aitoc_checkout_field_bottom);
                        $addon_code_x = 0;
                        foreach ($codes as $key => $code) {
                            if($code["code"] != '' && in_array($code["code"], $code_fields)){
                                $page->drawText($code["value"], ($bottom_shipping_address_pos['x'] + $addon_code_x), ($addressFooterXY[1] - 5), 'UTF-8');
                                //$line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                $addon_code_x += 40;
                            }
                        }
                    }
                    if($show_shipping_method_bottom_yn == 1){
                        if($case_rotate > 0)
                            $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                        $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $shipping_method_raw = $order->getShippingDescription();
                        $shipping_method = Mage::helper('pickpack/functions')->clean_method($shipping_method_raw, 'shipping');
                        $page->drawText($helper->__('Shipping Type') . ' : ', $bottom_shipping_address_pos['x'] + $show_shipping_method_bottom_nugde[0], ($bottom_shipping_address_pos['y'] - $this->_general['font_size_body'] - 18 + $show_shipping_method_bottom_nugde[1]), 'UTF-8');
                        $page->drawText($shipping_method, $bottom_shipping_address_pos['x'] + $show_shipping_method_bottom_nugde[0] + 50, ($bottom_shipping_address_pos['y'] - $this->_general['font_size_body'] - 18 + $show_shipping_method_bottom_nugde[1]), 'UTF-8');
                        if($case_rotate > 0)
                        $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);

                                                //}
                    }
                    /**BOTTOM 2nd SHIPPING ADDRESS**/
                    if ($bottom_shipping_address_yn == 1 && isset($show_this_shipping_line_bottom[$i]) && $bottom_2nd_shipping_address_yn == 1) {
                        $bottom_ispace = 0;
                        $bottom_2nd_shipping_address_pos = array();
                        $bottom_2nd_shipping_address_pos['x'] = $address2ndFooterXY[0];
                        $bottom_2nd_shipping_address_pos['y'] = $address2ndFooterXY[1];
                        $string_2nd_shipping_address = trim($string_2nd_shipping_address,",");
                        $multiline_shipping_bottom = wordwrap($string_2nd_shipping_address, $max_chars_shipping_bottom, "\n");
                        $token = strtok($multiline_shipping_bottom, "\n");
                        $multiline_shipping_bottom_array = array();
                        if ($bottom_shipping_address_id_yn == 1) {
                            $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 0.5), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            // $bottom_order_id_nudge = explode(",", $this->_getConfig('pickpack_nudge_id_bottom_shipping_address', '0,0', true, $wonder, $store_id));
                            // if (!isset($bottom_order_id_nudge[1]))
                            //     $bottom_order_id_nudge[1] = 0;
                            $page->drawText('#' . $order->getRealOrderId(), $address2ndFooterXY[0], $address2ndFooterXY[1] + $font_size_shipaddress - $line_addon - 30, 'UTF-8');
                            $minY[] = ($address2ndFooterXY[1] + ($font_size_shipaddress)) - 7;
                        }
                        if ($token != false) {
                            while ($token != false) {
                                $multiline_shipping_bottom_array[] = $token;
                                $token = strtok("\n");
                            }

                            foreach ($multiline_shipping_bottom_array as $shipping_in_line) {
                                if ($bottom_ispace == 0)
                                    $bottom_ispace++;
                                $bottom_ispace++;
                                $this->_setFont($page, $this->_general['font_style_body'], ($font_size_shipaddress - $font_size_adjust), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $bottom_2nd_shipping_address_pos['y'] = ($address2ndFooterXY[1] - ($line_height_bottom * $bottom_ispace) - $line_addon);
                                $page->drawText($shipping_in_line, $bottom_2nd_shipping_address_pos['x'], $bottom_2nd_shipping_address_pos['y'], 'UTF-8');
                            }
                        }
                    }

                    if($case_rotate > 0)
                        $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                    /***************************PRINTING BOTTOM EXTRA ADDRESS *******************************/
                    if ( (($bottom_shipping_address_yn_xtra == 1)||($bottom_shipping_address_yn_xtra == 2)) && ($shipping_address_flat != '')) {
                        $minY[] = $addressFooterXY_xtra[1];
                        $return_to_this_y = $this->y;
                        $this->y = $addressFooterXY_xtra[1];
                        $shipping_address_flat = trim(str_replace(',,', ',', $shipping_address_flat));
                        $max_flat_shipping_address_width = ($padded_right - $flat_address_margin_rt_xtra - $addressFooterXY_xtra[0]);
                        $max_flat_shipping_address_characters = stringBreak($shipping_address_flat, $max_flat_shipping_address_width, $font_size_shipaddress_xtra);
                        $shipping_address_flat_wrapped = wordwrap($shipping_address_flat, $max_flat_shipping_address_characters, "\n", false);
                        $this->y -= ($font_size_shipaddress_xtra + 2);
                        $line_count = 0;
                        $token = strtok($shipping_address_flat_wrapped, "\n");
                        $number_lines = (1 + substr_count($shipping_address_flat_wrapped, "\n"));
                        $this->_setFont($page, 'regular', ($font_size_shipaddress_xtra - 2), $this->_general['font_family_body'], $this->_general['non_standard_characters'], '#666666');
                        $page->drawText('#' . trim($order_id), $addressFooterXY_xtra[0], $this->y, 'UTF-8');
                        $this->y -= ($font_size_shipaddress_xtra * 1.2);

                        $this->_setFont($page, 'regular', ($font_size_shipaddress_xtra), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        while ($token != false) {
                            $page->drawText(trim($token), $addressFooterXY_xtra[0], $this->y, 'UTF-8');
                            $this->y -= $font_size_shipaddress_xtra;
                            $token = strtok("\n");
                            $line_count++;
                        }
                        unset($token);
                        unset($line_count);
                        unset($shipping_address_flat);
                        $this->y = $return_to_this_y;
                    }
                    /***************************END PRINTING BOTTOM EXTRA ADDRESS ***************************/

                    if($case_rotate > 0)
                        $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);

                    $max_address_line_count = 0;
                    if ($shipping_line_count > 1) $max_address_line_count = $shipping_line_count;
                    else $max_address_line_count = $billing_line_count;

                    /*************************** PRINTING TOP EMAIL,PHONE,FAX *******************************/
                    $tel_email_y = ceil($address_top_y - ($line_height_top * ($i_space + 1))) + $address_pad[0];
                    if($shipping_details_yn == 0 && $billing_details_yn == 0)
                        $i_space = -1;
                    $subheader_start = ($address_top_y - ($line_height_top * ($i_space + 1)) - $line_addon + $address_pad[1]);
                    $billing_shipping_details_y = null;
                    $top_phone_Y = 0;
                    $top_phone_Y = ceil($addressXY[1] - ($line_height_top * ($i_space + 1)) - $line_addon);
                    $bottom_tel_email_y = ceil($addressFooterXY[1] - (($max_address_line_count + 1) * $font_size_shipaddress));
                    if ($max_address_line_count < $bottom_ispace)
                        $max_address_line_count = $bottom_ispace + 0.5;
                    $bottom_tel_email_y = ceil($addressFooterXY[1] - (($max_address_line_count + 1) * $font_size_shipaddress));
                    $subheader_start_left = $subheader_start;
                    $thisY_left = $this->y;
                    $shipping_method_raw = '';
                    $shipping_method = '';
                    if ($has_shipping_address !== false && (($shipment_details_yn == 1) || ($beta_box_2_yn == 1) || $page_template == 'mailer')) {
                        $shipping_method = $order->getShippingDescription();
                    }
                    if($pickpack_show_full_payment_yn == 1){
                        $full_payment = $order->getPayment()->getData('additional_data');
                        if (($full_payment == '') || strpos($full_payment,'{{pdf_row_separator}}')==false){
                            $payment_order = $this->getPaymentOrder($order);
                            $full_payment = Mage::helper('payment')->getInfoBlock($payment_order)->setIsSecureMode(true)->toPdf();
                        }
                    }
                    if ($shipment_details_yn == 1) {
                        if (isset($date_y) && $date_y > 0) $shipment_details_y = ($date_y);
                            else $shipment_details_y = ($subheader_start - ($this->_general['font_size_body'] * 2));
                       // $shipment_details_y = $shipment_details_y + $shipping_detail_pad[0];
                        $orderdetailsX = $orderdetailsX + $shipping_detail_pad[1];
                        if ($float_top_address_yn == 1) {
                            if (isset($date_y) && $date_y > 0) $shipment_details_y = ($date_y);
                            else $shipment_details_y = ($subheader_start - ($this->_general['font_size_body'] * 2));
                            $orderdetailsX += 85;
                        } elseif (($billing_details_yn == 1 && $billing_details_position != 2) && ($has_billing_address === true) && ($shipping_details_yn == 1)) {
                            if ($tel_email_y < $top_phone_Y && ($customer_phone_yn != 'no') && ($customer_phone != '') && (strlen($customer_phone) > 5))
                                $shipment_details_y = $top_phone_Y;
                            else
                                $shipment_details_y = $tel_email_y;
                        } else {
                            $shipment_details_y = ($address_top_y - $line_height_top);
                            // if billing/shipping title, move up a line to be in line with the title
                            if (($shipping_billing_title_position != 'beside') && ((($billing_details_yn == 1 && $billing_details_position != 2) && ($has_billing_address === true) && ($billing_title != '')) || (($shipping_details_yn == 1) && ($shipping_title != ''))))
                                $shipment_details_y += $line_height_top;
                        }


                        $paymentInfo = '';
                        $payment_test = '';
                        if ($shipment_details_payment == 1 || $shipment_details_payment == 2) {
                            $payment_order = $this->getPaymentOrder($order);
                            if ($shipment_details_cardinfo == 0) {
                                if ($payment_order) {
                                    $paymentInfo = Mage::helper('payment')->getInfoBlock($payment_order)->setIsSecureMode(true)->toPdf();
                                } else {
                                    $paymentInfo = '';
                                }
                            } else {
                                if ($payment_order) {
                                    $paymentInfo = Mage::helper('payment')->getInfoBlock($payment_order)->setIsSecureMode(true)->toPdf();
                                } else {
                                    $paymentInfo = '';
                                }
                            }
                            if ($shipment_details_payment == 1)
                                $payment_test = clean_method($paymentInfo, 'payment-full');
                            if ($shipment_details_payment == 2) {
                                if ($this->checkPayment($paymentInfo))
                                    $payment_test = $this->cleanPaymentFull($paymentInfo);
                                else {
                                    $payment_test = clean_method($paymentInfo, 'payment-full');
                                }
                                $currencyCode = '';
                                $currency = $order->getOrderCurrency();
                                if (is_object($currency)) {
                                    $currencyCode = $currency->getCurrencyCode();
                                }
                            }
                            if(strpos($payment_test, 'BillSAFE') !== false)
                                $payment_test = '';
                        }

                        $customer_group = $helper->getCustomerGroupCode((int)$order->getCustomerGroupId());
                        $customer_id = trim($order->getCustomerId());
                        $line_height = 4;

                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $shipment_details_y += $shipment_details_nudge[1];
                        $orderdetailsX += $shipment_details_nudge[0];

                        $total_shipping_weight = 0;

                        /**PRINTING WEIGHT**/
                        if ($shipment_details_weight == 1) {
                            foreach ($order->getAllItems() as $item) {
                                if($helper->getProduct($item->getProductId())->getTypeID() != "configurable")
                                    $total_shipping_weight += ($helper->getProduct($item->getProductId())->getWeight() * $item->getQtyOrdered());
                            }
                            $total_shipping_weight = round($total_shipping_weight, 2);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Weight'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($total_shipping_weight . ' ' . $shipment_details_weight_unit, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                        }
                        /**END PRINTING WEIGHT**/

                        /**PRINTING BOXES**/
                        if ($shipment_details_boxes_yn == 1) {
                            $rounded_weight = ($total_shipping_weight / 30);
                            $rounded_weight = ceil($rounded_weight);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Boxes'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($rounded_weight, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                        }
                        /**END PRINTING BOXES**/

                        $maxWidthPage = ($padded_right - ($orderdetailsX + 95));
                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $font_size_compare = ($this->_general['font_size_body']);
                        $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                        $char_width = $line_width / 10;
                        $max_chars = round($maxWidthPage / $char_width);
                        $invert_X_plus = 0;

                        /**PRINTING SHIPMENT PICKUP DEADLINE **/
                        if ($shipment_details_pickup_time_yn == 1) {
                            $pickup_date = date('m/d/Y',strtotime(trim($order->getData('pickup_date'))));//$order->getData('pickup_date');
                            $time_slot = trim($order->getData('time_slot'));
                            if(isset($shipping_method))
                            {
                                $shipping_method = str_replace($time_slot,'',$shipping_method);
                                $shipping_method = str_replace($pickup_date,'',$shipping_method);
                                $shipping_method = trim($shipping_method);
                            }
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Pickup Time'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($time_slot.' '.$pickup_date, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $subheader_start -= $this->_general['font_size_body'];
                        }

                        /**PRINTING SHIPPING METHOD**/
                        if(isset($shipping_method))
                            $shipping_method = Mage::helper('pickpack/functions')->clean_method($shipping_method, 'shipping');

					    if (($shipment_details_carrier != '0') && ($shipping_method != '')) {
							$show_storepickup_shipmethod = false;
							// storedelivery storepickup wsa check
                            if ( ($show_wsa_storepickup != 1) || ( ($show_wsa_storepickup == 1) && ( (strpos($order->getData('shipping_method'),'storepickup') === false) && (strpos($order->getData('shipping_method'),'storedelivery') === false) ) ) ) {
								$show_storepickup_shipmethod = true;
	                            if($shipment_details_bold_label == 1){
	                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
	                            }
								$page->drawText($helper->__('Shipping Type'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
							}
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

						   	if (($shipment_details_carrier == 'filtered_by_pallet') && ($shipment_details_pallet_weight < $total_shipping_weight)) {
                                $page->drawText($helper->__('SHIP BY PALLET'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            } elseif ( ($shipping_method != '') && ($show_storepickup_shipmethod !== false) ) {
                                $shipment_details_carrier_trim_yn = $this->_getConfig('shipment_details_carrier_trim_yn', 0, false, $wonder, $store_id);
                                if ( (strlen($shipping_method) > $max_chars) && ($show_wsa_storepickup !== 1) ) {
                                    if($shipment_details_carrier_trim_yn == 1)
                                    {
                                        $shipping_display = str_trim($shipping_method, 'WORDS', $max_chars - 3, '...');
											$page->drawText($shipping_display, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
	                                        $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                    }
                                    else
                                    {
										// quick
                                        $shipping_display = mb_wordwrap_array($shipping_method, $max_chars);
                                        foreach ($shipping_display as $value) {
                                            $page->drawText(trim($value), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height += $this->_general['font_size_body'];
                                        }
                                        unset($value);
                                    }
                                } else {
									// storedelivery storepickup
                                    $page->drawText($shipping_method, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                }
                            }
                            $top_y_right_colum = $shipment_details_y - $line_height;
                            $shipping_method_static_display = '';
                            if ($this->_getConfig('shipment_details_carrier_static_text_yn', 0, false, $wonder, $store_id) == 1) {
                                $shipping_method_static_text = $this->_getConfig('shipment_details_carrier_static_text', 0, false, $wonder, $store_id);
                                if (strlen($shipping_method_static_text) > $max_chars) {
                                    $shipping_method_static_display = mb_wordwrap_array($shipping_method_static_text, $max_chars);

                                    $token = strtok($shipping_method_static_text, "\n");
                                    $multiline_shipping_top_array = array();

                                    if ($token != false) {
                                        while ($token != false) {
                                            $multiline_shipping_top_array[] = $token;
                                            $token = strtok("\n");
                                        }

                                        foreach ($multiline_shipping_top_array as $value) {
                                            $page->drawText(trim($value), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height += $this->_general['font_size_body'];
                                        }
                                    }
                                } else {
                                    $page->drawText($shipping_method_static_display, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                }
                                $top_y_right_colum = $shipment_details_y - $line_height;
                            }
                        }
                        /**END PRINTING SHIPPING METHOD**/

						/* Magalter Order Options */
                        if(isset($shipment_details_shipping_options) && $shipment_details_shipping_options == 1)
                        {

                            $shippingOptions = Mage::getModel('magalter_customshipping/order_option')->getCollection()->addFieldToFilter('order_id', $order->getId());
                            foreach ($shippingOptions as $shippingOption)
                            {
                                if(in_array($shippingOption->getData('name'),$shipment_details_shipping_options_filter))
                                {
                                    $page->drawText(ucfirst($shippingOption->getData('name')) . ': ', $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                    $page->drawText(ucfirst(trim($shippingOption->getData('value'))), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height += $this->_general['font_size_body'];
                                }
                            }
                            unset($shipment_details_shipping_options);
                            unset($shipment_details_shipping_options_filter);
                            unset($shippingOptions);
                            $line_height += $this->_general['font_size_body']*2;
                        }
						/* END Magalter Order Options */

                        if ($shipment_details_tracking_number != '0')
                        {
                            $tracking_number = $this->getTrackingNumber($order);
                             if(strlen($tracking_number) > 0)
                             {
                                if($shipment_details_bold_label == 1){
                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                }
								$page->drawText($helper->__('Tracking number'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);



                                    if (strlen($tracking_number) > $max_chars) {
                                        $shipping_display = mb_wordwrap_array($tracking_number, $max_chars);
                                        foreach ($shipping_display as $value) {
                                            $page->drawText(trim($value), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height += $this->_general['font_size_body'];
                                        }
                                        unset($value);
                                    } else {
                                        $page->drawText($tracking_number, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                        $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                    }

                                 $top_y_right_colum = $shipment_details_y - $line_height;
                            }
                        }
                        //add code field for aitcheckoutfields
                        if($show_aitoc_checkout_field_yn == 1 && Mage::helper('pickpack')->isInstalled("Aitoc_Aitcheckoutfields")){
                            $codes = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);
                            $code_fields = explode(',', $show_aitoc_checkout_field);
                            $code_lable = '';
                            $code_value = '';
                            foreach ($codes as $key => $code) {
                                if($code["code"] != '' && in_array($code["code"], $code_fields)){
                                    $code_lable = $code_lable . ' ' . $code["label"];
                                    $code_value = $code_value . ' ' . $code["value"];
                                }
                            }
                            $code_lable = trim($code_lable);
                            $code_lable_arr = explode(' ', $code_lable);
                            $arr_count_va = array_count_values($code_lable_arr);
                            $label = '';
                            foreach ($arr_count_va as $key => $value) {
                                if($value > 1)
                                    $label = $label . ' ' . $key;
                            }
                            $label = trim($label);
                            $code_value = trim($code_value);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__($label), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($code_value, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                        }
                        $purchase_test = 0;

                        /**PRINTING CUSTOMER COMMENT WITH TEMANDO**/
                        if ($shipment_temando_comment_yn == 1) {
                            if(Mage::helper('pickpack')->isInstalled('Temando_Temando') && Mage::helper('pickpack')->isInstalled('Idev_OneStepCheckout')){
                                $customer_comment = '';
                                $temando_model = Mage::getModel("temando/shipment");
                                $temando_model->load($order->getId(), "order_id");
                                $customer_comment = $temando_model->getData("customer_comment");
                                if($customer_comment != ''){
                                    if($shipment_details_bold_label == 1){
                                        $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                     }
									 $page->drawText($helper->__('Customer Comment'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    if(strlen($customer_comment) > $max_chars){
                                        $customer_comment_display = mb_wordwrap_array($customer_comment, $max_chars);
                                        foreach ($customer_comment_display as $value){
                                            $page->drawText($value, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height += $this->_general['font_size_body'];
                                            unset($value);
                                        }
                                    }
                                    else{
                                        $page->drawText($customer_comment, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                        $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                    }
                                }
                            }

                        }
                        /**END PRINTING CUSTOMER COMMENT WITH TEMANDO**/

                        /**PRINTING WHAT?**/
                        if ($shipment_details_purchase_order == 1) {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Purchase Order'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            if (strlen($purchase_test) > $max_chars) {
                                $payment_display = mb_wordwrap_array($purchase_test, $max_chars);
                                foreach ($payment_display as $value) {
                                    $page->drawText(trim(str_replace(array('#', ' '), '', $value)), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height += $this->_general['font_size_body'];
                                }
                                unset($value);
                                unset($purchase_test);
                                unset($payment_display);
                            } else {
                                $page->drawText(trim(str_replace(array('#', ' '), '', $purchase_test)), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            }
                        }

                        if ($shipment_details_custgroup == 1 && $customer_group != '') {
                            $customer_group_filtered = $customer_group;
                            $customer_group_filter_array = array();
                            $customer_group_filter_array = explode(',', $customer_group_filter);
                            foreach ($customer_group_filter_array as $customer_group_filter_single) {
                                $customer_group_filtered = trim(str_ireplace(trim($customer_group_filter_single), '', $customer_group_filtered));
                            }

                            if ($customer_group_filtered != '') {
                                if($shipment_details_bold_label == 1){
                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                }
								$page->drawText($helper->__('Customer group'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($customer_group, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                $top_y_right_colum -= $this->_general['font_size_body'];
                            }
                        }
                        /**PRINTING FIXED TEXT**/
                        if ($shipment_details_fixed_text == 1 && $shipment_details_fixed_title != '' && $shipment_details_fixed_value!= '') {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__($shipment_details_fixed_title) , $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($shipment_details_fixed_value, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }
                        /**PRINTING CUSTOMER ID**/
                        if ($shipment_details_customer_id == 1 && $customer_id != '') {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Customer ID'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($customer_id, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }
                        /**END PRINTING CUSTOMER ID**/

                        /**PRINTING Mageworx Multifees**/
                        if ($show_mageworx_multifees == 1) {
                            $details_multifees = array();
                            if(Mage::helper('pickpack')->isInstalled('MageWorx_MultiFees') && $order->getData('multifees_amount') > 0){
                                $details_multifees = unserialize($order->getData("details_multifees"));
                                $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                foreach ($details_multifees as $key => $fee) {
                                    $page->drawText(Mage::helper('multifees')->__($fee["title"]), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                    $top_y_right_colum -= $this->_general['font_size_body'];
                                }
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
                        }

                        /**PRINTING WSA Storepickup**/
                        if ( ($show_wsa_storepickup == 1) && (Mage::helper('pickpack')->isInstalled('Webshopapps_Wsacommon')) ) {
                                $wsa_storepickup_options = $this->_getConfig('wsa_storepickup_options', 0, false, $wonder, $store_id);
                                $wsa_storepickup_options_arr = explode(",", $wsa_storepickup_options);
                                $pickupStore = Mage::getModel($wsa_pickup_location_model_default)->load($order->getData('fulfillment_location'));
							 if ( (strpos($order->getData('shipping_method'),'storepickup') !== false) || (strpos($order->getData('shipping_method'),'storedelivery') !== false) ){
                                if ($this->_getConfig('non_store_pickup_showdatetime', 1, false, $wonder, $store_id) == 1){
                                    $wsa_storepickup_options_arr = array_replace($wsa_storepickup_options_arr,
                                        array_fill_keys(
                                            array_keys($wsa_storepickup_options_arr, 'pickup_date'),
                                            'pickup_date_time'
                                        )
                                    );
                                    if(($key = array_search('pickup_time', $wsa_storepickup_options_arr)) !== false) {
                                        unset($wsa_storepickup_options_arr[$key]);
                                    }
                                }

								$shown_store_pickup_address_title = false;

                                foreach ($wsa_storepickup_options_arr as $wsa_storepickup_options_arr_value) {
                                    switch ($wsa_storepickup_options_arr_value) {
                                        case 'pickup_location':
											/*
												Add in shipping method here so we can include the store ID in the same line
											*/
											if(!$pickupStore->getData('title'))
												continue;
											
											if($shipment_details_bold_label == 1){
												$this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
											}
											$page->drawText($helper->__('Shipping Type'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
											$this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
											$show_wsa_shipping_display = '';
											if(isset($shipping_method)) {
												$show_wsa_shipping_display = preg_replace('~ \- (.*)$~','',$shipping_method). ' - ';
											}
											$page->drawText($show_wsa_shipping_display . $pickupStore->getData('title')." (#".$pickupStore->getData('identifier').')', ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
											$line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
											$top_y_right_colum = $shipment_details_y - $line_height;
											break;

                                        case 'pickup_date':
                                            if(!$order->getData('fulfillment_date'))
                                                continue;
                                            if($shipment_details_bold_label == 1){
                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                             }
											 $page->drawText($helper->__('Pickup Date'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($order->getData('fulfillment_date'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;
                                        case 'pickup_time':
                                            if(!$order->getData('fulfillment_slot'))
                                                continue;
                                            if($shipment_details_bold_label == 1){
                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
											$page->drawText($helper->__('Pickup Time'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $pickUpTime = trim($order->getData('fulfillment_slot'));
                                            $pickUpTime = explode('|', $pickUpTime);
											$pickUpTimeFrom =  date('g:i a',strtotime(substr($pickUpTime[0],0,19)));
											$pickUpTimeTo =  date('g:i a',strtotime(substr($pickUpTime[1],0,19)));
                                            $page->drawText( $pickUpTimeFrom." - ".$pickUpTimeTo  , ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;

                                        case 'pickup_store_address':
                                            if(!$pickupStore->getData('street') && !$pickupStore->getData('city') && !$pickupStore->getData('region'))
                                                continue;
                                            if($shipment_details_bold_label == 1){
                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
											$page->drawText($helper->__('Store Address'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');

											$shown_store_pickup_address_title = true;
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            if($pickupStore->getData('street')) $page->drawText($pickupStore->getData('street'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
	                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
	                                            $top_y_right_colum = $shipment_details_y - $line_height;
												$pickup_store_address = '';

											if($pickupStore->getData('city')) 	$pickup_store_address .= $pickupStore->getData('city');
											if($pickupStore->getData('region'))	$pickup_store_address .= ', ' . $this->convert_state($pickupStore->getData('region'));
											if($pickupStore->getData('postal_code'))$pickup_store_address .= ' '.$pickupStore->getData('postal_code');
											if($pickup_store_address != ''){
												$page->drawText($pickup_store_address, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
											}
	                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
	                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;

                                        case 'pickup_store_phone':
                                            if(!$pickupStore->getData('phone'))
                                                continue;

											if($shown_store_pickup_address_title === false) {
	                                            if($shipment_details_bold_label == 1){
	                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
	                                            }
												$page->drawText($helper->__('Store Address'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
												$shown_store_pickup_address_title = true;
											}
											// $page->drawText($helper->__('Store Phone'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($pickupStore->getData('phone'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;
                                        case 'pickup_store_email':
                                            if(!$pickupStore->getData('email'))
                                                continue;
											if($shown_store_pickup_address_title === false) {
	                                            if($shipment_details_bold_label == 1){
	                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
	                                            }
												$page->drawText($helper->__('Store Address'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
												$shown_store_pickup_address_title = true;
											}
											// $page->drawText($helper->__('Store Email'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($pickupStore->getData('email'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;
                                        case 'pickup_date_time':
                                            if(!$order->getData('fulfillment_slot') && !$order->getData('fulfillment_date'))
                                                continue;
                                            if($shipment_details_bold_label == 1){
                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                             }
											 $page->drawText($helper->__('Date/Time'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $pickUpTime = trim($order->getData('fulfillment_slot'));
                                            $pickUpTime = explode('|', $pickUpTime);
											// may need to check against timezone of each store
											$pickUpTimeFrom =  date('g:i a',strtotime(substr($pickUpTime[0],0,19)));
											$pickUpTimeTo =  date('g:i a',strtotime(substr($pickUpTime[1],0,19)));
                                            if($pickUpTimeFrom == $pickUpTimeTo)
                                                $page->drawText($order->getData('fulfillment_date').' '.$pickUpTimeFrom , ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            else
                                                $page->drawText($order->getData('fulfillment_date').' '.$pickUpTimeFrom." - ".$pickUpTimeTo  , ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            $top_y_right_colum = $shipment_details_y - $line_height;
                                            break;
                                    }
                                }
                            }
                        }

                        /**PRINTING PAYMENT METHOD**/
                        if(($shipment_details_payment == 1 || $shipment_details_payment == 2) && (strlen($payment_test) > 0))
                        {
                            if ($shipment_details_purchase_order == 1) {
                                if (stripos($payment_test, 'purchase order') !== false) {
                                    $purchase_test = trim(str_ireplace(array('Purchase order', ':', '  '), array('', '', ' '), $payment_test));
                                    $payment_test = 'Purchase Order';
                                    if (strlen($purchase_test) < 1) $shipment_details_purchase_order = 0;
                                } else $shipment_details_purchase_order = 0;
                            }
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Payment') , $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $shipment_details_payment_trim_yn = $this->_getConfig('shipment_details_payment_trim_yn', 0, false, $wonder, $store_id);
                            if ((strlen($payment_test) > $max_chars) || (strpos($payment_test, '|') !== false))
                            {
                                if($shipment_details_payment_trim_yn == 1)
                                {
                                    $payment_display = str_trim($payment_test, 'WORDS', $max_chars - 3, '...');
                                    $page->drawText($payment_display, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                    $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                }
                                else
                                {
                                    if (strpos($payment_test, '|') !== false) $payment_display = explode('|', $payment_test);
                                    else $payment_display = mb_wordwrap_array($payment_test, $max_chars);

                                    foreach ($payment_display as $value) {
                                        $page->drawText(Mage::helper('pickpack/functions')->clean_method(trim($value),'payment'), ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                        $line_height += $this->_general['font_size_body'];
                                    }
                                    unset($value);
                                    unset($payment_display);
                                }
                            } else {
                                $page->drawText($payment_test, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            }

                            if ($shipment_details_payment == 2 && isset($currencyCode)) {
                                $page->drawText("Order was placed using " . $currencyCode, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
                            }
                            $top_y_right_colum = $shipment_details_y - $line_height;
                        }
                        /**END PRINTING PAYMENT METHOD**/

                        /**PRINTING ITEM COUNT**/
                        if ($shipment_details_count == 1) {
							$items_count = 0;
                            $items_count = ceil($order->getTotalQtyOrdered());
                            if (!$items_count || ($items_count == 0)) {
                                $items_count = count($order->getAllVisibleItems());
                            }
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Item count'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $orderdetailsX_itemcount = $orderdetailsX;
                            $orderdetailsY_itemcount = $shipment_details_y - $line_height;
                            /**draw item count after**/
                            $page->drawText($items_count, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
							$top_y_right_colum = $shipment_details_y - $line_height;
                        }
                        /**END PRINTING ITEM COUNT**/

						/** PRINTING CUSTOMER SERVICE DETAILS **/
                        if ($show_wsa_storepickup == 1) {
	                        if ( (Mage::helper('pickpack')->isInstalled('Shipperhq_Pickup'))
								&& (strpos($order->getData('shipping_method'),'storepickup') === false)
								&& (strpos($order->getData('shipping_method'),'storedelivery') === false)
								&& ($this->_getConfig('non_store_pickup_yn', 0, false, $wonder, $store_id) == 1) ){

	                                $non_store_pickup_label = $this->_getConfig('non_store_pickup_label', '', false, $wonder, $store_id);
	                                $non_store_pickup_value = $this->_getConfig('non_store_pickup_value', '', false, $wonder, $store_id);
	                                if($shipment_details_bold_label == 1){
	                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
	                                }
									$page->drawText($helper->__($non_store_pickup_label), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
	                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
	                                $page->drawText($non_store_pickup_value, ($orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $line_height), 'UTF-8');
	                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
	                                $top_y_right_colum = $shipment_details_y - $line_height;
	                        }
						}
						/** END PRINTING CUSTOMER SERVICE DETAILS **/

                        /** PRINTING CUSTOMER EMAIL **/
                        if ($shipment_details_customer_email == 1 && $customer_email != '') {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                             }
							$page->drawText($helper->__('Customer Email'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($customer_email, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }
                        /** END PRINTING CUSTOMER EMAIL **/
                         /** PRINTING CUSTOMER VAT **/
                        if ($shipment_details_customer_vat == 1) {
                            $billingaddress_2 = $order->getBillingAddress();
                            if ($billingaddress_2->getData('vat_id')) {
                                if($shipment_details_bold_label == 1){
                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                }
								$page->drawText($helper->__('Customer VAT'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText(trim($billingaddress_2->getData('vat_id')), ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                $top_y_right_colum -= $this->_general['font_size_body'];
                            }
                        }
                        /**END PRINTING CUSTOMER VAT**/

                        /**PRINTING ORDER ID**/
                        if ($shipment_details_order_id == 1) {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Order Number'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($order->getRealOrderId(), ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }

                        /**PRINTING ORDER Date**/
                        if ($shipment_details_order_date == 1) {
                            $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                             }
							 $page->drawText($helper->__('Order Date'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            //TODO Moo update 2
                            $page->drawText($order_date_title, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }

                        /**PRINTING INVOICE ID**/
                        $invoice_number_display = '';
                        foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                            if ($_tmpInvoice->getIncrementId()) {
                                if ($invoice_number_display != '') $invoice_number_display .= ',';
                                $invoice_number_display .= $_tmpInvoice->getIncrementId();
                            }
                            break;
                        }
                        if ($shipment_details_invoice_id == 1 && $invoice_number_display != '') {
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Invoice Number'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($invoice_number_display, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];

                        }
                        unset($invoice_number_display);
                        /**PRINTING PAID Date**/
                        if ($shipment_details_paid_date == 1 && $order->getCreatedAtStoreDate()) {
                            $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $date_format);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                             }
							 $page->drawText($helper->__('Paid Date'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($invoice_date_title, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }
                        /**PRINTING SHIPP Date**/
                        if ($shipment_details_shipp_date == 1 && count($order->getShipmentsCollection()) > 0) {
                            $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $date_format);
                            $shipment_date_title = Mage::helper('pickpack/functions')->createShipmentDateByFormat($order, $date_format_strftime, $date_format);
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Ship date'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($shipment_date_title, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }
                        /**PRINTING Order Source**/
                        if ($shipment_details_order_source == 1) {
                            $store = Mage::getModel('core/store')->load($order->getStoreId());
                            $source_website         = $store->getName();
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
							$page->drawText($helper->__('Order Source'), $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($source_website, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            //$subheader_start -= $this->_general['font_size_body'];
                            $top_y_right_colum -= $this->_general['font_size_body'];
                        }


                        /**PRINTING CUSTOMER CUSTOM ATTRIBUTE**/
                        if ($customer_custom_attribute_yn == 1 && $customer_custom_attribute != '') {
                        if(isset($customer_attribute_array[$customer_custom_attribute]))
                        {
                            $customer_attribute_array = array();
                            $customer_attribute_label_array = array();
                            $customer_attribute = '';
                            $customer_attribute_label = '';
                            $customer_attribute_array = Mage::getModel('customer/customer')->load($order->getCustomerId())->getData();
                            $customer_attribute_label_array = Mage::getSingleton('eav/config')->getAttribute('customer', $customer_custom_attribute);
                            $customer_attribute = $customer_attribute_array[$customer_custom_attribute];
                            $customer_attribute_label = $customer_attribute_label_array['frontend_label'];

                            if (strlen(trim($customer_attribute)) > 0) {
                                $page->drawText($customer_attribute_label . ' :', $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                $page->drawText($customer_attribute, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                $top_y_right_colum -= $this->_general['font_size_body'];
                            }

                            unset($customer_attribute_array);
                            unset($customer_attribute_label_array);
                            unset($customer_attribute);
                            unset($customer_attribute_label);
                        }
                        }
                        /**END PRINTING CUSTOMER CUSTOM ATTRIBUTE**/

                        /**PRINTING ORDER CUSTOM ATTRIBUTES **/
                        if (Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')) {
                        if ($shipment_details_custom_attribute_yn == 1 && $shipment_details_custom_attribute != '' && class_exists(get_class(Mage::getModel('amorderattr/attribute')))) {
                            unset($shipment_custom_attribute_label);
                            unset($shipment_custom_attribute);
                            $shipment_custom_attribute_label = array();
                            $shipment_custom_attribute = array();
                            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
                            $collection->addFieldToFilter('is_visible_on_front', 1);
                            $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
                            $attributes = $collection->load();
                            $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');

                            if ($attributes->getSize()) {
                                $list =  $this->getValueOrderAttribute($attributes, $filter_custom_attributes_array, $order);
                                foreach ($list as $label => $value) {
                                    if (is_array($value) && !(empty($value))) {
                                        $page->drawText($label . ': ', $email_X, $subheader_start, 'UTF-8');
                                        //$subheader_start -= $this->_general['font_size_body'];
                                        foreach ($value as $str) {
                                            foreach (explode("%BREAK%", $str) as $s) {
                                                $page->drawText($s, $email_X + 10, $subheader_start, 'UTF-8');
                                                //$page->drawText($s, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                                $line_height -= $this->_general['font_size_body'];
                                            }
                                        }
                                        $line_height -= $this->_general['font_size_body'];
                                    } else
                                        if (strlen(trim($value)) > 0) {
                                            if($shipment_details_bold_label == 1){
                                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($label, $email_X, ($shipment_details_y - $line_height), 'UTF-8');
                                            }else {
                                                $page->drawText($label . ' :', $email_X, ($shipment_details_y - $line_height), 'UTF-8');
											}
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($value, ($email_X + 85), ($shipment_details_y - $line_height), 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                            //$page->drawText($label . ': ', $email_X, $subheader_start, 'UTF-8');
                                            //$page->drawText($value, $email_X + 85, $subheader_start, 'UTF-8');
                                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                                        }

                                }
                            }

                            // if (isset($shipment_custom_attribute_label[$shipment_details_custom_attribute]) && $shipment_custom_attribute[$shipment_details_custom_attribute] != '') {
                            //     $display_cust_attr_label_1 = $shipment_custom_attribute_label[$shipment_details_custom_attribute];
                            //     $display_cust_attr_1 = $shipment_custom_attribute[$shipment_details_custom_attribute];

                            //     $page->drawText($display_cust_attr_label_1 . ' :', $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            //     $page->drawText($display_cust_attr_1, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            //     $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            // }
                        }
                        }

                        if ($shipment_details_custom_attribute_2_yn == 1 && $shipment_details_custom_attribute_2 != '') {
                            if (isset($shipment_custom_attribute_label[$shipment_details_custom_attribute_2]) && $shipment_custom_attribute[$shipment_details_custom_attribute_2] != '') {
                                $display_cust_attr_label_2 = $shipment_custom_attribute_label[$shipment_details_custom_attribute_2];
                                $display_cust_attr_2 = $shipment_custom_attribute[$shipment_details_custom_attribute_2];
                                if($shipment_details_bold_label == 1){
                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $page->drawText($display_cust_attr_label_2, $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                }else
                                $page->drawText($display_cust_attr_label_2 . ' :', $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($display_cust_attr_2, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                                $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            }
                        }
                        /**PRINTING ORDER CUSTOM ATTRIBUTES **/

                        /*
                         $shipment_details_deadline_yn    = $this->_getConfig('shipment_details_deadline_yn', 0, false,$wonder,$store_id);
                         $shipment_details_deadline_text    = trim($this->_getConfig('shipment_details_deadline_text', '', false,$wonder,$store_id));
                         $shipment_details_deadline_days
                         */

                        /**PRINTING SHIPMENT DETAILS DEADLINE **/
                        if ($shipment_details_deadline_yn == 1) {
                            $deadline_date = date($date_format, Mage::getModel('core/date')->timestamp((time() + (60 * 60 * 24 * $shipment_details_deadline_days))));
                            if($shipment_details_bold_label == 1){
                                $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($shipment_details_deadline_text, $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            }else
                                $page->drawText($shipment_details_deadline_text . ' :', $orderdetailsX, ($shipment_details_y - $line_height), 'UTF-8');
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText($deadline_date, ($orderdetailsX + 95), ($shipment_details_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + (1.15 * $this->_general['font_size_body']));
                            //$subheader_start -= $this->_general['font_size_body'];
                        }
                        /**END PRINTING SHIPMENT DETAILS DEADLINE **/
                        if (($address_pad[1] < 0)) {
                            $subheader_start = $top_y_left_colum + $address_pad[1];
                        }
                        if ($shipment_details_yn == 1) {
                            $line_height = ($line_height - (2 * $this->_general['font_size_body']));
                            if ($subheader_start > ($shipment_details_y - $line_height)) $subheader_start = ceil($shipment_details_y - ($line_height));
                            if ($subheader_start > $top_y_right_colum) $subheader_start = $top_y_right_colum ;
                        }
                    }

                    if (($billing_details_yn == 0) && ($shipping_details_yn == 0) && ($shipment_details_yn == 0)) {
                        if ($subheader_start > ($orderIdXY[1] + $this->_general['font_size_subtitles'])) {
                            $subheader_start = ($orderIdXY[1] + $this->_general['font_size_subtitles']);
                        } else {
                            $subheader_start = ($orderIdXY[1]);
                        }
                    } else {
                        if($subheader_start > $subheader_start_left)
                            $subheader_start = $subheader_start_left;
                    }
                    $subheader_start -= 10;
                    $order_notes_X = $padded_left;
                    $this->y = $subheader_start + $this->_general['font_size_body'];
                    $flag_message_after_shipping_address = 0;

                    /***************************PRINTING ORDER NOTE UNDER SHIPPING ADDRESS *******************************/
                    if ($notes_position == 'yesshipping') {

                        if ($order->getStatusHistoryCollection(true)) {
                            $notes = $order->getStatusHistoryCollection(true);
                            $note_line = array();
                            $note_comment_count = 0;
                            $character_breakpoint = 50;
                            $test_name = 'abcdefghij'; //10
                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $test_name_length = round($this->parseString($test_name, $font_temp, ($font_size_message)));
                            $pt_per_char = ($test_name_length / 10);
                            $max_name_length = $padded_right - $padded_left;
                            $character_breakpoint = round(($max_name_length / $pt_per_char));
                            $i = 0;
                            $line_count_note = 0;
                            foreach ($notes as $_item) {
                                //$_item['comment'] = clean_method($_item['comment']);

                                if ($notes_filter_options == 'yestext' && ($this->checkFilterNotes($_item['comment'], $notes_filter))) {
                                    $_item['comment'] = '';
                                }
                                if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                                    $check_comments_for_gift_message_filter = $this->_getConfig('check_comments_for_gift_message_filter', 'Checkout Message', false, $wonder, $store_id);
                                    $pos = strpos($_item['comment'], 'M2E Pro Notes');
                                    $pos2 = strpos($_item['comment'], $check_comments_for_gift_message_filter);
                                    if (($pos !== false) && ($pos2 !== false)) {
                                        $start_pos1 = strlen('M2E Pro Notes') + 1;
                                        $start_pos2 = strlen('Checkout Message From Buyer:') + 1;
                                        $str_1 = trim(substr($_item['comment'], $start_pos1));
                                        $str_2 = trim(substr($str_1, $start_pos2));
                                        $gift_message_array['notes'][] = $str_2;
                                        $_item['comment'] = '';
                                    } else
                                        if ($pos !== false) {
                                            $_item['comment'] = str_replace('M2E Pro Notes:', '', $_item['comment']);
                                        }
                                }
                                if(Mage::helper('pickpack')->isInstalled('Brainvire_OrderComment')){
                                    if($_item['is_customer_notified'] != 0)
                                        $_item['is_visible_on_front'] = 1;
                                }

                                if ($_item['comment'] != '' && (($notes_filter_options == 'yesfrontend' && $_item['is_visible_on_front'] == 1)
                                        || ($notes_filter_options == 'no'
                                            || $notes_filter_options == 'yestext'))
                                ) {
                                    $_item['created_at'] = date('m/d/y', strtotime($_item['created_at']));
                                    // $_item['comment'] = $_item['created_at'] . ' : ' . $_item['comment'];
                                    $note_comment = explode(':',$_item['comment']);
                                    $note_comment[count($note_comment) - 1] =  preg_replace('/\s+/', ' ', $note_comment[count($note_comment) - 1]);
                                    $_item['comment'] = $note_comment[count($note_comment) - 1];
                                    $str = Mage::helper('pickpack')->__('Because the Order currency is different from the Store currency, the conversion from');
                                    $str_to = Mage::helper('pickpack')->__('Prices converted from');
                                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                                    $order_currency_code = $order->getOrderCurrencyCode();
                                    $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
                                    $str = Mage::helper('pickpack')->__('"'.$order_currency_code.'" to "'.$store_currency_code.'"');
                                    $str_to = Mage::helper('pickpack')->__('"'.$store_currency_code.'" to "'.$order_currency_code.'"');
                                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                                    preg_match_all('/\d+\.\d+/',  $_item['comment'], $matches);
                                    $num = $matches[0];
                                    $str = Mage::helper('pickpack')->__('was performed using '. (float)$num[0] .' as a rate');
                                    $str_to = Mage::helper('pickpack')->__('@ '.(float)$num[0]);
                                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);                                    $note_line[$i]['date'] = $_item['created_at'];
                                    $note_line[$i]['comment'] = $_item['comment'];
                                    if ($note_line[$i]['comment'] != '') $note_comment_count = 1;
                                    $note_line[$i]['is_visible_on_front'] = $_item['is_visible_on_front'];
                                    $note_line_break = explode("\r\n", $note_line[$i]['comment']);
                                    foreach ($note_line_break as $note_line_each) {
                                        if ($note_line_each != "") {
                                            $note_line_each = trim($note_line_each);
                                            $note_line_wr = wordwrap($note_line_each, $character_breakpoint, "\n", false);
                                            $comment_array = explode("\n", $note_line_wr);
                                            $line_count_note += count($comment_array);
                                            unset($comment_array);
                                        }
                                    }
                                    $i++;
                                }
                            }

                            if ($note_comment_count > 0) {
                                $flag_message_after_shipping_address = 1;
                                $character_breakpoint = 50;
                                $subheader_start = $subheader_start + $this->_general['font_size_body'] ;
                                $this->y = $subheader_start - $vertical_spacing;
                                $this->y -= ($font_size_message + 2);
                                $test_name = 'abcdefghij';
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $test_name_length = round($this->parseString($test_name, $font_temp, ($this->_general['font_size_body'])));
                                $pt_per_char = ($test_name_length / 10);
                                $max_name_length = $padded_right;
                                $character_breakpoint = round(($max_name_length / $pt_per_char) - 25);

                                if (($background_color_message_zend != '') && ($background_color_message_zend != '#FFFFFF')) {
                                    $page->setFillColor($background_color_message_zend);
                                    $page->setLineColor($background_color_message_zend);
                                    $page->setLineWidth(0.5);
                                    $page->drawRectangle($padded_left, ($this->y + $font_size_message + 2), $max_name_length, ($this->y - (($line_count_note - 1) * ($font_size_message+3)) - 5));
                                }
                                $subheader_start = $this->y - ($line_count_note * ($font_size_message)) - 5;
                                $this->_setFont($page, 'bold', ($font_size_message), $font_family_message, $this->_general['non_standard_characters'], $font_color_message);
                                $page->drawText(Mage::helper('sales')->__($notes_title), ($order_notes_X + 4), $this->y, 'UTF-8');
                                $this->y -= ($font_size_message + 3);
                                $this->_setFont($page, $font_style_message, ($font_size_message - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_message);
                                sksort($note_line, 'date', true);
                                $i = 0;
                                $line_count = 0;
                                while (isset($note_line[$i]['date'])) {
                                    $token = strtok($note_line[$i]['comment'], "\r\n");
                                    while ($token != false) {
                                        //New TODO 3
                                        if ($this->_general['non_standard_characters'] == 0)
                                            $token = trim(Mage::helper('pickpack/functions')->clean_method($token, 'pdf_more'));
                                        else
                                            $token = trim(Mage::helper('pickpack/functions')->clean_method($token, 'pdf'));

                                        $page->drawText($token, ($order_notes_X + 4), $this->y, 'UTF-8');
                                        $this->y -= ($font_size_message + 3);
                                        $token = strtok("\n");
                                        $line_count++;
                                    }
                                    $order_notes_was_set = true;
                                    $i++;
                                }
                                $subheader_start = $this->y + ($font_size_message + 3);
                            }
                            unset($note_line);
                            unset($_item);
                        }
                    }
                    /***************************END PRINTING ORDER NOTE UNDER SHIPPING ADDRESS *******************************/
                    /***************************PRINTING ORDER GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/
                    if ($gift_message_yn == 'yesundership')
                    {
                        $gift_message ='';
                        $to_from = '';
                        $to_from_from = '';
                        if((!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL)){
                            //if(isset($gift_msg_array)){
                            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $giftWrap_info, $gift_msg_array);
                            $gift_sender = $gift_msg_array[1];
                            $gift_recipient = $gift_msg_array[2];
                            $gift_message = $gift_msg_array[0];
                            //}
                            if (isset($gift_recipient) && $gift_recipient != '') {
                                if ($gift_message_yn != 'yesnewpage') $to_from .= 'Message to: ' . $gift_recipient;
                                else $to_from .= 'To ' . $gift_recipient;
                            }
                            if (isset($gift_sender) && $gift_sender != '') $to_from_from = 'From: ' . $gift_sender;
                        }
                        if (Mage::helper('pickpack')->isInstalled('Webtex_GiftRegistry')){
                            $customerId = $order->getData("customer_id");

                            $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
                            $gift_registry_message = '';
                            if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
                                $gift_registry_message = 'This is a Gift Registry Order ' . '(' . $gift_registry["giftregistry_id"] . ')'  ;
                                $gift_message = $gift_message . $gift_registry_message;
                            }
                        }
                        if($gift_message != ''){
                            $subheader_start = $subheader_start + $this->_general['font_size_body'] ;
                            $this->y = $subheader_start + 15;
                            $this->y -= ($font_size_message *2 + 10);

                            $msgX = $padded_left;
                            if ($page_template != 1) $msgX = $orderIdXY[0];
                            $character_message_breakpoint = 96;
                            $gift_msg_array = $this->createMsgArray($gift_message);
                            $line_tofrom = 0;
                            if ($message_title_tofrom_yn == 1)
                                $line_tofrom = 2.5;
                            $msg_line_count = count($gift_msg_array) + $line_tofrom;
                            // Caculate necessary height for print gift message.
                            $temp_height = 0;
                            foreach ($gift_msg_array as $gift_msg_line) {
                                $temp_height += 2 * $font_size_message;
                            }

                            $flag_message_after_shipping_address = 1;
                            //draw background gift message
                            $left_bg_gift_msg = $padded_left;
                            $right_bg_gift_msg = $padded_right;
                            $top_bg_gift_msg = ($this->y + $font_size_message * 1.2);
                            $bottom_bg_gift_msg = $this->y - ($msg_line_count-0.5) * ($font_size_message + 1.4);
                            $this->drawBackgroundGiftMessage($background_color_message_zend, $background_color_message_zend, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
                            $this->_setFont($page, 'bold', ($font_size_message), $font_family_message, $this->_general['non_standard_characters'], $font_color_message);

                            // add option to show to from
                            $this->_setFont($page, 'bold', ($font_size_message), $font_family_message, $this->_general['non_standard_characters'], $font_color_message, $page);
                            //$this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $msgX + $font_size_message / 3, $this->y, $to_from_from, $font_size_message, $page);
                            $this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $email_X, $this->y, $to_from_from, $font_size_message, $page);
                            // print the gift message content
                            $this->_setFont($page, $font_style_message, ($font_size_gift_message - 1), $font_family_message, $this->_general['non_standard_characters'], $font_color_message);
                            //draw message order gift
                           // $this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $font_size_message / 3, $font_size_message, $this->y, $page);
                            $this->y = $this->drawOrderGiftMessage($gift_msg_array, $email_X, $font_size_message, $this->y, $page);
                            unset($gift_msg_array);
                            if (isset($giftWrap_info['wrapping_paper'])) {
                                $wrapping_paper_text = trim($giftWrap_info['wrapping_paper']);
                                if ($wrapping_paper_text != '') {
                                    if ($gift_message_yn == 'yesnewpage') {
                                        $this->y -= ($font_size_message + 3);
                                        if (strtoupper($background_color_message) != '#FFFFFF') {
                                            $page->setFillColor($background_color_message_zend);
                                            $page->setLineColor($background_color_message_zend);
                                            $page->setLineWidth(0.5);
                                            $page->drawRectangle($padded_left, ($this->y - ($font_size_gift_message / 2)), $padded_right, ($this->y + $font_size_gift_message + 2));
                                        }

                                        $this->_setFont($page, $font_style_gift_message, ($font_size_gift_message), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);

                                        $this->y -= ($font_size_gift_message + 2);
                                        $page->drawText(Mage::helper('pickpack')->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                    } else {
                                        $this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);

                                        $this->y -= ($font_size_gift_message + 2);
                                        $page->drawText(Mage::helper('pickpack')->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                    }
                                    $this->y -= ($font_size_gift_message + 2);
                                    $this->_setFont($page, 'regular', ($font_size_gift_message - 1), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);
                                    $page->drawText($wrapping_paper_text, ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                }
                            }
                            $subheader_start = $this->y + $this->_general['font_size_body'] - 5 ;//- 2.5 * $this->_general['font_size_body'];
                        }
                    }
                    /***************************END PRINTING ORDER GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/

                    /***************************PRINTING PRODUCT GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/
                    if ($product_gift_message_yn == 'yesundership') {
                        $test_name = 'abcdefghij';
                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $test_name_length = round($this->parseString($test_name, $font_temp, ($this->_general['font_size_body'])));
                        $pt_per_char = ($test_name_length / 10);
                        $max_chars_message = $this->getMaxCharMessage($padded_right, $font_size_options, $font_temp);
                        $gift_message_product = $this->getProductGiftMessageUnderShip($order, $max_chars_message);
                        if ($gift_message_product) {
                            $message_character_breakpoint = 96;
                            $msgX = $padded_left;
                            if ($page_template != 1) $msgX = $orderIdXY[0];

                            $gift_msg_array = $this->createMsgArray($gift_message_product);
                            // Caculate necessary height for print gift message.
                            $temp_height = $this->getHeightLine($gift_msg_array, $font_size_message);
                            if ($gift_message_yn == "yesundership" && !is_null($gift_message_id))
                                $this->y = $bottom_bg_gift_msg;
                            elseif ($gift_message_yn != "yesundership" && $notes_position == 'yesshipping') {
                                $this->y = $subheader_start + ($this->_general['font_size_body'] * ($line_count + 1));
                            } else
                                $this->y = $subheader_start - 4 * $font_size_message;
                            $flag_message_after_shipping_address = 1;
                            $left_bg_gift_msg = $padded_left;
                            $right_bg_gift_msg = $padded_right;
                            $top_bg_gift_msg = ($this->y + $font_size_message);
                            $bottom_bg_gift_msg = ($this->y - $temp_height);
                            $this->drawBackgroundGiftMessage($background_color_message_zend, $background_color_message_zend, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
                            // print the gift message content
                            $this->_setFont($page, $font_style_gift_message, ($font_size_message - 1), $font_family_message, $this->_general['non_standard_characters'], $font_color_message);
                            $this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $font_size_message, $font_size_message, $this->y, $page);
                            unset($gift_msg_array);
                            $subheader_start = $this->y - 3.5 * $this->_general['font_size_body'];
                        }
                    }
                    /***************************PRINTING PRODUCT GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/

                    /***************************PRINTING GIFT WRAP UNDER SHIPPING ADDRESS *******************************/
                    if (isset($giftWrap_info['style']) && ($giftwrap_style_yn == 'yesshipping')) {
                        $page->drawText('Giftwrap style: ' . $giftWrap_info['style'], ($order_notes_X), $subheader_start, 'UTF-8');
                        $this->y -= $this->_general['font_size_body'];
                        $subheader_start -= $this->_general['font_size_body'];
                    }
                    /***************************END PRINTING GIFT WRAP UNDER SHIPPING ADDRESS *******************************/

                    /***************************PRINTING CUSTOMER COMMENTS UNDER SHIPPING ADDRESS *******************************/
                    $customer_comments_shown = false;
                    $customer_comments = null;
                    $customer_comments_b = null;

                    if($notes_yn != 0)
                    {
                        if ($order->getOnestepcheckoutCustomercomment() != '')
                        {
                            $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getOnestepcheckoutCustomercomment())));

                        } elseif ($order->getData('gomage_checkout_customer_comment')) {
                            $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getData('gomage_checkout_customer_comment'))));
                        } elseif ($order->getHCheckoutcomment()) {
                            $customer_comments .= $helper->__('This is a message from the customer : ') . $order->getHCheckoutcomment();
                        } elseif ($order->getData('customer_comment')) {
    						// custom on solo site but likely used elsewhere so left in code
                            $customer_comments .= $order->getData('customer_comment');
                        }

                        if ($order->getFirecheckoutCustomerComment() != '')
                        {
                            $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getFirecheckoutCustomerComment())));

                        } elseif ($order->getData('firecheckoutCustomerComment')) {
                            $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getData('firecheckoutCustomerComment'))));
                        }

                        if(Mage::helper('pickpack')->isInstalled('MW_Onestepcheckout')){
                             $MWorder=Mage::getModel('onestepcheckout/onestepcheckout')->getCollection()->addFieldToFilter('sales_order_id',$order->getId())->getFirstItem();
                             $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $MWorder->getMwCustomercommentInfo())));
                        }

                        if (Mage::helper('pickpack')->isInstalled('Aitoc_Aitcheckoutfields')) {
                            $data_label = '';
                            //$filter_by_code = 'delivery'; // <<< enter attribute code to use here
                            $code = array();
                            $data = array();
                            $code = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);

                            if (is_array($code)) {
                                foreach ($code as $data) {
                                    if (($data['value'] != '')) ; // && ($data['code'] == $filter_by_code))
                                    {
                                        if ($customer_comments != '') $customer_comments .= ' | ';
                                        if ($data['label'] != '') $data_label = $data['label'] . ' : ';
                                        $customer_comments .= $data_label . trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $data['value'])));
                                    }
                                }
                            }
                        }

                        if (Mage::helper('pickpack')->isInstalled('Spletnisistemi_OrderComment')) {
                            if ($order->getSpletnisistemiOrdercomment() != '') {
                                $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getSpletnisistemiOrdercomment())));
                            }
                        }
                        if (Mage::helper('pickpack')->isInstalled('MageMods_OrderComment')) {

                            if ($order->getComment() != '') {
                                $customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getComment())));
                            }
                        }
                        if ($order->getBiebersdorfCustomerordercomment()) {
                            $customer_comments_b = trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getBiebersdorfCustomerordercomment())));
                        }
                    //New TODO Customer comment
                    // if(empty($customer_comments) && empty($customer_comments_b))
//                      {
//                          $comments = array();
//                          foreach($order->getStatusHistoryCollection() as $comment)
//                          {
//
//                              if (!$comment->getData('is_visible_on_front') && $comment->getComment() && !$comment->getData('is_customer_notified'))
//                              {
//                                  $displayed ++;
//                                  //$commentText = $this->_getTruncatedComment($comment->getComment(),'trim');
//                                  $commentText = $comment->getComment();
//
//                                  /*if (strpos($commentText, '<br />') !== false) {
//                                      $commentStrings = explode('<br />', $commentText);
//                                      foreach ($commentStrings as $key => $string) {
//                                          $commentStrings[$key] = $this->_getTruncatedComment($string,'trim');
//                                      }
//                                      $commentText = implode('<br />', $commentStrings);
//                                  } else {
//                                      $commentText = $this->_getTruncatedComment($commentText,'trim');
//                                  }*/
//
//                                  $comments[$displayed]['time'] = strtotime($comment->getCreatedAtDate());
//                                  $comments[$displayed]['datetime'] = $comment->getCreatedAtDate();
//                                  $comments[$displayed]['text'] = trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $commentText)));// mb_substr(Mage::helper('moogento_shipeasy/functions')->clean_method($commentText),0,(Mage::getStoreConfig('moogento_shipeasy/grid/admin_comments_truncate')-1), 'UTF-8');
//                                  $comments[$displayed]['text_full'] = $this->_getTruncatedComment($comment->getComment(),'full');
//                                  $comments[$displayed]['count'] = 1;
//                                  /*
//                                  $commentTextTrim = substr(preg_replace('~[^a-zA-Z0-9]+~', '', $commentText),0,10);
//                                  if(!isset($comments[$commentTextTrim]))
//                                  {
//                                          $comments[$commentTextTrim]['time'] = strtotime($comment->getCreatedAtDate());
//                                          $comments[$commentTextTrim]['datetime'] = $comment->getCreatedAtDate();
//                                          $comments[$commentTextTrim]['text'] = Mage::helper('moogento_shipeasy/functions')->clean_method($commentText);
//                                          $comments[$commentTextTrim]['text_full'] = $this->_getTruncatedComment($comment->getComment(),'full');
//                                          $comments[$commentTextTrim]['count'] = 1;
//                                  }
//                                  else
//                                  {
//                                          $thisTime = strtotime($comment->getCreatedAtDate());
//                                          $comments[$commentTextTrim]['count'] ++;
//                                          if($comments[$commentTextTrim]['time'] < $thisTime)
//                                          {
//                                                  $comments[$commentTextTrim]['time'] = $thisTime;
//                                                  $comments[$commentTextTrim]['datetime'] = $comment->getCreatedAtDate();
//                                          }
//                                  }*/
//
//                                  $customer_comments .=$comments[$displayed]['text'].'\n';
//                              }
//                          }
//                      }
                        if ($notes_position == 'yesshipping') {
                            if (strlen($customer_comments) > 0) {
                                $subheader_start -= $this->_general['font_size_body'];
                                $this->y = $subheader_start;

                                $max_comment_length = ($padded_right - $order_notes_X + 100);
                                $max_comment_characters = stringBreak($customer_comments, $max_comment_length, $this->_general['font_size_body']);
                                $customer_comments_wrapped = wordwrap($customer_comments, $max_comment_characters, "\n", false);
                                $this->y -= ($this->_general['font_size_body'] + 4);
                                $line_count = 0;
                                $token = strtok($customer_comments_wrapped,"\n");
                                $number_lines = substr_count($customer_comments_wrapped,"\n");
                                if (($background_color_message_zend != '') && ($background_color_message_zend != '#FFFFFF')) {
                                    $page->setFillColor($background_color_message_zend);
                                    $page->setLineColor($background_color_message_zend);
                                    $page->setLineWidth(0.5);
                                    if ($fill_background_color_comments == 0) {
                                    $page->drawLine($padded_left, 2 + ($this->y + (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))), $padded_right, 2 +($this->y + (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))));
                                    $page->drawLine($padded_left, ($this->y - (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))) - 10, $padded_right, ($this->y - (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))) - 10);
                                    $page->drawLine($padded_left, 2 + ($this->y + (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))), $padded_left, ($this->y - (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))) - 10);
                                    $page->drawLine($padded_right, 2 + ($this->y + (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))), $padded_right, ($this->y - (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))) - 10);
                                    }
                                    else
                                       $page->drawRectangle(20, ($this->y - (($font_size_comments / 2) + (($number_lines+0.5) * $font_size_comments))), $padded_right, ($this->y + $font_size_comments + 2));
                                }

                                $this->_setFont($page, 'bold', ($font_size_comments*1.2), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);
                                $page->drawText($helper->__('Customer Comments'), ($order_notes_X + 3), $this->y-2, 'UTF-8');
                                $this->y -= ($font_size_comments+4);
                                $this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);

                                while ($token != false) {
                                    $page->drawText(trim($token), $order_notes_X + 7, $this->y, 'UTF-8');
                                    $this->y -= $font_size_comments;
                                    $token = strtok("\n");
                                    $line_count++;
                                }
                                $customer_comments_shown = true;
                                $subheader_start = $this->y - $this->_general['font_size_body'];
                            }

                            if (strlen($customer_comments_b) > 0) {
                                $customer_comments_b_wrapped = wordwrap($customer_comments_b, 114, "\n", false);
                                $this->y -= ($this->_general['font_size_body'] + 4);
                                if ($customer_comments_shown === false) {
                                    $this->_setFont($page, $font_style_comments, ($font_size_comments), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);
                                    $page->drawText($helper->__('Customer Comments'), ($order_notes_X), $this->y, 'UTF-8');
                                    $this->y -= ($font_size_comments);
                                }
                                $this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);

                                $line_count = 0;
                                $token = strtok($customer_comments_b_wrapped, "\n");
                                while ($token != false) {
                                    $page->drawText(trim($token), ($order_notes_X), $this->y, 'UTF-8');
                                    $this->y -= $font_size_comments;
                                    $token = strtok("\n");
                                    $line_count++;
                                }
                                $customer_comments_shown = true;
                                $subheader_start = $this->y - $this->_general['font_size_body'];
                            }
                        }
                    }
                    /***************************END PRINTING CUSTOMER COMMENTS UNDER SHIPPING ADDRESS *******************************/
                    /***************************PRINTING POSTMAN NOTICE UNDER SHIPPING ADDRESS *******************************/
                    /* Add Order Postman Notice*/
                    if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
                        if (strlen($notice) > 0) {
                            $notice_line = array();
                            $notice_line_count = 0;
                            $notice_line = wordwrap($notice, 114, "\n", false);
                            $i = 0;
                            $this->y -= ($this->_general['font_size_body'] + 4);
                            $this->_setFont($page, 'bold', ($this->_general['font_size_body'] - 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $page->drawText('Postman Notice', ($addressXY[0]), $this->y);
                            $this->y -= ($this->_general['font_size_body'] + 3);
                            $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] - 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                            $i = 0;
                            $line_count = 0;
                            $token = strtok($notice_line, "\n");
                            while ($token != false) {
                                $page->drawText(trim($token), ($addressXY[0]), $this->y);
                                $this->y -= 10;
                                $token = strtok("\n");
                                $line_count++;
                            }
                            $order_notes_was_set = true;
                            $i++;

                            $subheader_start -= (($this->_general['font_size_body']) * ($line_count + 2));
                            unset($notice_line);
                            unset($notice_obj);
                            unset($notice);
                        }
                    }
                    /***************************END PRINTING POSTMAN NOTICE UNDER SHIPPING ADDRESS *******************************/


                    $line_height = 0;
                    $i = 0;
                    $page->setFillColor($black_color);
                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                    if ($page_template == 'mailer') {
                        $subheader_start -= $mailer_padding[1];
                        $page->drawText($order_date, $padded_left, ($subheader_start - ($this->_general['font_size_body'] * 2)), 'UTF-8');
                        $page->drawText($helper->__('Shipping Type') . ' : ' . $shipping_method, $padded_left, ($subheader_start - ($this->_general['font_size_body'] * 3.2)), 'UTF-8');
                    }

                    if (strtoupper($background_color_subtitles) == '#FFFFFF') {
                        $subheader_start -= ($this->_general['font_size_subtitles'] * 2);
                    }

                    // set the y pos of the first bar, according to height of logo image
                    if ($subheader_start > $datebar_start_y) $subheader_start = $datebar_start_y;
                    $subheader_start = min(array($subheader_start, $top_y_left_colum, $top_y_right_colum));


                    /**PRINTING AMASTY ORDER ATTRIBUTE**/
                    $order_attribute_value = '';
                    if (Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')) {
                    $subheader_start +=$this->_general['font_size_body'];
                        if($prices_yn && $multi_prices_yn == 1){
                            $attributeCode = $multiplier_attribute;
                            $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
                            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode("order", $attributeCode);
                            try{
                                $options = $attribute->getSource()->getAllOptions(true, true);
                            }
                            catch (Exception $e) {
                            };
                            $value = '';
                            if(isset($options) && is_array($options)){
                                foreach ($options as $option)
                                {
                                    if ($option['value'] == $orderAttributes->getData($attributeCode))
                                    {
                                        $value = $option['label'];
                                        break;
                                    }
                                }
                                unset($options);
                            }
                            $order_attribute_value = $value;
                            unset($orderAttributes);
                            unset($attribute);

                            unset($value);
                        }
                        $order_custom_attribute_yn = $this->_getConfig('order_custom_attribute_yn', 0, false, $wonder, $store_id);
                        $order_custom_attribute_filter = $this->_getConfig('order_custom_attribute_filter', '', false, $wonder, $store_id);

                        $filter_custom_attributes_array = explode("\n", $order_custom_attribute_filter);
                        foreach ($filter_custom_attributes_array as $key => $value) {
                            $filter_custom_attributes_array[$key] = trim($value);
                        }

                        if ($order_custom_attribute_yn == 1) {
                            if (
                                (($wonder == 'wonder_invoice') && (Mage::getStoreConfig('amorderattr/pdf/invoice') == 1))
                                ||
                                (($wonder == 'wonder') && (Mage::getStoreConfig('amorderattr/pdf/shipment') == 1))
                            ) {
                                $amas_attributes = $this->getAmasAttribute();
                                if ($amas_attributes->getSize() > 0) {
                                    $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
                                    $list = array();
                                    $list =  $this->getValueOrderAttribute($amas_attributes, $filter_custom_attributes_array, $order);

                                    $flat_print_separator_line = 0;
                                    foreach ($list as $label => $value) {
                                        if (is_array($value) && !(empty($value))) {
                                            if ($flat_print_separator_line == 0) {
                                                if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                                    $page->setFillColor($background_color_subtitles_zend);
                                                    $page->setLineColor($background_color_subtitles_zend);
                                                    $page->setLineWidth(0.5);
                                                    if ($fill_product_header_yn == 1) {
                                                        $page->drawLine($padded_left, $subheader_start - $this->_general['font_size_body'], $padded_right, $subheader_start - $this->_general['font_size_body']);
                                                        $subheader_start -= 30;
                                                    }
                                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                    $flat_print_separator_line = 1;
                                                }
                                            }
                                                if($shipment_details_bold_label == 1){
                                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                }else{
                                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                }
                                            $page->drawText($helper->__($label) . ': ', $padded_left, $subheader_start, 'UTF-8');
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $label_length = round($this->parseString($helper->__($label), $font_temp, ($this->_general['font_size_body'])));
                                            $count_line = 0;
                                            foreach ($value as $str) {
                                                $page->drawText(trim($str), $padded_left + $label_length + 6, $subheader_start, 'UTF-8');
                                                $count_line++;
                                                if (count($value) > $count_line){
                                                    $subheader_start -= 1.5 * $this->_general['font_size_body'];
                                                }
                                            }
                                            if(count($list) > 1 || $flag_message_after_shipping_address != 1)
                                                $subheader_start -= 1.5 * $this->_general['font_size_body'];
                                        } else
                                            if (is_string($value) && strlen(trim($value)) > 0) {
                                                if ($flat_print_separator_line == 0) {
                                                    if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                                        $page->setFillColor($background_color_subtitles_zend);
                                                        $page->setLineColor($background_color_subtitles_zend);
                                                        $page->setLineWidth(0.5);
                                                        if ($fill_product_header_yn == 1) {
                                                            $page->drawLine($padded_left, $subheader_start - $this->_general['font_size_body'], $padded_right, $subheader_start - $this->_general['font_size_body']);
                                                            $subheader_start -= 30;
                                                        }
                                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                        $flat_print_separator_line = 1;
                                                    }
                                                }
                                                if($shipment_details_bold_label == 1){
                                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                                                    $this->_setFont($page, "bold", $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                }else{
                                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                }

                                                //$page->drawText($helper->__($label) . ': ', $email_X, $subheader_start, 'UTF-8');
                                                $page->drawText($helper->__($label) . ': ', $padded_left, $subheader_start, 'UTF-8');

                                                $label_length = round($this->parseString($helper->__($label), $font_temp, ($this->_general['font_size_body'])));

                                                $amorderattrX = $padded_left + $label_length + 10;

                                                if($label_length > $padded_right - 100){
                                                    $amorderattrX = $padded_left;
                                                    $subheader_start -= 1.5 * $this->_general['font_size_body'];
                                                }

                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText(trim($value), $amorderattrX, $subheader_start, 'UTF-8');
                                                $subheader_start -= 1.5 * $this->_general['font_size_body'];
                                            }

                                    }
                                }
                            }
                        }
                    }
                    else{
                        if($prices_yn ==1 && $multi_prices_yn == 1){
                            $attributeCode = $multiplier_attribute;
                            $order_attribute_value = $order->getData("declaration_percentage");
                        }
                    }
                    /**END PRINTING AMASTY ORDER ATTRIBUTE**/
                    /**PRINTING AMASTY DELIVERY DATE **/
                    $order_custom_delivery_date_yn = $this->_getConfig('order_custom_delivery_date_yn', 0, false, $wonder, $store_id);
                    if (($order_custom_delivery_date_yn == 1) && Mage::helper('pickpack')->isInstalled('Amasty_Deliverydate')) {
                        if (Mage::getStoreConfig('amdeliverydate/general/enabled', $currentStore)) {
                            $currentStore = $order->getStoreId();
                            $fields = Mage::helper('amdeliverydate')->whatShow('invoice_pdf', $currentStore, 'include');
                            $shipment_fields = Mage::helper('amdeliverydate')->whatShow('shipment_pdf', $currentStore, 'include');
                            if (is_array($fields) && (!empty($fields))) {
                                $deliveryDate = Mage::getModel('amdeliverydate/deliverydate');
                                $deliveryDate->load($order->getId(), 'order_id');
                                $list = array();
                                foreach ($fields as $field) {

                                    $value = $deliveryDate->getData($field);

                                    if ('date' == $field) {

                                        $label = 'Approximate Shipping Date';

                                        if ('0000-00-00' != $value) {

                                            $format = Mage::app()->getLocale()->getDateTimeFormat(

                                                Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM

                                            );

                                            $format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));

                                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString($format);

                                        } else {

                                            $value = '';

                                        }

                                    } elseif ('time' == $field) {

                                        $label = 'Delivery Time Interval';

                                    } elseif ('comment' == $field) {

                                        $label = 'Customer Comments';

                                        $value = htmlentities(preg_replace('/\$/', '\\\$', $value), ENT_COMPAT, "UTF-8");

                                        $text = str_replace(array("\r\n", "\n", "\r"), '~~~', $value);

                                        $value = array();

                                        foreach (explode('~~~', $text) as $str) {

                                            foreach (Mage::helper('core/string')->str_split($str, 120, true, true) as $part) {

                                                if (empty($part)) {

                                                    continue;

                                                }

                                                $value[] = $part;

                                            }

                                        }

                                    }

                                    if (is_array($value)) {

                                        $list[$label] = $value;

                                    } elseif ($value) {

                                        $list[$label] = $value;

                                    }

                                }
                                $this->y -= ($this->_general['font_size_body'] + 4);

                                $this->_setFont($page, 'bold', ($this->_general['font_size_body'] - 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText('Delivery date', ($addressXY[0]), $this->y);
                                $this->y -= ($this->_general['font_size_body'] + 3);
                                $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] - 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                $i = 0;
                                $line_count = 0;

                                $subheader_start -= (($this->_general['font_size_body']) * ($line_count + 2));
                                if (!empty($list)) {

                                    foreach ($list as $label => $value) {

                                        if (is_array($value)) {

                                            $page->drawText($label . ': ', $addressXY[0], $this->y, 'UTF-8');

                                            foreach ($value as $str) {

                                                $page->drawText($str, $addressXY[0] + 160, $this->y, 'UTF-8');
                                                $this->y -= 10;
                                                $line_count++;

                                            }

                                        } else {

                                            $page->drawText($label . ': ', $addressXY[0], $this->y, 'UTF-8');

                                            $page->drawText($value, $addressXY[0] + 160, $this->y, 'UTF-8');

                                            $this->y -= 10;
                                            $line_count++;

                                        }

                                    }

                                }
                                $subheader_start -= (($this->_general['font_size_body']) * ($line_count + 2));

                            }

                        }
                    }
                    /**PRINTING AMASTY DELIVERY DATE **/
                    if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                        $page->setFillColor($background_color_subtitles_zend);
                        $page->setLineColor($background_color_subtitles_zend);
                        $page->setLineWidth(0.5);
                    }

					/**PRINTING MW DELIVERY DATE **/
                    $order_mw_custom_delivery_date_yn = $this->_getConfig('order_mw_custom_delivery_date_yn', 0, false, $wonder, $store_id);
                    if ( ($order_mw_custom_delivery_date_yn == 1) && Mage::helper('pickpack')->isInstalled('MW_Ddate') ) {
						$ddate = Mage::getResourceModel('ddate/ddate')->getDdateByOrder($order->getIncrementId());
						if($ddate) {
							$Tm = '';
							$fields = array();
						        if(!empty($ddate['dtime'])){$Tm=$ddate['dtime'];}else{$Tm=$ddate['dtimetext'];}
	                            $fields = array('date'=>$ddate['ddate'],'time'=>$Tm);
	                            if (is_array($fields) && (!empty($fields))) {
	                                $list = array();
	                                foreach ($fields as $key=>$field) {
	                                    $value = $field;
	                                    if ('date' == $key) {
	                                        $label = 'Delivery Date';
	                                        if ('0000-00-00' != $value) {
	                                            $value = Mage::helper('ddate')->format_ddate($value);
	                                        } else {
	                                            $value = '';
	                                        }
	                                    } elseif ('time' == $key) {
	                                        $label = 'Delivery Time';
	                                    }
	                                    if (is_array($value)) {
	                                        $list[$label] = $value;
	                                    } elseif ($value) {
	                                        $list[$label] = $value;
	                                    }
	                                }
									if (!strlen($customer_comments) > 0) {
	                                   $this->y -= ($this->_general['font_size_body'] - 80);
									}
	                                $this->y -= ($this->_general['font_size_body'] + 10);

									$page->setFillColor($background_color_subtitles_zend);
									$page->setLineColor($background_color_subtitles_zend);
									$page->setLineWidth(0.5);
									$page->drawRectangle($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ($this->y + $this->_general['font_size_subtitles'] + 2));

	                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
	                                $page->drawText('Delivery Date', ($addressXY[0]), $this->y);
	                                $this->y -= ($this->_general['font_size_body'] + 15);
	                                $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 2), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

	                                $i = 0;
	                                $line_count = 0;

	                                $subheader_start -= (($this->_general['font_size_body']) * ($line_count + 2));
	                                if (!empty($list)) {

	                                    foreach ($list as $label => $value) {

	                                        if (is_array($value)) {

	                                            $page->drawText($label . ': ', $addressXY[0], $this->y, 'UTF-8');

	                                            foreach ($value as $str) {

	                                                $page->drawText($str, $addressXY[0] + 160, $this->y, 'UTF-8');
	                                                $this->y -= 12;
	                                                $line_count++;

	                                            }

	                                        } else {

	                                            $page->drawText($label . ': ', $addressXY[0], $this->y, 'UTF-8');

	                                            $page->drawText($value, $addressXY[0] + 160, $this->y, 'UTF-8');

	                                            $this->y -= 10;
	                                            $line_count++;

	                                        }

	                                    }

	                                }
	                                $subheader_start -= (($this->_general['font_size_body']) * ($line_count + 2));

	                            }
						}
                    }
                    /**PRINTING MW DELIVERY DATE **/
                    if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                        $page->setFillColor($background_color_subtitles_zend);
                        $page->setLineColor($background_color_subtitles_zend);
                        $page->setLineWidth(0.5);
                    }

                    /***************************PRINTING HEADER TITLE BAR UNDER SHIPPING ADDRESS*****************************/
                    if ($pickpack_headerbar_yn == 2) {
                        $subheader_start -= ($this->_general['font_size_body'] + 2);
                        $subheader_start -= ($vertical_spacing + 3);
                        if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                            $page->setFillColor($background_color_subtitles_zend);
                            $page->setLineColor($background_color_subtitles_zend);
                            $page->setLineWidth(0.5);
                            if ($fill_product_header_yn == 1) {

                                switch ($fill_bars_options) {
                                    case 0:
                                        $page->drawRectangle($padded_left, ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ceil($subheader_start + $this->_general['font_size_subtitles'] + 2));
                                        break;
                                    case 1:
                                        if ($invoice_title_linebreak <= 1 && ($line_widths[0] > 0 || $line_widths[1] > 0)) {
                                            $bottom_fillbar = ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2)) - $fillbar_padding[1];
                                            $top_fillbar = ceil($subheader_start + $this->_general['font_size_subtitles'] + 2) + $fillbar_padding[0];
                                            if($line_widths[0] > 0){
                                                $page->setLineWidth($line_widths[0]);
                                                $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                            }
                                            if($line_widths[1] > 0){
                                                $page->setLineWidth($line_widths[1]);
                                                $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                            }
                                        }
                                        break;
                                    case 2:
                                        break;
                                }

                            } else {
                                switch ($fill_bars_options) {
                                    case 1:
                                        $page->drawRectangle($padded_left, ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3), $padded_right, ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3));
                                        break;
                                    case 2:
                                        if ($invoice_title_linebreak <= 1  && ($line_widths[0] > 0 || $line_widths[1] > 0)) {
                                            $bottom_fillbar = ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3) - $fillbar_padding[1];
                                            $top_fillbar = ceil($subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3) + $fillbar_padding[0];
                                            if($line_widths[0] > 0){
                                                $page->setLineWidth($line_widths[0]);
                                                $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                            }
                                            if($line_widths[1] > 0){
                                                $page->setLineWidth($line_widths[1]);
                                                $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                            }
                                        }
                                        break;
                                    case 3:
                                        break;
                                }
                            }

                        }

                        $order_date = '';
                        if ($order_or_invoice_date == 'order') {
                            if (($from_shipment != 'invoice') && ($order->getCreatedAtStoreDate())) {
                                $order_date = 'n/a';
                                $dated = $order->getCreatedAt();
                                $dated_timestamp = strtotime($dated);

                                if ($dated != '') {
                                    $dated_timestamp = strtotime($dated);
                                    if ($dated_timestamp != false) {
                                        $order_date = Mage::getModel('core/date')->date($date_format, $dated_timestamp);
                                    } else {
                                        $locale_timestamp = Mage::getModel('core/date')->timestamp(strtotime($order->getCreatedAt()));
                                        if ($locale_timestamp != false) $order_date = Mage::getModel('core/date')->date($date_format, $locale_timestamp);
                                    }
                                }
                            }
                        } elseif ($order_or_invoice_date == 'invoice') {
                            if ($order->getCreatedAtStoreDate()) {
                                $_invoices = $order->getInvoiceCollection();
                                foreach ($_invoices as $_invoice) {
                                    $invoiceIncrementId = $_invoice->getIncrementId();
                                    $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceIncrementId);
                                    $dated = $invoice->getCreatedAt();
                                    if ($dated != '')
                                    {
                                        $dated_timestamp = strtotime($dated);
                                        $order_date = date($date_format, $dated_timestamp);
                                    }
                                    break;
                                }

                            }
                        } elseif ($order_or_invoice_date == 'today') {
                            $order_date = date($date_format, Mage::getModel('core/date')->timestamp(time()));
                        }

                        $invoice_number_display = '';
                        $order_number_display = '';

                        foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                            if ($_tmpInvoice->getIncrementId()) {
                                if ($invoice_number_display != '') $invoice_number_display .= ',';
                                $invoice_number_display .= $_tmpInvoice->getIncrementId();
                            }
                            break;
                        }

                        if ($order_or_invoice == 'order') $order_number_display = $order->getRealOrderId();
                        elseif ($order_or_invoice == 'invoice' && $invoice_number_display != '') {
                            $order_number_display = $invoice_number_display;
                        }

                        $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        $orderIdXY[1] -= $mailer_padding[1];

                        if ($split_supplier_yn == 'pickpack') {
                            $order_date .= '      Supplier: ' . $supplier;
                            $title_date_xpos -= 50;
                        }

                        $date_y = null;
                        if ($title_date_xpos == 'auto' && $page_template != 'mailer') {
                            $order_number_display .= '   ' . $order_date;
                        } elseif ($page_template != 'mailer') {
                            $date_y = $orderIdXY[1];
                        }

                        if ($page_template == 'mailer') {
                            $orderIdXY[1] += ($font_size_company * 2);
                            $orderIdXY[0] = $padded_left;
                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] * 1.4), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        }

                        if ($invoice_title != '') {
                            // If small logo, make sure Invoice Title/Date start below height of raised address (if address has been brought up)
                            if ($float_top_address_yn == 1 && (($has_billing_address == 1) || ($has_shipping_address == 1)) && ($orderIdXY[1] > ($page_top - ($this->_general['font_size_body'] * 15)))) $orderIdXY[1] = ($page_top - ($this->_general['font_size_body'] * 15));

                            $title_start_X = $orderIdXY[0];
                            $date_y = $orderIdXY[1];

                            if ($title_invert_color != 1) {

                                ////Order date. n/a if empty
                                $order_date_title = 'n/a';
                                $dated_title = $order->getCreatedAt();
                                $dated_timestamp = strtotime($dated_title);

                                if ($dated_title != '') {
                                    $dated_timestamp = strtotime($dated_title);
                                    if ($dated_timestamp != false) {
                                        $order_date_title = Mage::getModel('core/date')->date($date_format, $dated_timestamp);
                                    } else {
                                        $locale_timestamp = Mage::getModel('core/date')->timestamp(strtotime($order->getCreatedAt()));
                                        if ($locale_timestamp != false) $order_date_title = Mage::getModel('core/date')->date($date_format, $locale_timestamp);
                                    }

                                    $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);

                                } else {
                                    //This field is empty.
                                    $from_date = "{{if order_date}}";
                                    $end_date = "{{endif order_date}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }

                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);

                                }
                                //////////// Invoice date  n/a if empty
                                if ($order->getCreatedAtStoreDate()) {
                                    $invoice_date_title = '';
                                    $_invoices_title = $order->getInvoiceCollection();
                                    foreach ($_invoices_title as $_invoice_title) {
                                        $invoiceIncrementId_title = $_invoice_title->getIncrementId();
                                        $invoice_title_2 = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceIncrementId_title);
                                        $dated_invoice_title = $invoice_title_2->getCreatedAt();
                                        if ($dated != '') // eg Packing Sheet from Invoices page = no date
                                        {
                                            $dated_timestamp_invoice_title = strtotime($dated_invoice_title);
                                            $invoice_date_title = date($date_format, $dated_timestamp_invoice_title);
                                        }
                                        break;
                                    }
                                } else {
                                    $invoice_date_title = '';
                                }

                                if ($invoice_date_title == '') {
                                    //This field is empty.
                                    $from_date = "{{if invoice_date}}";
                                    $end_date = "{{endif invoice_date}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }
                                    $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);
                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);
                                } else {
                                    $invoice_title = str_replace("{{if invoice_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_date}}", '', $invoice_title);
                                }

                                if ($invoice_number_display == '') {
                                    //This field is empty.
                                    $from_date = "{{if invoice_id}}";
                                    $end_date = "{{endif invoice_id}}";
                                    $from_date_pos = strpos($invoice_title, $from_date);
                                    if ($from_date_pos !== false) {
                                        $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                        $date_length = $end_date_pos - $from_date_pos;
                                        $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                        $invoice_title = str_replace($date_str, '', $invoice_title);
                                    }
                                    $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                                    unset($from_date);
                                    unset($end_date);
                                    unset($from_date_pos);
                                    unset($end_date_pos);
                                    unset($date_length);
                                    unset($date_str);
                                } else {
                                    $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                                }

                                /*****  Get Warehouse information ****/
                                if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                                    $warehouse_helper = Mage::helper('warehouse');
                                    $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                                    $resource = Mage::getSingleton('core/resource');
                                    /**
                                     * Retrieve the read connection
                                     */
                                    $readConnection = $resource->getConnection('core_read');
                                    $query = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse") . ' WHERE entity_id=' . $order->getData('entity_id');
                                    $warehouse_stock_id = $readConnection->fetchOne($query);
                                    if ($warehouse_stock_id) {
                                        $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                                        $warehouse_title = ($warehouse->getData('title'));
                                    } else {
                                        $warehouse_title = '';
                                    }
                                } else {
                                    $warehouse_title = '';
                                }

                                $from_date = "{{if warehouse}}";
                                $end_date = "{{endif warehouse}}";
                                $from_date_pos = strpos($invoice_title, $from_date);
                                if ($from_date_pos !== false) {
                                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                                    $date_length = $end_date_pos - $from_date_pos;
                                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                                    $invoice_title = str_replace($date_str, '', $invoice_title);
                                } else {
                                    $invoice_title = str_replace("{{if warehouse}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif warehouse}}", '', $invoice_title);
                                }
                                unset($from_date);
                                unset($end_date);
                                unset($from_date_pos);
                                unset($end_date_pos);
                                unset($date_length);
                                unset($date_str);
                                /*****  Get Warehouse information ****/

                                //////////// Printing date  n/a if empty
                                $printing_date_title = date($date_format, Mage::getModel('core/date')->timestamp(time()));
                                if ($printing_date_title != '') {
                                    $invoice_title = str_replace("{{if printing_date}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif printing_date}}", '', $invoice_title);
                                }

                                ////Order id title
                                $order_number_display_title = $order->getRealOrderId();
                                if ($order_number_display_title != '') {
                                    $invoice_title = str_replace("{{if order_id}}", '', $invoice_title);
                                    $invoice_title = str_replace("{{endif order_id}}", '', $invoice_title);
                                }
                                ////// Invoice number display
                                $arr_1 = array('{{order_date}}', '{{invoice_date}}', '{{printing_date}}', '{{order_id}}', '{{invoice_id}}');
                                $arr_2 = array($order_date_title, $invoice_date_title, $printing_date_title, $order_number_display_title, $invoice_number_display);
                                $invoice_title_print = str_replace($arr_1, $arr_2, $invoice_title);
                                $order_number_display = $invoice_title_print;
                                $page->drawText($order_number_display, $title_start_X, $subheader_start, 'UTF-8');

                            } elseif ($title_invert_color == 1) {
                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], '#FFFFFF');
                                $page->drawText($invoice_title, $title_start_X, $subheader_start, 'UTF-8');
                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                $page->drawText($order_number_display, ($title_start_X + (($this->_general['font_size_subtitles'] / 2) * strlen($invoice_title))), $subheader_start, 'UTF-8');
                            }
                        } else {
                            $page->drawText($order_number_display, $orderIdXY[0], $subheader_start, 'UTF-8');
                        }
                        $subheader_start -= ($this->_general['font_size_subtitles'] / 2);
                    }
                    /***************************PRINTING HEADER TITLE BAR UNDER SHIPPING ADDRESS*****************************/

                    /*************************** START PRINT PRODUCTS *******************************/
                    //COLUMNS: qty,tickbock,sku,name,shelving1,shelving2,shelving3,price,tax,barcode,total price

                    $subheader_start += $this->_general['font_size_body'];
                    if ($flag_message_after_shipping_address == 1)
                        $subheader_start -= $this->_general['font_size_body'] * 1.3;
                    /***PRINTING PRODUCT HEADER BAR***/
                    $titlebar_padding_top = $this->_getConfig('titlebar_padding_top', '0', false, 'general', $store_id) ;
                    $titlebar_padding_bot = $this->_getConfig('titlebar_padding_bot', '0', false, 'general', $store_id) ;
                    if ($fill_product_header_yn == 0) {
                        //$subheader_start -= 10;
                        $this->y = ($subheader_start - 10);
                        if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                            $page->setFillColor($background_color_subtitles_zend);
                            $page->setLineColor($background_color_subtitles_zend);
                            $page->setLineWidth(0.5);
                            switch ($fill_bars_options) {
                                case 0:
                                    $page->drawRectangle($padded_left, $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3, $padded_right, $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3);
                                    break;
                                case 1:
                                    $page->drawLine($padded_left, $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3, ($padded_right), $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3);
                                    $page->drawLine($padded_left, $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3, ($padded_right), $subheader_start - ($this->_general['font_size_subtitles'] / 2) - 3);
                                    break;
                                case 2:
                                    break;
                            }
                        }
                    } else {
                        //$subheader_start -= 10;
                        $this->y = ($subheader_start - $vertical_spacing);
                        $this->y -= $this->_general['font_size_subtitles'] + 2;
                        if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                            $page->setFillColor($background_color_subtitles_zend);
                            $page->setLineColor($background_color_subtitles_zend);
                            $page->setLineWidth(0.5);
                            switch ($fill_bars_options) {
                                case 0: // Yes(default)
                                    $page->drawRectangle($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ($this->y + $this->_general['font_size_subtitles'] + 2));
                                    break;
                                case 1: // Partially: lines top & bottom
                                    $page->drawLine($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2) - $titlebar_padding_top - $titlebar_padding_bot), ($padded_right), ($this->y - ($this->_general['font_size_subtitles'] / 2) - $titlebar_padding_top - $titlebar_padding_bot));
                                    $page->drawLine($padded_left, ($this->y + $this->_general['font_size_subtitles'] + 2) , ($padded_right), ($this->y + $this->_general['font_size_subtitles'] + 2));
                                    $this->y = $this->y - $titlebar_padding_top;
                                    break;
                                case 2:
                                    //dont draw anything
                                    break;
                            }
                        }
                    }

                    $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);

                    if ($from_shipment == 'shipment') {
                        $productXInc = 25;
                    } else {
                        $productXInc = 0;
                    }

                    $first_item_title_shift_sku = 0;
                    $first_item_title_shift_items = 0;
                    if (($qtyX > 50) && ($tickbox_yn == 1)) {
                        if ($productX < $skuX) $first_item_title_shift_items = $first_item_title_shift;
                        elseif ($skuX < $productX) $first_item_title_shift_sku = $first_item_title_shift;
                    }


                    if ($product_images_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($images_title), $imagesX, $this->y, 'UTF-8');
                    }
                    $page->drawText(Mage::helper('sales')->__($qty_title), $qtyX + 8, $this->y, 'UTF-8');
                    $minDistanceSku = $padded_right - $skuX;
                    if ($show_name_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($items_title), ($productX + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
                        $distance_name = $productX + $productXInc - $skuX;
                        if ($distance_name > 0 && $distance_name < $minDistanceSku)
                            $minDistanceSku = $distance_name;
                    }

                    if($show_gift_wrap_yn == 1){
                        $page->drawText(Mage::helper('sales')->__($gift_wrap_title), ($gift_wrap_xpos + $first_item_title_shift_items), $this->y, 'UTF-8');
                    }

                    if ($serial_code_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($serial_code_title), ($serial_codeX + $first_item_title_shift_items), $this->y, 'UTF-8');
                    }
                    if ($product_sku_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($sku_title), ($skuX), $this->y, 'UTF-8');

                    if ($product_sku_barcode_yn != 0) {
                        $page->drawText(Mage::helper('sales')->__($sku_barcode_title), ($sku_barcodeX - 1), $this->y, 'UTF-8');
                        $distance_barcode = $sku_barcodeX - $skuX;
                        if ($distance_barcode > 0 && $distance_barcode < $minDistanceSku)
                            $minDistanceSku = $distance_barcode;
                    }

                    if ($product_sku_barcode_yn != 0 && $product_sku_barcode_2_yn != 0) {
                        $page->drawText(Mage::helper('sales')->__($sku_barcode_2_title), ($sku_barcodeX_2 - 1), $this->y, 'UTF-8');
                        $distance_barcode = $sku_barcodeX_2 - $skuX;
                        if ($distance_barcode > 0 && $distance_barcode < $minDistanceSku)
                            $minDistanceSku = $distance_barcode;
                    }

                    if ($product_stock_qty_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($product_stock_qty_title), ($stockqtyX), $this->y, 'UTF-8');
                        $distance_stock = $stockqtyX - $skuX;
                        if ($distance_stock > 0 && $distance_stock < $minDistanceSku)
                            $minDistanceSku = $distance_stock;
                    }

                    if ($product_qty_backordered_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($product_qty_backordered_title), ($prices_qtybackorderedX), $this->y, 'UTF-8');
                        $distance_qtybarcode = $prices_qtybackorderedX - $skuX;
                        if ($distance_qtybarcode > 0 && $distance_qtybarcode < $minDistanceSku)
                            $minDistanceSku = $distance_qtybarcode;
                    }
                if($supplier_hide_attribute_column ==0)
                    if ($product_warehouse_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($product_warehouse_title), ($prices_warehouseX), $this->y, 'UTF-8');
                        $distance_warehouse = $prices_warehouseX - $skuX;
                        if ($distance_warehouse > 0 && $distance_warehouse < $minDistanceSku)
                            $minDistanceSku = $distance_warehouse;
                    }

                    if ($product_options_yn == 'yescol') {
                        $page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
                        $distance_option = $optionsX - $skuX;
                        if ($distance_option > 0 && $distance_option < $minDistanceSku)
                            $minDistanceSku = $distance_option;
                    }

                    if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0) {
                        $page->drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y, 'UTF-8');
                        $distance_shel1 = $shelfX - $skuX;
                        if ($distance_shel1 > 0 && $distance_shel1 < $minDistanceSku)
                            $minDistanceSku = $distance_shel1;
                    }

                    if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0) {
                        $page->drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y, 'UTF-8');
                    }

                    if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0) {
                        $page->drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y, 'UTF-8');
                    }

                    if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0) {
                        $page->drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y, 'UTF-8');
                    }
                    if ($combine_custom_attribute_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y, 'UTF-8');
                    }
                    if ($prices_yn != '0') {
                        $page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
                        $page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
                        $distance_price = $priceEachX - $skuX;
                        if ($distance_price > 0 && $distance_price < $minDistanceSku)
                            $minDistanceSku = $distance_price;
                    }
                    if($show_allowance_yn == 1){
                        $page->drawText(Mage::helper('sales')->__($show_allowance_title), $show_allowance_xpos, $this->y, 'UTF-8');
                    }
                    if ($tax_col_yn == 1) {
                        $page->drawText(Mage::helper('sales')->__($tax_title), $taxEachX, $this->y, 'UTF-8');
                    }

                    $this->y = $this->y - $titlebar_padding_bot;

                    /***PRINTING PRODUCT HEADER BAR***/
                    $this->y -= ($this->_general['font_size_subtitles'] / 2 + $vertical_spacing + $this->_general['font_size_body'] - 1);

                    if (strtoupper($background_color_subtitles) == '#FFFFFF') $this->y += 10;

                    $items_y_start = $this->y;

                    // number of lines of products to show
                    $cutoff_no = round((($this->y - ($addressFooterXY[1] - 30)) / 15));

                    // if no bottom labels, can show more products
                    if (($bottom_shipping_address_yn == 0) && ($this->_packingsheet['pickpack_return_address_yn'] == 0)) $cutoff_no = round(($this->y - 30) / 15);

                    $counter = 1;
                    $total_items = count($itemsCollection);
                    $total_qty = 0;
                    $total_price = 0;
                    $total_price_ex_vat = 0;
                    $total_weight = 0;
                    $total_price_taxed = 0;
                    $total_price_ex_vat = 0;
                    $price_unit_taxed = 0;
                    $price_unit_tax = 0;
                    $price_discount_unrounded = 0;
                    $max_name_length = 0;

                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                    $next_col_to_product_x = getPrevNext2($this->columns_xpos_array, 'productX', 'next');
                    $max_name_length = ($next_col_to_product_x - $productX);

                    $name_overflows_at_position = 0;
                    if ($trim_names_yn == 0 || $trim_names_yn == 'linebreak') {
                        $name_overflows_at_position = $max_name_length;
                    }

                    $custom_options_output = '';
                    $bundle_options_part = '';
                    $product_build = array();
                    $product_build_item = array();
                    $subtotal_addon = array();

                    $coun = 1;
                    $tax_percents = array();
                    $tax_percents_total = array();
                    $filter_print_item_count = 0;
                    $print_item_count = 0;
                    $store_view = $this->_getConfig('name_store_view', 'storeview', false, $wonder, $store_id);
                    $specific_store_id = $this->_getConfig('specific_store', '', false, $wonder, $store_id);
                    /* PREPARE PRODUCT INFO FOR PRINTING*/
                    foreach ($itemsCollection as $itemId => $item) {
                        if (Mage::helper('pickpack')->isInstalled('Webtex_GiftRegistry')){
                            $customOptions = $item->getProductOptions();

                            if ($customOptions['info_buyRequest'])
                            {
                                $info_buyRequest = $customOptions['info_buyRequest'];

                            }


                            $answer = '';

                            if(isset($info_buyRequest['webtex_giftregistry_id']) && $info_buyRequest['webtex_giftregistry_id'])
                            {
                                $registry = Mage::helper('webtexgiftregistry')->getRegistryById($info_buyRequest['webtex_giftregistry_id']);
                                $_array['firstname'] = $registry->getData('firstname');
                                $_array['lastname'] = $registry->getData('lastname');
                                $_array['cofirstname'] = $registry->getData('co_firstname');
                                $_array['colastname'] = $registry->getData('co_lastname');

                                $url = $this->getUrl('webtexgiftregistry/index/registry', array('id'=>$registry->getData('giftregistry_id')));

                                $answer = 'For'. ' ' . $_array['firstname'] . ' ' . $_array['lastname'];

                                if($_array['cofirstname'] || $_array['colastname'])
                                {
                                    $answer .= ' ' . 'And' . ' ';
                                    $answer .= $_array['cofirstname'] . ' ' . $_array['colastname'];
                                }

                                $answer .= ' ' . 'Gift Registry (#'.$registry->getData('giftregistry_id').')';
                            }
                        }
                        $item_invoiced = $item->getData('qty_invoiced') - 0;
                        $item_shiped = $item->getData('qty_shipped') - 0;
                        if (($filter_items_by_status == 1) && ($item_invoiced < 1))
                            continue;
                        else
                            if (($filter_items_by_status == 2) && ($item_shiped < 1))
                                continue;

                        $filter_print_item_count++;

                        $custom_options_output = '';
                        $Magikfee = 0;

                        $product = $this->_getProductFromItem($item);
                        if(isset($product_full_sku) && $product_full_sku == "fullsku")
                            $sku = $item->getSku();
                        else
                            $sku = $product->getSku();
                        $product_id = $product->getId();

                        $product_sku = $sku;
                        if (!isset($supplier)) $supplier = '~Not Set~';
                        if (!isset($sku_supplier_item_action[$supplier][$product_sku])) {
                            if ($supplier_options == 'filter') $sku_supplier_item_action[$supplier][$product_sku] = 'hide';
                            elseif ($supplier_options == 'grey') $sku_supplier_item_action[$supplier][$product_sku] = 'keepGrey';
                            if ($split_supplier_yn == 'no') $sku_supplier_item_action[$supplier][$product_sku] = 'keep';
                        }

                        if (isset($sku_supplier_item_action[$supplier]) && isset($sku_supplier_item_action[$supplier][$product_sku]) && $sku_supplier_item_action[$supplier][$product_sku] != 'hide') {
                            $product_build_item[] = $sku . '-' . $coun;
                            //TODO what's for
                            $product_sku = $sku;
                            $sku = $sku . '-' . $coun;
                            $product_build[$sku]['sku'] = $product_sku;
                            $product_build[$sku]['product'] = $product;
                            $max_chars_message = $this->getMaxCharMessage($padded_right, $font_size_options, $font_temp);
                            $product_build[$sku]['has_message'] = 0;
                            if ((Mage::helper('giftmessage/message')->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()) ||
                             isset($answer) && (strlen($answer)>0))
                            {
                                $product_build[$sku]['has_message'] = 1;
                                if(isset($answer))
                                {
                                    $product_build[$sku]['message-from'] = '';
                                    $product_build[$sku]['message-to'] = '';
                                    $product_build[$sku]['message-content'] = $answer;
                                    $gift_message_array['items'][$sku]['message-from'] = '';
                                    $gift_message_array['items'][$sku]['message-to'] = '';
                                    $gift_message_array['items'][$sku]['message-content'] = $answer;
//                                  unset($answer);
                                }
                                else
                                {
                                    $item_msg_array = $this->getItemGiftMessage($item, $max_chars_message);
                                    $product_build[$sku]['message-from'] = $item_msg_array[0];
                                    $product_build[$sku]['message-to'] = $item_msg_array[1];
                                    $product_build[$sku]['message-content'] = $item_msg_array[2];

                                    $gift_message_array['items'][$sku]['message-from'] = $item_msg_array[0];
                                    $gift_message_array['items'][$sku]['message-to'] = $item_msg_array[1];
                                    $gift_message_array['items'][$sku]['message-content'] = $item_msg_array[2];

                                }
                                    unset($gift_msg_array);
                                    unset($token);
                                    unset($msg_line_count);
                                    unset($_giftMessage);
                                    unset($item_message_from);
                                    unset($item_message_to);
                                    unset($item_message);
                                $gift_message_array['items']['pos'] = $product_gift_message_yn;
                                $gift_message_array['items'][$sku]['printed'] = 0;

                            }
                            //TODO QUESTION is correct?
                            if ($product_sku_simple_configurable == 'configurable') {
                                // get parent sku
                                $simpleProductSku = $product_sku;
                                $_product_temp = Mage::getModel('catalog/product');
                                $simpleProductId = $_product_temp->getIdBySku($simpleProductSku);
                                $_product_temp->load($simpleProductId);
                                if ($_product_temp->getId()) {

                                    $objConfigurableProduct = Mage::getModel('catalog/product_type_configurable');
                                    $arrConfigurableProductIds = $objConfigurableProduct->getParentIdsByChild($simpleProductId);
                                    if (is_array($arrConfigurableProductIds)) {
                                        $sku_temp = '';
                                        $sku_comma = '';
                                        foreach ($arrConfigurableProductIds as $key => $productId_temp) {
                                            $product_temp = '';
                                            $product_temp = Mage::getModel('catalog/product')->load($productId_temp);
                                            $sku_temp .= $sku_comma . $product_temp->getSku();
                                            $sku_comma = ', ';
                                        }
                                        if ($sku_temp != '') $product_build[$sku]['sku'] = $sku_temp;
                                    }
                                }
                            }
                            $product_build[$sku]['product_id'] = $product_id;
                            $sku_productid[$product_sku] = $product_id;

                            $options_pre = array();
                            $options = array();
                            $options_pre = $item->getProductOptions();
                            if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
                                $periodTypeId = @$options_pre['info_buyRequest']['aw_sarp_subscription_type'];
                                $periodStartDate = @$options_pre['info_buyRequest']['aw_sarp_subscription_start'];
                            }
                            if (isset($options_pre['info_buyRequest']) && is_array($options_pre['info_buyRequest'])) {
                                unset($options_pre['info_buyRequest']['uenc']);
                                unset($options_pre['info_buyRequest']['form_key']);
                                unset($options_pre['info_buyRequest']['related_product']);
                                unset($options_pre['info_buyRequest']['return_url']);
                                unset($options_pre['info_buyRequest']['qty']);
                                unset($options_pre['info_buyRequest']['_antispam']);
                                unset($options_pre['info_buyRequest']['super_attribute']);
                                unset($options_pre['info_buyRequest']['cpid']);
                                unset($options_pre['info_buyRequest']['callback']);
                                unset($options_pre['info_buyRequest']['isAjax']);
                                unset($options_pre['info_buyRequest']['item']);
                                unset($options_pre['info_buyRequest']['original_qty']);
                                unset($options_pre['info_buyRequest']['bundle_option']);
                                $options['options'] = array();
                                if(isset($options_pre['additional_options']) && is_array($options_pre['additional_options']))
                                    $options['options'] = $options_pre['additional_options'];
                                else{

                                    if (isset($options_pre['options']) && is_array($options_pre['options'])){
                                        foreach ($options_pre['options'] as $value) {
                                            $options['options'][count($options['options'])] = $value;
                                        }
                                    }

                                    if(isset($options_pre['attributes_info']) && is_array($options_pre['attributes_info'])){
                                         foreach ($options_pre['attributes_info'] as $value) {
                                            $options['options'][count($options['options'])] = $value;
                                        }
                                    }

                                }

                            } else $options = $options_pre;

                            if (isset($options_pre['bundle_options']) && is_array($options_pre['bundle_options'])) {
                                $options['bundle_options'] = $options_pre['bundle_options'];
                            }
                            if (!(isset($options['options'])) || count($options['options']) == 0)
                                if (isset($options_pre['attributes_info']) && is_array($options_pre['attributes_info']))
                                    $options['options'] = $options_pre['attributes_info'];
                            unset($options_pre);
                            $category_label = '';
                            $shelving = '';
                            $shelving_real = '';
                            if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')){
                                $product_real = Mage::getModel("catalog/product")->load($item->getProductId());
                                $option_ebay = $this->getEbayOption($order, $product_real->getSku(),$item->getProductId());
                                if(!isset($options['options']))
                                    $options['options'] = array();
                                $options['options'] = array_merge($options['options'], $option_ebay);
                                $options['options'] = array_map('unserialize', array_unique(array_map('serialize', $options['options'])));
                            }

                            if ($filter_items_by_status == 1) {
                                $qty = $item_invoiced;
                            } else
                                if ($filter_items_by_status == 2) {
                                    $qty = $item_shiped;
                                } else {
                                    $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                                }
                            $sku_print = $product_build[$sku]['sku'];

                            # Get product's category collection object
                            //TUDU 2: OPTIMIZE HERE
                            $catCollection = $product->getCategoryCollection();

                            $categsToLinks = array();
                            # Get categories names
                            foreach ($catCollection as $cat) {
                                if ($cat->getName() != '') {
                                    $categsToLinks[] = $cat->getName();
                                }
                            }
                            $category_label = implode(', ', $categsToLinks);

                            $product_build[$sku]['%category%'] = $category_label;
                            unset($category_label);

                            $product_build[$sku]['shelving'] = '';
                            if ($shelving_yn == 1 && $shelving_attribute != '' && $product->offsetExists($shelving_attribute)) {
                                $attributeName = $shelving_attribute;
                                if($item->getData($shelving_attribute) != ''){
                                    $product_build[$sku]['shelving'] = $item->getData($shelving_attribute);
                                    if($product_build[$sku]['shelving'] == 0)
                                       $product_build[$sku]['shelving'] = 'No';
                                    if($product_build[$sku]['shelving'] == 1)
                                       $product_build[$sku]['shelving'] = 'Yes';
                                }else{
                                    if ($attributeName == '%category%') {
                                        $product_build[$sku]['shelving'] = $product_build[$sku]['%category%']; //$category_label;
                                    } else{
                                        if ($this->_general['non_standard_characters']!=0){
                                            $product_build[$sku]['shelving'] = $this->getProductAttributeValue($product, $shelving_attribute,false);
                                        }else $product_build[$sku]['shelving'] = $this->getProductAttributeValue($product, $shelving_attribute);
                                    }
                                }
                            }

                            $product_build[$sku]['shelving2'] = '';
                            if ($shelving_2_yn == 1 && $shelving_2_attribute != '' && $product->offsetExists($shelving_2_attribute)) {
                                $attributeName = $shelving_2_attribute;
                                if($item->getData($shelving_2_attribute) != ''){
                                    $product_build[$sku]['shelving2'] = $item->getData($shelving_2_attribute);
                                    if($product_build[$sku]['shelving2'] == 0)
                                       $product_build[$sku]['shelving2'] = 'No';
                                    if($product_build[$sku]['shelving2'] == 1)
                                       $product_build[$sku]['shelving2'] = 'Yes';
                                }else{
                                    if ($attributeName == '%category%') {
                                        $product_build[$sku]['shelving2'] = $product_build[$sku]['%category%']; //$category_label;
                                    } else{
                                        if ($this->_general['non_standard_characters']!=0){
                                            $product_build[$sku]['shelving2'] = $this->getProductAttributeValue($product, $shelving_2_attribute,false);
                                        }else $product_build[$sku]['shelving2'] = $this->getProductAttributeValue($product, $shelving_2_attribute);
                                    }
                                }
                            }
                            $product_build[$sku]['shelving3'] = '';
                            if ($shelving_3_yn == 1 && $shelving_3_attribute != '' && $product->offsetExists($shelving_3_attribute)) {
                                $attributeName = $shelving_3_attribute;
                                if($item->getData($shelving_3_attribute) != ''){
                                    $product_build[$sku]['shelving3'] = $item->getData($shelving_3_attribute);
                                    if($product_build[$sku]['shelving3'] == 0)
                                       $product_build[$sku]['shelving3'] = 'No';
                                    if($product_build[$sku]['shelving3'] == 1)
                                       $product_build[$sku]['shelving3'] = 'Yes';
                                }else{
                                    if ($attributeName == '%category%') {
                                        $product_build[$sku]['shelving3'] = $product_build[$sku]['%category%'];
                                    } else {
                                        if ($this->_general['non_standard_characters']!=0){
                                            $product_build[$sku]['shelving3'] = $this->getProductAttributeValue($product, $shelving_3_attribute,false);
                                        }else $product_build[$sku]['shelving3'] = $this->getProductAttributeValue($product, $shelving_3_attribute);
                                    }
                                }
                            }
                            //TODO for sort first
                            if ($sort_packing != 'none' && $sort_packing != '') {
                                $product_build[$sku][$sort_packing] = $this->createArraySort($sort_packing,$product_build, $sku,$product_id, $trim_names_yn);
                            }
                            //TODO for sort secondary
                            if ($sort_packing_secondary != 'none' && $sort_packing_secondary != '') {
                                $product_build[$sku][$sort_packing_secondary] = $this->createArraySort($sort_packing_secondary,$product_build, $sku,$product_id, $trim_names_yn);
                            }

                            $product_build[$sku]['shelving_real'] = '';
                            if ($shelving_real_yn == 1 && $shelving_real_attribute != '' && $product->offsetExists($shelving_real_attribute)) {
                                $attributeName = $shelving_real_attribute;

                                if ($attributeName == '%category%') {
                                    $product_build[$sku]['shelving_real'] = $product_build[$sku]['%category%'];
                                } else {
                                    if ($this->_general['non_standard_characters']!=0){
                                        $product_build[$sku]['shelving_real'] = $this->getProductAttributeValue($product, $shelving_real_attribute,false);
                                    }else $product_build[$sku]['shelving_real'] = $this->getProductAttributeValue($product, $shelving_real_attribute);
                                }

								if($custom_round_yn != '0')
								{
									$shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
								}
                            }
                            if ($product_options_yn != 'no') {
                                if (isset($options['options']) && is_array($options['options'])) {
                                    $i = 0;
                                    if (isset($options['options'][$i])) $continue = 1;
                                    else $continue = 0;

                                    while ($continue == 1) {
                                        if (trim($options['options'][$i]['label'] . $options['options'][$i]['value']) != '') {
                                            if ($i > 0) $custom_options_output .= ' ';
                                            if(isset($options['options'][$i]['option_id'])){
                                                $options_store = $this->getOptionProductByStore($store_view, $helper, $product_id, $store_id, $specific_store_id, $options, $i);
                                                $options['options'][$i]['label'] = $options_store["label"];
                                                $options['options'][$i]['value'] = $options_store["value"];
                                            }
                                            if ($product_options_yn == 'yescol') {
                                                $custom_options_output .= htmlspecialchars_decode($options['options'][$i]['value']);
                                            } else {
                                                $custom_options_output .= htmlspecialchars_decode('[ ' . $options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value'] . ' ]');
                                            }
                                        }
                                        $i++;
                                        if (isset($options['options'][$i])) $continue = 1;
                                        else $continue = 0;
                                    }
                                } elseif (is_array($options)) {
                                    unset($options['product']);
                                    foreach ($options as $attribute_code => $value) {
                                        if ($attribute_code != "bundle_options") {
                                            while (is_array($value))
                                                $value = reset($value);
                                            if (is_string($value) && trim($value) != '') {
                                                if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
                                                    if ($attribute_code == 'aw_sarp_subscription_type') {
                                                        if (($periodTypeId > 0) && $periodStartDate) {
                                                            $value = Mage::getModel('sarp/period')->load($periodTypeId)->getName();
                                                        }
                                                    }
                                                }

                                                if ($product_options_yn == 'yescol') {
                                                    $custom_options_output .= htmlspecialchars_decode($value);
                                                } else {
                                                    // TODO should show label here
                                                    $custom_options_output .= htmlspecialchars_decode('[ ' . str_replace(array('aw_sarp_subscription_type', 'aw_sarp_subscription_start'), array('Subscription type', 'First delivery'), $attribute_code) . ' : ' . $value . ' ]');
                                                }
                                            }
                                        }
                                    }

                                }
                            }

                            $sku_bundle_real = '';
                            if (isset($options['bundle_options'])) {
                                if (is_array($options['bundle_options'])) {
                                    $sku_bundle_real = $sku_print;
                                    $bundle_options_sku = 'SKU : ' . $sku_print;
                                    $sku_print = $helper->__('(Bundle)');
                                    $bundle_sku_test = $sku;
                                }
                            }

                            if (Mage::helper('core')->isModuleEnabled('Magik_Magikfees') === TRUE) {
                                $farr = unserialize($item->getPaymentFee());
                                foreach ($farr as $fkey => $fval) {
                                    $Magikfee += $fval[0];
                                }
                                if ($Magikfee != 0) {

                                    if ($Magikfee != '') {
                                        $subtotal_addon['magikfee'] += $Magikfee;
                                        $magik_product_str[$itemId] = implode("\n", array_values(array_filter(unserialize($item->getPaymentStr()))));
                                    }
                                }
                            }

                            if($show_gift_wrap_yn == 1){
                                $show_item_gift = false;
                                if($item->getData('gw_id')){
                                    $show_item_gift = $item->getData('gw_id');
                                    if($show_gift_wrap_top_right)
                                        $show_top_right_gift_icon = true;
                                }
                                $product_build[$sku]['show_item_gift'] = $show_item_gift;
                            }

                            $name = '';
                            $product_stock_qty = 0;

                            //TUDO OPTIMIZE HERE
                            if ($product && $configurable_names == 'simple') {
                                switch ($store_view) {
                                    case 'itemname':
                                        $_newProduct =$helper->getProduct($product_id);
                                        $name = trim($item->getName());
                                        break;
                                    case 'default':
                                        $_newProduct = $helper->getProduct($product_id);
                                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                                        if ($name == '') $name = trim($item->getName());
                                        break;
                                    case 'storeview':
                                        $_newProduct = $helper->getProductForStore($product_id, $storeId);
                                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                                        if ($name == '') $name = trim($item->getName());
                                        break;
                                    case 'specificstore':
                                        $_newProduct = $helper->getProductForStore($product_id,$specific_store_id);
                                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                                        if ($name == '') $name = trim($item->getName());
                                        break;
                                    default:
                                        $_newProduct =$helper->getProduct($product_id);
                                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                                        if ($name == '') $name = trim($item->getName());
                                        break;
                                }
                            }
                            else {
                                if ($store_view == "storeview")
                                    $name = trim($item->getName());
                                else
                                    $name = $this->getNameDefaultStore($item);
                                $_newProduct = $helper->getProductForStore($product_id, $storeId);
                                if($store_view == "specificstore" && $specific_store_id != ""){
                                    $_Product = $helper->getProductForStore($product_id, $specific_store_id);
                                    if ($_Product->getData('name')) $name = trim($_Product->getData('name'));
                                    if ($name == '') $name = trim($item->getName());
                                }
                            }

                            if ($product_stock_qty_yn == 1){
                                $product_stock_qty = (int)($_newProduct->getStockItem()->getQty());

                                if($this->_getConfig('location_specific_stock_yn', 0, false, $wonder, $store_id)){
                                    if (strpos($order->getData('shipping_method'),'storepickup') !== false){
                                        try {
                                            $resource = Mage::getSingleton('core/resource');
                                            $readConnection = $resource->getConnection('core_read');
                                            $_newProduct_id = $_newProduct->getId();
                                            $stock_id = $item->getData('stock_id');
                                            if (isset($_newProduct_id) && isset($stock_id)){
                                                $query = 'SELECT * FROM '.Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item').' WHERE stock_id = '.$stock_id.' AND product_id = '.$_newProduct_id;
                                                $results = $readConnection->fetchAll($query);
                                                $product_stock_qty = (int)$results[0]['qty'];
                                            }
                                            unset($_newProduct_id);
                                            unset($stock_id);
                                        } catch (Exception $e) {

                                        }
                                    }
                                }
                            }

                            $product_build[$sku]['product_stock_qty'] = $product_stock_qty;
                           $product_build[$sku]['product_qty_backordered'] = 0;
                            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                                $children_items = $item->getChildrenItems();
                                foreach($children_items as $children_item)
                                {
                                    if (version_compare($magentoVersion, '1.7', '>=')){
                                    //version is 1.6 or greater
                                    $current_product_qty = $children_item->getProduct()->getStockItem()->getQty();
                                    }
                                    else {
                                        //version is below 1.7
                                        $_newProduct2 = $helper->getProductForStore($children_item->getData('product_id'), $storeId);
                                        $current_product_qty = $_newProduct2->getStockItem()->getQty();
                                    }

                                    $ordered_product_qty = $children_item->getData('qty_ordered');
                                    if($current_product_qty < 0)
                                    {
                                        if(($current_product_qty+$ordered_product_qty)<=0)
                                        {
                                            $product_build[$sku]['product_qty_backordered'] += round($ordered_product_qty,0);
                                        }
                                        else
                                            $product_build[$sku]['product_qty_backordered'] += round($current_product_qty+$ordered_product_qty,0);
                                    }
                                }
                            }
                            else
                            {

                                if (version_compare($magentoVersion, '1.7', '>=')){
                                $current_product_qty = $item->getProduct()->getStockItem()->getQty();
                                }
                                else {
                                    $_newProduct2 = $helper->getProductForStore($item->getData('product_id'), $storeId);
                                    $current_product_qty = $_newProduct2->getStockItem()->getQty();
                                }

                                $ordered_product_qty = $item->getData('qty_ordered');
                                if($current_product_qty < 0)
                                {
                                    if(($current_product_qty+$ordered_product_qty)<=0)
                                    {
                                        $product_build[$sku]['product_qty_backordered'] = round($ordered_product_qty,0);
                                    }
                                    else
                                        $product_build[$sku]['product_qty_backordered'] = round($current_product_qty+$ordered_product_qty,0);
                                }
                            }

                            /**Warehouse of each item**/
                            $product_build[$sku]['item_warehouse'] = $item->getWarehouseTitle();
                            /**Warehouse of each item**/
                            if ($from_shipment == 'shipment') {
                                switch ($show_qty_options) {
                                    case 1:
                                        $price_qty = $qty;
                                        $productXInc = 0;
                                        break;
                                    case 2:
                                        $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                        $productXInc = 25;
                                        break;
                                    case 3:
                                        $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                        $productXInc = 25;
                                        break;
                                }

                                $price_qty = (int)$item->getQtyShipped();
                                $productXInc = 25;
                            } else {
                                switch ($show_qty_options) {
                                    case 1:
                                        $price_qty = $qty;
                                        $productXInc = 0;
                                        break;
                                    case 2:
                                        $price_qty = (int)$item->getQtyShipped();
                                        $productXInc = 25;
                                        break;
                                    case 3:
                                        $price_qty = (int)$item->getQtyShipped();
                                        $productXInc = 25;
                                        break;
                                }
                                $productXInc = 0;
                                $shiped_items_qty = '';
                            }
                            /***get qty string**/
                            $qty_string = $this->getQtyString($from_shipment, $shiped_items_qty, $item, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids);
                            $this->_item_qty_array[$item->getData('product_id')] = $qty_string;
                            $children_items = $item->getChildrenItems();
                            if($children_items > 0)
                            {
                                foreach($children_items as $child)
                                {
                                    $this->_item_qty_array[$child->getData('product_id')] = $child->getData('qty_ordered');
                                }
                                unset($children_items);
                            }
                            if ($show_qty_options == 2 && !$order_invoice_id && !$shipment_ids)
                                $price_qty = $qty;
                            else
                                $price_qty = $qty_string;

                            $display_name = '';
                            $name_length = 0;

                            $test_name = $name;
                            switch ($this->_general['font_family_body']) {
                                case 'helvetica':
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    break;

                                case 'times':
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                                    break;

                                case 'courier':
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                                    break;

                                default:
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    break;
                            }
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                            $name = clean_for_pdf($name);

                            $product_build[$sku]['display_name'] = $name;
                            $product_build[$sku]['qty_string'] = $qty_string;
                            $product_build[$sku]['sku_print'] = $sku_print;
                            if (isset($options['bundle_options']))
                                $product_build[$sku]['sku_bundle_real'] = $sku_bundle_real;

                            if($serial_code_yn == 1){
                                $product_build[$sku]["serial_code"] = $this->getSerialCode($order, $item);
                            }

                            if ($prices_yn != '0') {
                                $item_discount = 0;
                                $item_discount_title = '';
                                $item_discount_label = '';
                                if ($discount_line_or_subtotal == 'line') {
                                    $item_discount = $item->getDiscountAmount();
                                }
                                if (((float)$item->getDiscountAmount()) != 0) {
                                    $oCoupon = Mage::getModel('salesrule/coupon')->load($item_discount_label, 'code');
                                    $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
                                    if (($discount_line_or_subtotal == 'line') && ($oRule->getData('simple_action') == 'cart_fixed')) {
                                        $discount_line_or_subtotal = 'subtotal';
                                        $item_discount = 0;
                                    }
                                }
                                $gift_card_array['code'] = '';
                                $gift_card_array['width'] = 0;
                                if (Mage::helper('pickpack')->isInstalled('Webtex_Giftcards')) {

                                    if ($order->getDiscountDescription()) $gift_card_array['code'] = trim($order->getDiscountDescription());
                                    if ($gift_card_array['code'] != '') {
                                        $gift_card_array['code'] = ' (' . $gift_card_array['code'] . ')';
                                        $gift_card_array['width'] = widthForStringUsingFontSize($gift_card_array['code'], $this->_general['font_family_body'], $this->_general['font_size_body']);
                                    }
                                }

                                //  qty     unit-price      unit-tax        combined-subtotal
                                //  1       10              5               10      << this would have tax added in combined subtotal
                                //  1       10              5               15

                                // price_unit = price for one item
                                $price_unit_unrounded = ($item->getPrice() - $item_discount);

                                if ($item->getPriceInclTax()) {
                                    $price_unit_plus_tax_unrounded = ($item->getPriceInclTax() - $item_discount);
                                } else {
                                    $qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
                                    $price = (floatval($qty)) ? ($item->getRowTotal() + $item->getTaxAmount()) / $qty : 0;
                                    $price_unit_plus_tax_unrounded = ($price - $item_discount);
                                }

                                if ($price_unit_plus_tax_unrounded < $price_unit_unrounded) {
                                    // some bug, but taxed !< untaxed, so...
                                    $price_unit_unrounded = $price_unit_plus_tax_unrounded;
                                }
                                if($multi_prices_yn == 1 && $order_attribute_value != ''){
                                    $order_attribute_value_dec = str_replace('%', '', $order_attribute_value) / 100;
                                    $price_unit_unrounded = $price_unit_unrounded * $order_attribute_value_dec;
                                    $price_unit_plus_tax_unrounded = $price_unit_plus_tax_unrounded * $order_attribute_value_dec;
                                }
                                $price_discount_unrounded += $item->getDiscountAmount();
                                $price_unrounded = ($price_qty * (round($price_unit_unrounded, 2) + $Magikfee)); //< round is for returning correct numbers in subtotal
                                $price_plus_tax_unrounded = ($price_qty * ($price_unit_plus_tax_unrounded + $Magikfee));
                                $price_unit = number_format($price_unit_unrounded, 2, '.', ',');
                                $price_unit_plus_tax = number_format($price_unit_plus_tax_unrounded, 2, '.', ',');
                                if (isset($show_subtotal_options) && $show_subtotal_options == 1 && $multi_prices_yn == 0) {
                                    $price = $item->getRowTotal();
                                    $price_plus_tax = $item->getRowTotalInclTax();
                                } else {
                                    $price = number_format($price_unrounded, 2, '.', ',');
                                    $price_plus_tax = number_format($price_plus_tax_unrounded, 2, '.', ',');
                                }
                                $price_ex_vat_unrounded = ($price_qty * ($item->getPrice() + $Magikfee));
                                $price_ex_vat = number_format($price_ex_vat_unrounded, 2, '.', ',');

                                $tax_percent_temp = 0;
                                $price_unit_tax = 0;
                                $price_unit_taxed = 0;

                                $tax = Mage::getModel('tax/calculation');
                                $taxClassId = $product->getTaxClassId();
                                $rates = $tax->load($taxClassId, 'product_tax_class_id');
                                $taxCalculationRate = Mage::getModel('tax/calculation_rate')->load($rates['tax_calculation_rate_id']);

                                if ($item->getTaxPercent())
                                    $tax_percent_temp = trim($item->getTaxPercent());
                                if ($tax_percent_temp > 0) {
                                    $tax_percent_temp = number_format($tax_percent_temp, 2, '.', '');
                                } elseif ($tax_percent_temp != 0)
                                    $tax_percent_temp = $helper->__('Other');

                                if ($tax_percent_temp > 0) {
                                    $price_unit_tax = $item->getTaxAmount();
                                    $price_unit_taxed_b = number_format($item->getTaxAmount(), 2, '.', ',');
                                } else {
                                    $price_unit_taxed = 0;
                                    $price_unit_taxed_b = 0;
                                }
                                //TODO Moo update 2
                                if ($tax_percent_temp >= 0) {
                                    $tax_percent_temp = preg_replace('~\.0(.*)$~', '', $tax_percent_temp);
                                    $tax_percent_temp = preg_replace('~\.1(.*)$~', '', $tax_percent_temp);
                                    $tax_rate = explode("-", $taxCalculationRate['code']);
                                    $tax_rate_code[$tax_percent_temp] = isset($tax_rate[1]) ? $tax_rate[1] : $tax_rate[0];
                                    if (!isset($tax_percents[$tax_percent_temp])) $tax_percents[$tax_percent_temp] = $price_unit_tax;
                                    else $tax_percents[$tax_percent_temp] = ($price_unit_tax + $tax_percents[$tax_percent_temp]);

                                    if(!isset($tax_percents_total[$tax_percent_temp])) $tax_percents_total[$tax_percent_temp] = $price;
                                    else $tax_percents_total[$tax_percent_temp] = $tax_percents_total[$tax_percent_temp] + $price;
                                }

                                $product_build[$sku]['tax_each'] = $this->formatPriceTxt($order, $price_unit_taxed);
                                $product_build[$sku]['tax_each_method_b'] = $this->formatPriceTxt($order, $price_unit_taxed_b);
                                $product_build[$sku]['price_each'] = $this->formatPriceTxt($order, $price_unit);
                                $product_build[$sku]['price_each_plus_tax'] = $this->formatPriceTxt($order,$price_unit_plus_tax); // single item price
                                $product_build[$sku]['price_each_no_tax'] = $this->formatPriceTxt($order, $price_unit - $price_unit_taxed);
                                $product_build[$sku]['allowance'] = $this->formatPriceTxt($order, ($price_unit) * $show_allowance_multiple);
                                $product_build[$sku]['price'] = $this->formatPriceTxt($order, $price);
                                $product_build[$sku]['price_unformat'] = $price;
                                $product_build[$sku]['price_plus_tax'] = $this->formatPriceTxt($order,$price_plus_tax); //total item price
                                $product_build[$sku]['price_plus_tax_unformat'] = $price_plus_tax; //total item price unformat
                                $product_build[$sku]['price_discount_unformat'] = (float)$item->getDiscountAmount(); //total item price unformat

                                $total_qty = ($total_qty + $price_qty);
                                $total_price = ($total_price + $price_unrounded);
                                $total_price_taxed = ($total_price_taxed + $price_plus_tax_unrounded);
                                $total_price_ex_vat = ($total_price_ex_vat + $price_ex_vat_unrounded);
                            }

                            if ($custom_options_output != '') {
                                $custom_options_title = '';
                                $product_build[$sku]['custom_options_title_output'] = strip_tags($custom_options_title . $custom_options_output);
                            }

                            if (($bundle_children_yn == 1) && isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                                $product_build[$sku]['bundle_options_sku'] = $bundle_options_sku;
                                $product_build[$sku]['bundle_children'] = $item->getChildrenItems();
                                if($prices_yn != '0')
                                    foreach ($item->getChildrenItems() as $key => $child) {
                                        if ($child->getTaxPercent())
                                            $tax_percent_temp = trim($child->getTaxPercent());
                                        if ($tax_percent_temp > 0) {
                                            $tax_percent_temp = number_format($tax_percent_temp, 2, '.', '');
                                        } elseif ($tax_percent_temp != 0)
                                            $tax_percent_temp = $helper->__('Other');

                                        if ($tax_percent_temp > 0) {
                                            $price_unit_tax = $child->getTaxAmount();
                                            $price_unit_taxed_b = number_format($child->getTaxAmount(), 2, '.', ',');
                                        } else {
                                            $price_unit_taxed = 0;
                                            $price_unit_taxed_b = 0;
                                        }
                                        if ($tax_percent_temp > 0) {
                                            $tax_percent_temp = preg_replace('~\.0(.*)$~', '', $tax_percent_temp);
                                            $tax_percent_temp = preg_replace('~\.1(.*)$~', '', $tax_percent_temp);
                                            $tax_rate = explode("-", $taxCalculationRate['code']);
                                            $tax_rate_code[$tax_percent_temp] = isset($tax_rate[1]) ? $tax_rate[1] : $tax_rate[0];
                                            if (!isset($tax_percents[$tax_percent_temp])) $tax_percents[$tax_percent_temp] = $price_unit_tax;
                                            else $tax_percents[$tax_percent_temp] = ($price_unit_tax + $tax_percents[$tax_percent_temp]);

                                            if(!isset($tax_percents_total[$tax_percent_temp])) $tax_percents_total[$tax_percent_temp] = $price;
                                            else $tax_percents_total[$tax_percent_temp] = $tax_percents_total[$tax_percent_temp] + $price;
                                        }
                                    }
                                $product_build[$sku]['bundle_qty_shipped'] = (int)$item->getQtyShipped();
                                $product_build[$sku]['bundle_qty_invoiced'] = (int)$item->getQtyInvoiced();
                            }

                            $product_build[$sku]['itemId'] = $itemId;

                            if ($shipment_details_yn == 1) {
                                $weight = ($price_qty * $item->getWeight());
                                $total_weight = ($total_weight + $weight);
                            }
                            $this->y -= 15;

                            $counter++;

                        } // end if hide

                        unset($options);
                        $coun++;
                    } // end items

                    /*SORT ITEMS BEFOR PRINTING*/

                    if ($sort_packing != 'none') {
                        $sortorder_packing_bool = false;
                        if ($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
                        //$sort_packing_secondary = 'none';
                        if($sort_packing_secondary == 'none' || $sort_packing_secondary == ''){
                            sksort($product_build, $sort_packing, $sortorder_packing_bool);
                            sksort($product_build_item, $sort_packing, $sortorder_packing_bool);
                        }
                        else{
                            $sortorder_packing_secondary_bool = false;
                            if ($sortorder_packing_secondary == 'ascending') $sortorder_packing_secondary_bool = true;
                $this->sortMultiDimensional($product_build, $sort_packing, $sort_packing_secondary, $sortorder_packing_bool, $sortorder_packing_secondary_bool);
                        }
                    }
                    if ($background_color_subtitles == '#FFFFFF') $this->y += $this->_general['font_size_body'];
                    $product_count = 0;
                    $min_product_y = 0;
                    $options_y_counter = 0;
                    $page_count = 1;
                    $this->y = $items_y_start;
                    $next_product_line_ypos = null;
                    $temp_count = 0;

                    $order_subtotal_value = 0;
                    $vat_rateable_value = 0;
                    $zero_rate_value = 0;
                    $order_item_count = 0;
                    $font_color_body_temp = $this->_general['font_color_body'];
                    $hide_bundle_parent_f = false;
            $childArray = array();
            $bundle_children_split = $this->_getConfig('split_bundles', 7, false, $wonder, $store_id);
                    /***************************PRINTING EACH ITEM**********************/

                    foreach ($product_build as $key => $product_build_value) {
                    $product_sku_md5 = md5($product_build_value['product']->getData('sku'));
                        if($show_bundle_parent_yn != 1 && isset($product_build_value['bundle_options_sku']))
                            $hide_bundle_parent_f = true;
                        if($new_pdf_per_name_yn==0 || ($new_pdf_per_name_yn==1 && $product_build_value["sku_print"]== $sku_array[count($sku_array) - $count_item])){
                        if (isset($product_build_value['bundle_options_sku']) && isset($product_build_value['sku_bundle_real'])) // after
                            $sku_real = $product_build_value['sku_bundle_real'];
                        else
                            $sku_real = $product_build_value['sku_print'];
                        $is_show_zero_qty = false;
                        if ((!is_numeric($product_build_value['qty_string']) || ($product_build_value['qty_string'] > 0) || ($show_zero_qty_options == 1) || $show_zero_qty_options == 2))
                            $is_show_zero_qty = true;

                        if ((!$order_invoice_id || $this->checkItemBelongInvoiceDetail($sku_real, $order_invoice_id)) && (!$shipment_ids || $this->checkItemBelongShipment($sku_real, $shipment_ids)) && $is_show_zero_qty) {
                            /****draw gray line for qty=0***/
                            if ($show_zero_qty_options == 1 && (int)$product_build_value['qty_string'] == 0)
                                $this->_general['font_color_body'] = $grayout_color;
                            else
                                $this->_general['font_color_body'] = $font_color_body_temp;
                            $temp_count++;
                            $min_product_y = 10;
                            $print_item_count++; //count item is showed.
                            $order_item_count = $order_item_count + $product_build_value['qty_string'];
                            /***************************FIRST PAGE SETTING**********************/
                            if ($page_count == 1) {
                                if ($bottom_shipping_address_yn == 1 || $this->_packingsheet['pickpack_return_address_yn'] == 1) {
                                    $min_product_y = ($addressFooterXY[1] + ($font_size_shipaddress * 2));
                                    if ($shipaddress_packbarcode_yn == 1)
                                        $min_product_y = $addressFooterXY[1] + $barcode_font_size + 5 - ($left_down / 2) + $bottom_barcode_nudge[1];

                                }
                                if (!empty($minY)) {
                                    if (max($minY) > $min_product_y)
                                        $min_product_y = max($minY);
                                }
                                if ($page_1_products_y_cutoff > $min_product_y) $min_product_y = $page_1_products_y_cutoff;
                            }
                            $product_count++;
                            $sku = $product_build_value['sku'];
                            $itemId = $product_build_value['itemId'];

                            /*************************CHECKING NEED TO CREATE NEW PAGE OR NOT**************************/
                            if ( ($this->y < $page_bottom) ||  ($this->y < ( $min_product_y ))) {
                                if ($page_count != 1 || $this->_packingsheet['pickpack_return_address_yn'] != 0 || $bottom_shipping_address_yn != 0) {
                                    $min_product_y = 10;
                                    if ($shipaddress_packbarcode_yn == 1) $min_product_y += 20;
                                }
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);

                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                else $this->y = $page_top;

                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');

                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                /*************************PRINT LINE BAR AT BEGIN NEW PAGE**************************/
                                if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                    $line_widths = explode(",", $this->_getConfig('bottom_line_width', '1,2', false, 'general', $store_id));
                                    $page->setFillColor($background_color_subtitles_zend);
                                    $page->setLineColor($background_color_subtitles_zend);
                                    $page->setLineWidth(0.5);

                                    if ($fill_product_header_yn == 0) {
                                        switch( $fill_bars_options ){
                                            case 0 :
                                                $page->drawLine($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2), ($padded_right), ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2));
                                                $page->drawLine($padded_left, ($this->y + $this->_general['font_size_subtitles'] + 2 + 2), ($padded_right), ($this->y + $this->_general['font_size_subtitles'] + 2 + 2));
                                                break;
                                            case 1 :
                                                if ($invoice_title_linebreak <= 1) {
                                                    $bottom_fillbar = ceil($this->y - ($this->_general['font_size_subtitles'] / 2) - 2) + $fillbar_padding[1];
                                                    $top_fillbar = ceil($this->y + $this->_general['font_size_subtitles'] + 2 + 2) + $fillbar_padding[0] ;
                                                    if(isset($line_widths[0]) && $line_widths[0] > 0){
                                                        $page->setLineWidth($line_widths[0]);
                                                        $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                                    }
                                                    if(isset($line_widths[1]) && $line_widths[1] > 0){
                                                        $page->setLineWidth($line_widths[1]);
                                                        $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                                    }
                                                }
                                                break;
                                            case 2 :
                                                break;
                                        }
                                    } else {
                                        switch( $fill_bars_options ){
                                            case 0 :
                                                $page->drawRectangle($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ($this->y + $this->_general['font_size_subtitles'] + 2));
                                                break;
                                            case 1 :
                                                if ($invoice_title_linebreak <= 1) {
                                                    $bottom_fillbar = ceil($this->y - ($this->_general['font_size_subtitles'] / 2))  + $fillbar_padding[1];
                                                    $top_fillbar = ceil($this->y + $this->_general['font_size_subtitles'] + 2) + $fillbar_padding[0] ;
                                                    if(isset($line_widths[0]) && $line_widths[0] > 0){
                                                        $page->setLineWidth((int)$line_widths[0]);
                                                        $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                                    }
                                                    if(isset($line_widths[1]) && $line_widths[1] > 0){
                                                        $page->setLineWidth((int)$line_widths[1]);
                                                        $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                                    }
                                                }
                                                break;
                                            case 2 :
                                                break;
                                        }
                                    }
                                }
                                /*************************END PRINT LINE BAR AT BEGIN NEW PAGE**************************/

                                $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);


                                if ($product_images_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($images_title), $imagesX, $this->y, 'UTF-8');
                                }
                                $page->drawText(Mage::helper('sales')->__($qty_title), $qtyX + 8, $this->y, 'UTF-8');
                                if ($show_name_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($items_title), ($productX + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
                                }

                                if($show_gift_wrap_yn == 1){
                                    $page->drawText(Mage::helper('sales')->__($gift_wrap_title), ($gift_wrap_xpos + $first_item_title_shift_items), $this->y, 'UTF-8');
                                }

                                if ($serial_code_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($serial_code_title), ($serial_codeX + $first_item_title_shift_items), $this->y, 'UTF-8');
                                }
                                if ($product_sku_yn == 1) $page->drawText(Mage::helper('sales')->__($sku_title), ($skuX), $this->y, 'UTF-8');

                                if ($product_sku_barcode_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_title), ($sku_barcodeX - 1), $this->y, 'UTF-8');

                                if ($product_sku_barcode_2_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_2_title), ($sku_barcodeX_2 - 1), $this->y, 'UTF-8');

                                if ($product_stock_qty_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($product_stock_qty_title), ($stockqtyX), $this->y, 'UTF-8');
                                }

                                if ($product_options_yn == 'yescol') {
                                    $page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
                                }

                                if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0) {
                                    $page->drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y, 'UTF-8');
                                }

                                if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0) {
                                    $page->drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y, 'UTF-8');
                                }

                                if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0) {
                                    $page->drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y, 'UTF-8');
                                }

                                if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0) {
                                    $page->drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y, 'UTF-8');
                                }
                                if ($combine_custom_attribute_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y, 'UTF-8');
                                }
                                if ($prices_yn != '0') {
                                    $page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
                                    $page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
                                }
                                if($show_allowance_yn == 1){
                                    $page->drawText(Mage::helper('sales')->__($show_allowance_title), $show_allowance_xpos, $this->y, 'UTF-8');
                                }
                                if ($tax_col_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($tax_title), $taxEachX, $this->y, 'UTF-8');
                                }

                                $this->y = ($this->y - 28);
                                $items_y_start = $this->y;
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }

                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            /****draw gray line for qty=0***/
                            if ($show_zero_qty_options == 1 && (int)$product_build_value['qty_string'] == 0) {
                                $page->setFillColor($greyout_color);
                                $page->drawRectangle($padded_left, ($this->y), $padded_right, ($this->y + $this->_general['font_size_body']));
                            }

                            /************************PRINTING CHECKBOX**************************/
                            if (isset($sku_supplier_item_action[$supplier][$sku]) && $sku_supplier_item_action[$supplier][$sku] != 'hide' && !$hide_bundle_parent_f) {
                                if ($sku_supplier_item_action[$supplier][$sku] == 'keepGrey') {
                                    $page->setFillColor($greyout_color);
                                } elseif (($tickbox_yn == 1) || ($tickbox_2_yn == 1)) {
                                    $page->setLineWidth(0.5);
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($black_color);
                                    if ($tickbox_yn == 1) {
                                        $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                        if ($doubleline_yn == 1.5)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                        elseif ($doubleline_yn == 2)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                        elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                        else
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                        /* tickbox 1 signature line */
                                        if ($this->_getConfig('tickbox_signature_line', 0, false, $wonder, $order_storeId)){
                                            $page->drawLine(($tickboxX - $tickbox_width_1), ($this->y + 2), ($tickboxX - ($tickbox_width_1 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                        }
                                    }
                                    if ($tickbox_2_yn == 1) {
                                        $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                        if ($doubleline_yn == 1.5)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                        elseif ($doubleline_yn == 2)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                        elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                        else
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                        /* tickbox 2 signature line */
                                        if ($this->_getConfig('tickbox_2_signature_line', 0, false, $wonder, $order_storeId)){
                                            $page->drawLine(($tickbox2X - $tickbox_width_2), ($this->y + 2), ($tickbox2X - ($tickbox_width_2 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                        }
                                    }
                                    $page->setFillColor($black_color);
                                }
                            } elseif ((($tickbox_yn == 1) || ($tickbox_2_yn == 1)) && !$hide_bundle_parent_f) {
                                $page->setLineWidth(0.5);
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                if ($tickbox_yn == 1) {
                                    $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                    if ($doubleline_yn == 1.5)
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                    elseif ($doubleline_yn == 2)
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                    elseif ($doubleline_yn == 3)
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                    else
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                    $after_print_ticbox1 = $this->y - $tickbox_width_1 + $this->_general['font_size_body'];
                                    /* tickbox 1 signature line */
                                    if ($this->_getConfig('tickbox_signature_line', 0, false, $wonder, $order_storeId)){
                                        $page->drawLine(($tickboxX - ($tickbox_width_1 - 2)), ($this->y + 2), ($tickboxX - ($tickbox_width_1 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                    }
                                }
                                if ($tickbox_2_yn == 1) {
                                    $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                    if ($doubleline_yn == 1.5)
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                    elseif ($doubleline_yn == 2)
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                    elseif ($doubleline_yn == 3)
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] /3), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                    else
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                    $after_print_ticbox2 = $this->y - $tickbox_width_2 + $this->_general['font_size_body'];
                                    /* tickbox 2 signature line */
                                    if ($this->_getConfig('tickbox_2_signature_line', 0, false, $wonder, $order_storeId)){
                                        $page->drawLine(($tickbox2X - ($tickbox_width_2 - 2)), ($this->y + 2), ($tickbox2X - ($tickbox_width_2 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                    }
                                }
                                $page->setFillColor($black_color);
                            }
                            if ($numbered_product_list_yn == 1 && !$hide_bundle_parent_f) {
                                $page->drawText($temp_count . $numbered_list_suffix, $numbered_product_list_X, ($this->y), 'UTF-8');
                            }
                            if (!isset($max_chars)) {
                                $maxWidthPage = ($padded_right + 20) - ($productX + $productXInc + $offset);
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($font_size_options);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / 10;
                                $max_chars = round($maxWidthPage / $char_width);
                            }

                            $line_height = (1.15 * $this->_general['font_size_body']);
                            if (is_numeric($product_build_value['qty_string']))
                                $draw_qty_value = round($product_build_value['qty_string'], 2);
                            else
                                $draw_qty_value = $product_build_value['qty_string'];
                            if($this->_general['font_family_body'] == 'traditional_chinese' || $this->_general['font_family_body'] == 'simplified_chinese'){
                                $font_family_body_temp = $this->_general['font_family_body'];
                                $this->_general['font_family_body'] = 'helvetica';
                            }
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $draw_qty_value = round($product_build_value['qty_string'], 2);
                            /************************PRINTING QTY**************************/
                            if ($product_qty_upsize_yn == 1 && $product_build_value['qty_string'] > 1 && !$hide_bundle_parent_f) {
                                if ($product_qty_red == 1) $this->_setFont($page, 'bold', ($this->_general['font_size_body'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'darkRed');

                                if ($product_qty_rectangle == 1) {
                                    $page->setLineWidth(1);
                                    $page->setLineColor($black_color);
                                    $page->setFillColor($black_color);
                                    if($center_value_qty){
                                        $qty_value_center_width = widthForStringUsingFontSize( $qty_title , $page->getFont() , $page->getFontSize() + 10 ) / 2 - widthForStringUsingFontSize( $draw_qty_value , $page->getFont() , $page->getFontSize() + 10 ) / 2;
                                    }
                                    if ($product_qty_red == 1) $page->setLineColor($red_color);
                                    {

                                        if (($product_build_value['qty_string'] >= 100) || (strlen($draw_qty_value) >= 3)) {
                                            $page->drawRectangle(($qtyX + 6) + $qty_value_center_width, ($this->y - 3), ($qtyX + 9+(strlen($draw_qty_value) * 2*$this->_general['font_size_body']/3)) + $qty_value_center_width, ($this->y - 3 + $this->_general['font_size_body'] * 1.2));
                                        } else
                                            if (($product_build_value['qty_string'] >= 10) || (strlen($draw_qty_value) > 2)) {
                                                $page->drawRectangle(($qtyX + 6) + $qty_value_center_width, ($this->y - 3), ($qtyX + 9+ (strlen($draw_qty_value) * 2*$this->_general['font_size_body']/3)) + $qty_value_center_width, ($this->y - 3 + $this->_general['font_size_body'] * 1.2));
                                            } else {
                                                $page->drawRectangle(($qtyX + 6) + $qty_value_center_width, ($this->y - 3), ($qtyX + 9 + (strlen($draw_qty_value) * 2*$this->_general['font_size_body'])/3) + $qty_value_center_width, ($this->y - 3 + $this->_general['font_size_body'] * 1.2));
                                            }
                                    }
                                    $this->_setFont($page,'bold', ($this->_general['font_size_body'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'white');
                                    $page->drawText($draw_qty_value, ($qtyX + 8) + $qty_value_center_width, ($this->y - 1), 'UTF-8');
                                } else {
                                    if($center_value_qty){
                                        $qty_value_center_width = widthForStringUsingFontSize( $qty_title , $page->getFont() , $page->getFontSize() +10 ) / 2 - widthForStringUsingFontSize( $product_build_value['qty_string'] , $page->getFont() , $page->getFontSize() +10 ) / 2;
                                    }
                                    if ($product_qty_underlined == 1) {
                                        $page->setLineWidth(1);
                                        $page->setLineColor($black_color);
                                        $page->setFillColor($white_color);
                                        if ($product_qty_red == 1) $page->setLineColor($red_color);
                                        $page->drawLine(($qtyX + 7) + $qty_value_center_width, ($this->y - 3), ($qtyX + 6 + (strlen($product_build_value['qty_string']) * $this->_general['font_size_body'])) + $qty_value_center_width, ($this->y - 3));
                                    }
                                    $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $page->drawText($product_build_value['qty_string'], ($qtyX + 8) + $qty_value_center_width, ($this->y - 1), 'UTF-8');
                                }
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            } elseif(!$hide_bundle_parent_f) {
                                if($center_value_qty){
                                   $qty_value_center_width = widthForStringUsingFontSize( $qty_title , $page->getFont() , $page->getFontSize() + 10) / 2 - widthForStringUsingFontSize( $draw_qty_value , $page->getFont() , $page->getFontSize() + 10) / 2;
                                }

                               $page->drawText($draw_qty_value, ($qtyX + 8) + $qty_value_center_width, $this->y, 'UTF-8');
                            }
                            if(isset($font_family_body_temp)){
                                $this->_general['font_family_body'] = $font_family_body_temp;
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
                            /***************************PRINTING SKU**********************/
                            if ($product_sku_yn == 1 && !$hide_bundle_parent_f) {
                                $line_height = (1.15 * $this->_general['font_size_body']);
                                $temp_y = $this->y;
                                $after_print_sku_y = $this->y;
                                $line_count_sku = 0;
                                $multiline_sku = $this->skuWordwrap($minDistanceSku, $this->_general['font_size_body'], $product_build_value['sku_print']);
                                foreach ($multiline_sku as $sku_in_line) {
                                    $line_count_sku++;
                                    $page->drawText($sku_in_line, $skuX, $this->y, 'UTF-8');
                                    $this->y -= $line_height;
                                }
                                $after_print_sku_y = $temp_y - ($line_count_sku) * $line_height;
                                $this->y = $temp_y;
                            }

                            /***************************PRINTING STOCK**********************/
                            if ($product_stock_qty_yn == 1 && !$hide_bundle_parent_f) {
                                $page->drawText($product_build_value['product_stock_qty'], ($stockqtyX), $this->y, 'UTF-8');
                            }

                            /***************************PRINTING QTY BACKORDERED *************/
                            if ($product_qty_backordered_yn == 1 && !$hide_bundle_parent_f) {
                                $page->drawText($product_build_value['product_qty_backordered'], ($prices_qtybackorderedX), $this->y, 'UTF-8');
                            }
                            if($supplier_hide_attribute_column ==0 && !$hide_bundle_parent_f)
                            if ($product_warehouse_yn == 1) {
                                $page->drawText($product_build_value['item_warehouse'], ($prices_warehouseX), $this->y, 'UTF-8');
                            }

                            /***************************PRINTING BARCODE**********************/
                            if (($product_sku_barcode_yn != 0) && !$hide_bundle_parent_f) {
                                $after_print_barcode_y = $this->y;
                                $sku_barcodeY = $this->y - 4;
                                $barcode = $product_build_value['sku_print'];
                                if ($product_sku_barcode_yn == 2)
                                    $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id);
                                $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }

                            if (($product_sku_barcode_2_yn != 0) && !$hide_bundle_parent_f) {
                                $after_print_barcode_y = $this->y;
                                $barcode = $product_build_value['sku_print'];
                                if ($product_sku_barcode_2_yn == 2)
                                    $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,2);
                                $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX_2,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }



                            /***************************PRINTING PRICE************************/
                            if ($prices_yn != 0 && !$hide_bundle_parent_f) {
                                if (!isset($product_build_value['price_discount_unformat']))
                                    $price_discount_unformat = 0;
                                else
                                    $price_discount_unformat = $product_build_value['price_discount_unformat'];
                                if (isset($product_build_value['tax_each']) && $product_build_value['price_unformat'] != $product_build_value['price_plus_tax_unformat']) {
                                    $vat_rateable_value = $vat_rateable_value + (float)(str_replace(',', '', $product_build_value['price_plus_tax_unformat'])) - $price_discount_unformat;
                                } elseif ($product_build_value['price_unformat'] == $product_build_value['price_plus_tax_unformat'])
                                    $zero_rate_value = $zero_rate_value + (float)(str_replace(',', '', $product_build_value['price_unformat'])) - $price_discount_unformat;
                                if ($tax_yn == 'yessubtotal') {
                                    $page->drawText($product_build_value['price'], $priceX, $this->y, 'UTF-8');
                                    $order_subtotal_value += (float)(str_replace(',', '', $product_build_value['price_unformat']));
                                } else {
                                    $page->drawText($product_build_value['price_plus_tax'], $priceX, $this->y, 'UTF-8');
                                    $order_subtotal_value = $order_subtotal_value + (float)(str_replace(',', '', $product_build_value['price_plus_tax_unformat']));
                                }
                                if (($tax_yn != 'no') && ($tax_yn != 'noboth')) {
                                    if ($tax_col_yn == 1) {
                                        if ($tax_col_method == 'a') $page->drawText($product_build_value['tax_each'], $taxEachX, $this->y, 'UTF-8');
                                        elseif ($tax_col_method == 'b') $page->drawText($product_build_value['tax_each_method_b'], $taxEachX, $this->y, 'UTF-8');

                                        $page->drawText($product_build_value['price_each'], $priceEachX, $this->y, 'UTF-8');
                                    } else {
                                        if ($tax_yn == 'yessubtotal') {
                                            $page->drawText($product_build_value['price_each'], $priceEachX, $this->y, 'UTF-8');
                                        } else {
                                            $page->drawText($product_build_value['price_each_plus_tax'], $priceEachX, $this->y, 'UTF-8');
                                        }
                                    }
                                } elseif ($tax_yn == 'no' || $tax_yn == 'noboth') {
                                    $page->drawText($product_build_value['price_each_plus_tax'], $priceEachX, $this->y, 'UTF-8');
                                }
                            }
                            /***************************PRINTING ALLOWANCE**********************/
                            if($show_allowance_yn == 1){
                                $page->drawText($product_build_value['allowance'], $show_allowance_xpos, $this->y, 'UTF-8');
                            }
                            /***************************PRINTING NAME**********************/
                            $yPosTemp = $this->y;
                            $line_height = (1.15 * $this->_general['font_size_body']);
                            $height_print_name = 0;
                            $after_print_name_y = $this->y;
                            $next_col_to_product_x = getPrevNext2($this->columns_xpos_array, 'productX', 'next');
                            $max_name_length = $next_col_to_product_x - $productX;
                            $name = Mage::helper('pickpack/functions')->clean_method($product_build_value['display_name'], 'pdf');
                            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            if($name != "" && !$hide_bundle_parent_f){
                                $line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->_general['font_size_body']));
                                $char_width_name = ceil($line_width_name / strlen($name));
                                $max_chars_name = round($max_name_length / $char_width_name);
                                $multiline_name = wordwrap($name, $max_chars_name, "\n");
                                $name_trim = str_trim($name, 'WORDS', $max_chars_name - 3, '...');
                                $token = strtok($multiline_name, "\n");
                                if ($this->_getConfig('product_name_bold_yn', 0, false, $wonder, $order_storeId) || (Mage::getStoreConfig('pickpack_options/general/product_name_style') == 1)){
                                    $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                }
                                //$character_breakpoint_name = stringBreak($name, $max_name_length, $this->_general['font_size_body'], $font_helvetica);
                                $new_line_after_name = false;
                                $line_count = 0;
                                if ($this->_getConfig('trim_product_name_yn', 0, false, $wonder, $order_storeId)) {
                                    if ($show_name_yn == 1){
                                        // custom for print deposit label
                                        $temp_y = $this->y;
                                        $page->drawText($name_trim, ($productX + $productXInc), $this->y, 'UTF-8');
                                        $line_count++;

                                        $deposit_label = $product_build_value['product']['creare_deposit_label'];
                                        if (isset($deposit_label) && ($deposit_label != '')){
                                            $this->y -= $line_height;
                                            $page->drawText($deposit_label.' '  , ($productX + $productXInc), $this->y, 'UTF-8');
                                            $line_count++;
                                        }

                                        $height_print_name = $temp_y - $this->y;
                                        $after_print_name_y = $temp_y - $line_count * $line_height;
                                        $this->y = $temp_y;
                                    }else{
                                        $height_print_name = $this->_general['font_size_body'] * 1.4;
                                    }
                                } else {
                                    if ($show_name_yn == 1) {
                                        $token = strtok($multiline_name, "\n");
                                        $multiline_name_array = array();
                                        $temp_y = $this->y;
                                        if ($token != false) {
                                            while ($token != false) {
                                                $multiline_name_array[] = $token;
                                                $token = strtok("\n");
                                            }
                                            foreach ($multiline_name_array as $name_in_line) {
                                                $line_count++;
                                                $page->drawText($name_in_line. ' '  , ($productX + $productXInc), $this->y, 'UTF-8');
                                                $this->y -= $line_height;
                                            }
                                        } else {
                                            $page->drawText($name_in_line, ($productX + $productXInc), $this->y, 'UTF-8');
                                            $line_count++;
                                            $this->y -= $line_height;
                                        }

                                        // custom for print deposit label
                                        $deposit_label = $product_build_value['product']['creare_deposit_label'];
                                        if (isset($deposit_label) && ($deposit_label != '')){
                                            $page->drawText($deposit_label.' '  , ($productX + $productXInc), $this->y, 'UTF-8');
                                            $line_count++;
                                            $this->y -= $line_height;
                                        }

                                        $height_print_name = $temp_y - $this->y;
                                        $after_print_name_y = $temp_y - ($line_count) * $line_height;
                                        $this->y = $temp_y;
                                    } else {
                                        $height_print_name = $this->_general['font_size_body'] * 1.4;
                                        $after_print_name_y = $this->y - $this->_general['font_size_body'];
                                    }
                                }
                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }

                            /*PRINT GIFT WRAP*/
                            if($show_gift_wrap_yn == 1){
                                if($product_build_value['show_item_gift']){
                                    $gift_wrap_data = Mage::getModel('enterprise_giftwrapping/wrapping')->load($product_build_value['show_item_gift']);
                                    if($show_gift_wrap_icon == 0)
                                        $page->drawText('Yes - '.$gift_wrap_data->getData('design'), ($gift_wrap_xpos + $first_item_title_shift_items), $this->y, 'UTF-8');
                                    else{

                                            $media_path = Mage::getBaseDir('media');
                                            $image = Zend_Pdf_Image::imageWithPath($media_path.'/moogento/pickpack/gift_wrap.png');
                                            $x1 = $gift_wrap_xpos + $first_item_title_shift_items;
                                            $x2 = $gift_wrap_xpos + $first_item_title_shift_items + 13;
                                            $y1 = $this->y - 5;
                                            $y2 = $y1 +13 ;
                                            $page->drawImage($image, $x1, $y1 , $x2, $y2);
                                            $show_gift_wrap_label = $this->_getConfig('show_gift_wrap_label', 0, false, $wonder, $store_id);
                                            if($show_gift_wrap_label)
                                                $page->drawText($gift_wrap_data->getData('design'), $x2 + 2, $y1 + 2, 'UTF-8');
                                    }
                                }

                            }

                            /***************************PRINT SERIAL CODE**********************/
                            if($serial_code_yn == 1 && $product_build_value['serial_code'] != '' && !$hide_bundle_parent_f){
                                $serial_code_item = $product_build_value['serial_code'];
                                $page->drawText($serial_code_item, $serial_codeX, $this->y, 'UTF-8');
                            }

                            /***************************PRINTING OPTIONS**********************/
                            $after_print_option_y = $this->y;
                            if ($product_options_yn == 'yescol' && isset($product_build_value['custom_options_title_output']) && !$hide_bundle_parent_f) {
                                $page->drawText($product_build_value['custom_options_title_output'], $optionsX, $this->y, 'UTF-8');
                            } elseif ((($product_options_yn == 'yes') || ($product_options_yn == 'yesstacked') || ($product_options_yn == 'yesboxed')) && isset($product_build_value['custom_options_title_output']) && ($product_build_value['custom_options_title_output'] != '') && !$hide_bundle_parent_f) {
                                $temp_y = $this->y;

                                $this->y = $after_print_name_y + $this->_general['font_size_body'] * 1.4 - 2;
                                $offset = 10;
                                $this->_setFont($page, $this->_general['font_style_body'], ($font_size_options), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $maxWidthPage = ($padded_right + 20) - ($productX + $productXInc + $offset);
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($font_size_options);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / 10;
                                $max_chars = round($maxWidthPage / $char_width);
                                if (strlen($product_build_value['custom_options_title_output']) > $max_chars) {
                                    if ($product_options_yn == 'yes') $chunks = split_words($product_build_value['custom_options_title_output'], '/ /', $max_chars);
                                    elseif ($product_options_yn == 'yesstacked' || $product_options_yn == 'yesboxed')
                                        $chunks = explode(']', $product_build_value['custom_options_title_output']);
                                    if($product_options_yn == 'yesboxed'){
                                        $page->setLineWidth(1);
                                        $page->setFillColor($white_color);
                                        $page->setLineColor($black_color);
                                        foreach ($chunks as $key => $element) {
                                            if(trim($element) == '')
                                                unset($chunks[$key]);
                                        }
                                        $bottom_box_y = $this->y - count($chunks) * ($font_size_options + 2) - 4;
                                        $page->drawRectangle($productX + $productXInc + $offset - 2, ($this->y - 1),$productX + $productXInc + $maxWidthPage/2, $bottom_box_y);
                                    }
                                    $this->_setFont($page, $this->_general['font_style_body'], ($font_size_options), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    $lines = 0;
                                    foreach ($chunks as $key => $chunk) {
                                        $chunk_display = '';
                                        if (trim($chunk != '')) {
                                            $chunk = trim($chunk);

                                            if ($product_options_yn == 'yesstacked' || $product_options_yn == 'yesboxed')
                                                $chunk_display = str_replace('[', '', $chunk);
                                            else $chunk_display = $chunk;
                                            if($trim_name_yn == 1){
                                                $this->y -= ($font_size_options + 2);
                                                $options_y_counter += $font_size_options;
                                                $chunk_display = str_trim($chunk_display, 'WORDS', $max_chars + 4, '...');
                                                $page->drawText($chunk_display, ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                                                $lines++;
                                            }else{
                                                $multiline_name = wordwrap($chunk_display, $max_chars + 4, "\n");
                                                $token = strtok($multiline_name, "\n");
                                                if ($token != false) {
                                                    while ($token != false) {
                                                        $this->y -= ($font_size_options + 2);
                                                        $options_y_counter += $font_size_options;
                                                        $page->drawText($token, ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                                                        $lines++;
                                                        $token = strtok("\n");
                                                    }
                                                } else {
                                                    $this->y -= ($font_size_options + 2);
                                                    $options_y_counter += $font_size_options;
                                                    $page->drawText($token, ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                                                    $lines++;
                                                }
                                            }
                                        }
                                    }

                                    unset($chunks);
                                } else {
                                    if ($product_options_yn == 'yesstacked' || $product_options_yn == 'yesboxed') {
                                        //$chunks = explode('[', $product_build_value['custom_options_title_output']);
                                        $chunks = explode(']', $product_build_value['custom_options_title_output']);
                                        if($product_options_yn == 'yesboxed'){
                                            $page->setLineWidth(1);
                                            $page->setFillColor($white_color);
                                            $page->setLineColor($black_color);
                                            foreach ($chunks as $key => $element) {
                                                if(trim($element) == '')
                                                    unset($chunks[$key]);
                                            }
                                            $bottom_box_y = $this->y - count($chunks) * ($font_size_options + 2) - 4;
                                            $page->drawRectangle($productX + $productXInc + $offset - 2, ($this->y - 1),$productX + $productXInc + $maxWidthPage/2, $bottom_box_y);
                                        }
                                        $lines = 0;
                                        $this->_setFont($page, $this->_general['font_style_body'], ($font_size_options), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        foreach ($chunks as $key => $chunk) {
                                            $chunk_display = '';
                                            if (trim($chunk != '')) {
                                                $this->y -= ($font_size_options + 2);
                                                $options_y_counter += $font_size_options;
                                                //$chunk_display = str_replace('[[', '[', '[' . $chunk);
                                                $chunk = trim($chunk);
                                                if ($product_options_yn == 'yesstacked' || $product_options_yn == 'yesboxed')
                                                    $chunk_display = str_replace('[', '', $chunk);
                                                else $chunk_display = $chunk;
                                                $page->drawText($chunk_display, ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                                                $lines++;
                                            }
                                        }
                                        unset($chunks);
                                    } else {
                                        $this->y -= ($font_size_options + 2);
                                        $options_y_counter += $font_size_options;
                                        $page->drawText($product_build_value['custom_options_title_output'], ($productX + $productXInc + $offset), $this->y, 'UTF-8');

                                    }
                                }
                                $this->y -= $this->_general['font_size_body'];
                                $after_print_option_y = $this->y;
                                $this->y = $temp_y;
                            }
                            /***************************PRINTING COMBINE ATTRIBUTE UNDER PRODUCT LINE**************************/
                            if($combine_custom_attribute_under_product == 1){
                                $offset = 10;
                                $attribute_string = $this->getCombineAttribute($product_build_value, '', "", "", $wonder, $store_id);
                                if((($product_options_yn == 'yes') || ($product_options_yn == 'yesstacked')) && isset($product_build_value['custom_options_title_output']) && ($product_build_value['custom_options_title_output'] != '')){
                                    $this->y = $after_print_option_y;
                                    $after_print_option_y = $this->y - $this->_general['font_size_body'];
                                }
                                else{
                                    $this->y = $after_print_name_y + $this->_general['font_size_body'] * 1.4 - $font_size_options - 3;
                                }
                                $page->drawText(trim($attribute_string, ","), ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                            }
                            /*Begin Print Image,Custom Attribute, Gift Message,Product Bundle Options*/

                            /***************************PRINTING IMAGE**********************/
                            $this->y = $yPosTemp;
                            $before_print_image_y = $this->y;
                            $befor_print_image_y_newpage = $this->y;
                            $after_print_image_y = $this->y;
                            $after_print_image_y_newpage = $this->y;
                            $flag_image_newpage = 0;
                            //if ($height_print_name > 0) {
                            //    $this->y += $this->_general['font_size_body'];
                            //}


                            $has_shown_product_image = 0;
                            $options_y_counter_image = 0;
                            $img_width = 0;
                            $img_height = 0;
                            $resize_x = null;
                            $resize_y = null;
                            $has_real_image_set = null;
                            $image_product_id = null;
                            $parent_ids = array();
                            $imagePaths = array();
                            $product_images_source_res = $product_images_source;
                            if ($product_images_source == 'gallery') $product_images_source_res = 'image';
                            if ($product_images_yn == 1 && $sku_productid[$product_sku] != '' && !$hide_bundle_parent_f) {
                                $product_id = $product_build_value['product_id'];
                                $product = $product_build_value['product'];
                                if ($product_images_parent_yn == 1) {
                                    $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                                    $product = $_newProduct = $helper->getProduct($product_id);;
                                }
                                $product_images_source_res = $helper->getSourceImageRes($product_images_source, $product);
                                $img_demension = $helper->getWidthHeightImage($product, $product_images_source_res, $product_images_maxdimensions);
                                if (is_array($img_demension) && count($img_demension)) {
                                    $img_width = $img_demension[0];
                                    $img_height = $img_demension[1];
                                }
                                $imagePaths = $helper->getImagePaths($product, $product_images_source, $product_images_maxdimensions);
                                $x1 = $imagesX;
                                $y1 = ($this->y - $img_height + $image_y_nudge);
                                $x2 = ($imagesX + $img_width);
                                $y2 = ($this->y + $image_y_nudge);
                                $imagePath = '';
                                $image_x_addon = 0;
                                $image_x_addon_2 = 0;
                                $page_prev = $page;
                                $count = 1;
                                foreach ($imagePaths as $imagePath) {
                                    $imagePath = trim($imagePath);
                                    if ($imagePath != '') {
                                        $image_x_addon += ($count * ($img_width + 10)); // shift the 2nd image over
                                        $image_x_addon_2 += (($count - 1) * ($img_width + 10)); // shift the 2nd image over
                                        $count++;
                                        $media_path = Mage::getBaseDir('media');
                                        $image_url = $imagePath;
                                        $image_url_after_media_path_with_media = strstr($image_url, '/media/');
                                        $image_url_after_media_path = strstr_after($image_url, '/media/');

                                        $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                        $final_image_path2 = $media_path . '/' . $image_url_after_media_path;
                                        $image_ext = '';
                                        $image_part = explode('.', $image_url_after_media_path);
                                        $image_ext = array_pop($image_part);
                                        $image_ext = strtolower($image_ext);
                                        if (($image_ext != 'jpg') && ($image_ext != 'JPG') && ($image_ext != 'jpeg') && ($image_ext != 'png') && ($image_ext != 'PNG')) continue;
                                        //Check to print image in current page or in a new page.

                                        if (($y1 - 5) < $min_product_y) {
                                            $flag_image_newpage = 1;
                                            $page = $this->nooPage($this->_packingsheet['page_size']);
                                            $page_count++;
                                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                            if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                            else $this->y = $page_top;
                                            $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                            $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                            $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));
                                            $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                            $this->y = ($this->y - ($this->_general['font_size_subtitles']));
                                            $items_y_start = $this->y;
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                            $x1 = ($imagesX);
                                            $y1 = $this->y - $img_height - 5 +$image_y_nudge;
                                            $x2 = ($imagesX + $img_width);
                                            $y2 = $this->y - 5+$image_y_nudge;
                                            $before_print_image_y = $this->y;

                                        }
                                        if ($product_images_border_color_temp != '#FFFFFF') {
                                            $page->setLineWidth(0.5);
                                            $page->setFillColor($product_images_border_color);
                                            $page->setLineColor($product_images_border_color);
                                            $page->drawRectangle(($x1 - 1 + $image_x_addon_2), ($y1 - 1 +7), ($x2 + 1 + $image_x_addon_2), ($y2 + 1 +7));
                                            $page->setFillColor($black_color);
                                        }
                                        try {
                                            // if(!$helper->checkTypeImageProduct($final_image_path, $image_ext)){
//                                                 imagepng(imagecreatefromstring(file_get_contents($final_image_path)), $image_part[0] . '.png');
//                                                 $final_image_path2 = $final_image_path = $image_part[0] . '.png';
//                                             }
                                            $image_source = $final_image_path2;
                                            $io = new Varien_Io_File();
                                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                                            $ext = substr($image_source, strrpos($image_source, '.') + 1);
                                            if($ext != 'jpg' && $ext != 'JPG')
                                            {
                                                $image_zebra->source_path = $final_image_path2;
                                                $image_zebra->target_path = $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.jpeg';;
//                                              $image->jpeg_quality = 100;
                                                if(!(file_exists($image_zebra->target_path)))
                                                {
                                                    $size_1 = $img_width*300/72;
                                                    $size_2 = $img_height*300/72;
                                                    if (!$image_zebra->resize($size_1, $size_2, ZEBRA_IMAGE_NOT_BOXED, -1))
                                                         show_error($image_zebra->error, $image_zebra->source_path, $image_zebra->target_path);

                                                 }
                                                 $final_image_path = $image_target;
                                            }
                                            else
                                            {
                                                $ext = 'jpeg';
                                                $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.'.$ext;

                                                if(!(file_exists($image_target)))
                                                {
                                                    $size_1 = $img_width*300/72;
                                                    $size_2 = $img_height*300/72;
                                                        $image_simple->load($image_source);
                                                        $image_simple->resize($size_1,$size_2);
                                                        $image_simple->save($image_target);
                                                }
                                            $final_image_path = $image_target;
                                            }

                                            $image = Zend_Pdf_Image::imageWithPath($final_image_path);
                                            $page->drawImage($image, $x1 + $image_x_addon_2, $y1 +7, $x2 + $image_x_addon_2, $y2 + 7);
                                        } catch (Exception $e) {
                                            echo $e->getMessage(); exit;
                                            if ($product_images_border_color_temp != '#FFFFFF') {
                                                $page->setLineWidth(0.5);
                                                $page->setFillColor($white_color);
                                                $page->setLineColor($white_color);
                                                $page->drawRectangle(($x1 - 2 + $image_x_addon_2), ($y1 - 3 + 7), ($x2 + 2 + $image_x_addon_2), ($y2 + 3 + 7));
                                                $page->setFillColor($black_color);
                                            }
                                            continue;
                                        }
                                        $has_shown_product_image = 1;
                                        $after_print_image_y = $this->y - $img_height + 5 + 3 - 15 +$image_y_nudge; // $this->y;
                                        if ($flag_image_newpage)
                                            $after_print_image_y_newpage = $this->y - $img_height + 5 + 3 - 15+$image_y_nudge;;
                                    }
                                }

                                if ($has_shown_product_image == 0) {
                                    $product_images_source_res = $product_images_source;
                                    if ($product) {
                                        $image_path = $product->getImage();
                                        $image_parent_sku = $product->getSku();
                                        $has_real_image_set = ($image_path != null && $image_path != "no_selection" && $image_path != '');
                                        $image_product_id = $product_id;

                                        if (($product_images_source_res == 'thumbnail') && (!$product->getThumbnail() || ($product->getThumbnail() == 'no_selection'))) $product_images_source_res = 'image';
                                        elseif (($product_images_source_res == 'small_image') && (!$product->getSmallImage() || ($product->getSmallImage() == 'no_selection'))) $product_images_source_res = 'image';
                                        if (($product_images_source_res == 'image') && (!$product->getImage() || ($product->getImage() == 'no_selection'))) $product_images_source_res = 'small_image';
                                        if (($product_images_source_res == 'small_image') && (!$product->getSmallImage() || ($product->getSmallImage() == 'no_selection'))) $product_images_source_res = 'thumbnail';
                                        $image_galleries = $product->getData('media_gallery');
                                        if (isset($image_galleries['images'])) {
                                            if (count($image_galleries['images']) > 0) {
                                                if ($product->getData($product_images_source_res) != 'no_selection') // continue; // if no images are valid, skip it
                                                {
                                                    try{
                                                        $image_obj = Mage::helper('catalog/image')->init($product, $product_images_source_res);
                                                    }
                                                    catch(Exception $e){}
                                                    if (isset($image_obj)) {
                                                        $img_width = $product_images_maxdimensions[0];
                                                        $img_height = $product_images_maxdimensions[1];

                                                        $orig_img_width = $image_obj->getOriginalWidth();
                                                        $orig_img_height = $image_obj->getOriginalHeigh(); // getOriginalHeigh() = spell mistake
                                                        if ($orig_img_width != $orig_img_height) {
                                                            if ($orig_img_width > $orig_img_height) {
                                                                $img_height = ceil(($orig_img_height / $orig_img_width) * $product_images_maxdimensions[1]);
                                                            } elseif ($orig_img_height > $orig_img_width) {
                                                                $img_width = ceil(($orig_img_width / $orig_img_height) * $product_images_maxdimensions[0]);
                                                            }
                                                        }

                                                        $x1 = $imagesX;
                                                        $y1 = ($this->y - $img_height);
                                                        $x2 = ($imagesX + $img_width);
                                                        $y2 = ($this->y);

                                                        if (is_integer($img_width)) $resize_x = ($img_width * 4);
                                                        if (is_integer($img_height)) $resize_y = ($img_height * 4);

                                                        $image_placeholder_height = ($y2 - $y1);

                                                        // product_images_source = $thumbnail, small_image, image, gallery
                                                        if ($product_images_source == 'gallery') {
                                                            $gallery = $product->getMediaGalleryImages();
                                                            // can get posiiton here

                                                            $image_urls = array();
                                                            foreach ($gallery as $image) {
                                                                $imagePath_temp = Mage::helper('catalog/image')->init($product, 'image', $image->getFile())
                                                                    ->constrainOnly(TRUE)
                                                                    ->keepAspectRatio(TRUE)
                                                                    ->keepFrame(FALSE)
                                                                    ->resize($resize_x, $resize_y)
                                                                    ->__toString();

                                                                if (strpos($imagePath_temp, 'placeholder') === false) $imagePaths[] = $imagePath_temp;
                                                            }
                                                        } else {
                                                            try{
                                                                $imagePath_temp = Mage::helper('catalog/image')->init($product, $product_images_source_res)
                                                                    ->constrainOnly(TRUE)
                                                                    ->keepAspectRatio(TRUE)
                                                                    ->keepFrame(FALSE)
                                                                    ->resize($resize_x, $resize_y)
                                                                    ->__toString();
                                                            }
                                                            catch(Exception $e){
                                                            }
                                                            if (strpos($imagePath_temp, 'placeholder') === false) $imagePaths[] = $imagePath_temp;
                                                        }

                                                        $imagePath = '';
                                                        $image_x_addon = 0;
                                                        $image_x_addon_2 = 0;
                                                        $count = 1;

                                                        foreach ($imagePaths as $imagePath) {
                                                            $imagePath = trim($imagePath);
                                                            if ($imagePath != '') {
                                                                $image_x_addon += ($count * ($img_width + 10)); // shift the 2nd image over
                                                                $image_x_addon_2 += (($count - 1) * ($img_width + 10)); // shift the 2nd image over
                                                                $count++;
                                                                $media_path = Mage::getBaseDir('media');
                                                                $image_url = $imagePath;
                                                                $image_url_after_media_path_with_media = strstr($image_url, '/media/');
                                                                $image_url_after_media_path = strstr_after($image_url, '/media/');

                                                                $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                                                $final_image_path2 = $media_path . '/' . $image_url_after_media_path;
                                                                $image_ext = '';
                                                                $image_part = explode('.', $image_url_after_media_path);
                                                                $image_ext = array_pop($image_part);
                                                                if (($image_ext != 'jpg') && ($image_ext != 'jpeg') && ($image_ext != '.png')) continue;
                                                                //Check to print image in current page or in a new page.

                                                                if (($y1 - 5) < $min_product_y) {

                                                                    $flag_image_newpage = 1;
                                                                    $page = $this->nooPage($this->_packingsheet['page_size']);
                                                                    $page_count++;
                                                                    $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                                    if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                                    else $this->y = $page_top;
                                                                    $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                                                    $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                                    $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));
                                                                    $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                                    $this->y = ($this->y - ($this->_general['font_size_subtitles']));
                                                                    $items_y_start = $this->y;
                                                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                                                    $x1 = ($imagesX);
                                                                    $y1 = ($this->y - $img_height - 5);
                                                                    $x2 = ($imagesX + $img_width);
                                                                    $y2 = ($this->y - 5);
                                                                    $before_print_image_y = $this->y;

                                                                }
                                                                if ($product_images_border_color_temp != '#FFFFFF') {
                                                                    $page->setLineWidth(0.5);
                                                                    $page->setFillColor($product_images_border_color);
                                                                    $page->setLineColor($product_images_border_color);
                                                                    $page->drawRectangle(($x1 - 1 + $image_x_addon_2), ($y1 - 1 +7), ($x2 + 1 + $image_x_addon_2), ($y2 + 1 +7));
                                                                    $page->setFillColor($black_color);
                                                                }
                                                                try {
                                                                    // if(!$helper->checkTypeImageProduct($final_image_path, $image_ext)){
//                                                                         imagepng(imagecreatefromstring(file_get_contents($final_image_path)), $image_part[0] . '.png');
//                                                                         $final_image_path2 = $final_image_path = $image_part[0] . '.png';
//                                                                     }
                                                                        $image_source = $final_image_path2;
                                                                        $io = new Varien_Io_File();
                                                                        $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                                                                        $ext = substr($image_source, strrpos($image_source, '.') + 1);
                                                                        if($ext != 'jpg' && $ext != 'JPG')
                                                                        {
                                                                            $image_zebra->source_path = $final_image_path2;
                                                                            $image_zebra->target_path = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.jpeg';;
                            //                                              $image->jpeg_quality = 100;
                                                                            if(!(file_exists($image_zebra->target_path)))
                                                                            {
                                                                                $size_1 = $img_width*300/72;
                                                                                $size_2 = $img_height*300/72;
                                                                                if (!$image_zebra->resize($size_1, $size_2, ZEBRA_IMAGE_NOT_BOXED, -1))
                                                                                     show_error($image_zebra->error, $image_zebra->source_path, $image_zebra->target_path);

                                                                             }
                                                                             $final_image_path = $image_target;
                                                                        }
                                                                        else
                                                                        {
                                                                            $ext = 'jpeg';
                                                                            $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.'.$ext;

                                                                            if(!(file_exists($image_target)))
                                                                            {
                                                                                $size_1 = $img_width*300/72;
                                                                                $size_2 = $img_height*300/72;
                                                                                    $image_simple->load($image_source);
                                                                                    $image_simple->resize($size_1,$size_2);
                                                                                    $image_simple->save($image_target);
                                                                            }
                                                                        }
                                                                        $final_image_path = $image_target;
                                                                    $image = Zend_Pdf_Image::imageWithPath($final_image_path);
                                                                    $page->drawImage($image, $x1 + $image_x_addon_2, $y1 +7, $x2 + $image_x_addon_2, $y2 +7);
                                                                } catch (Exception $e) {
                                                                        echo $e->getMessage(); exit;
                                                                    if ($product_images_border_color_temp != '#FFFFFF') {
                                                                        $page->setLineWidth(0.5);
                                                                        $page->setFillColor($white_color);
                                                                        $page->setLineColor($white_color);
                                                                        $page->drawRectangle(($x1 - 2 + $image_x_addon_2), ($y1 - 3 + 7), ($x2 + 2 + $image_x_addon_2), ($y2 + 3 + 7));
                                                                        $page->setFillColor($black_color);
                                                                    }
                                                                    continue;
                                                                }
                                                                $has_shown_product_image = 1;
                                                                $after_print_image_y = $this->y - $img_height + 5 + 3 - 15; // $this->y;
                                                                if ($flag_image_newpage)
                                                                    $after_print_image_y_newpage = $this->y - $img_height + 5 + 3 - 15;;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $this->y -= 15;
                                if ($product_images_line_nudge != 0 && !isset($product_build_value['bundle_options_sku'])) {
                                    $this->y = ($this->y + $product_images_line_nudge);
                                }
                                unset($product_id);
                            }
                            //Goto before print image.
                            if ($flag_image_newpage && isset($page_prev)) {
                                $page = $page_prev;
                                $this->y = $befor_print_image_y_newpage;
                            } else $this->y = $before_print_image_y;
                            /***************************END PRINT ITEM IMAGE*********************/

                            /***************************PRINTING SHELVING**********************/
                            $this->y = $yPosTemp;
                            $yPosTempCombine = $this->y;
                            $custom_attribute_combined_array = array();
                            $page_shelving_1 = count($this->_getPdf()->pages);
                            $page_count_shelving_1 = 0;
                            $flag_print_shelving_1 = false;
                            $arr_page_y_shelving_1 = array();
                            $line_height = (1.15 * $this->_general['font_size_body']);
                            $shelving_y_pos = $this->y;
                            if (isset($product_build_value['shelving_real']) && ($product_build_value['shelving_real'] != '') && !$hide_bundle_parent_f) {
                                $print_star_shelving = 0;
                                $shelving_real = $product_build_value['shelving_real'];

                                $flag_print_shelving_1 = true;
                                $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_real_star_specific_value_yn', 0, false, $wonder, $store_id);
                                $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_real_star_specific_value_filter', '', false, $wonder, $store_id)));

                                if($shelving_real_star_specific_value_yn !== 0)
                                {
                                    if(is_array($shelving_real_star_specific_value_filter))
                                    {
                                        foreach($shelving_real_star_specific_value_filter as $text_filter)
                                        {
                                            if(!empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                                            {
                                                $print_star_shelving = 1;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if($shelving_real_star_specific_value_yn && ($print_star_shelving == 1))
                                {
                                    if($shelving_real_star_specific_value_yn == 1)
                                        $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_real_image', $order_storeId);
                                    elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                                    elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                                    elseif($shelving_real_star_specific_value_yn == '18_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-18.png';
                                    elseif($shelving_real_star_specific_value_yn == '21_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-21.png';

                                    if (isset($shelving_real_image_filename) && $shelving_real_image_filename) {
                                        $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                                        $dirImg = $shelving_real_image_path;
                                        $imageObj = new Varien_Image($dirImg);
                                        $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                                        $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                                        $image_ext = '';
                                        $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                                            $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                                            $page->drawImage($shelving_real_image, $shelfX, $shelving_y_pos - $shelving_image_height/4 , $shelfX+$shelving_image_width, $shelving_y_pos + $shelving_image_height*.75);
                                        }
                                        unset($shelving_real_star_specific_value_yn);
                                        unset($shelving_real_star_specific_value_filter);
                                        unset($shelving_real_image_filename);
                                    }
                                }
                                else
                                {


                                    if (is_array($shelving_real)) $shelving_real = implode(',', $shelving_real);
                                    $shelving_real = trim($shelving_real);
									if($custom_round_yn != '0')
									{
										$shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
									}
                                    $next_col_to_shelving_real = getPrevNext2($this->columns_xpos_array, 'shelfX', 'next', $padded_right - $page_pad_leftright);
                                    $max_shelving_real_length = ($next_col_to_shelving_real - $shelfX);
                                    $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                                    $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    $line_width_shelving_real = $this->parseString('1234567890', $font_temp_shelf2, ($this->_general['font_size_body']));
                                    $char_width_shelving_real = $line_width_shelving_real / 11;
                                    $max_chars_shelving_real = round($max_shelving_real_length / $char_width_shelving_real);
                                    $shelving_real = wordwrap($shelving_real, $max_chars_shelving_real, "\n");
                                    $shelving_real_trim = str_trim($shelving_real, 'WORDS', $max_chars_shelving_real - 3, '...');
                                    $token = strtok($shelving_real, "\n");
                                    $msg_line_count = 2;
                                    if ($token != false) {
                                        while ($token != false) {
                                            $shelving_real_array[] = strip_tags($token);
                                            $msg_line_count++;
                                            $token = strtok("\n");
                                        }
                                    } else
                                        $shelving_real_array[] = $shelving_real;
                                    if ($this->_getConfig('shelving_real_trim_content_yn', 0, false, $wonder, $order_storeId)) {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_real_title] = $shelving_real_trim;
                                        }
                                        else{
                                            $page->drawText($shelving_real_trim, $shelfX, $this->y, 'UTF-8');
                                            $this->y -= $line_height;
                                        }
                                    } else {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_real_title] = $shelving_real;
                                        }
                                        else{
                                            $count_shelving_row = count($shelving_real_array);
                                        foreach ($shelving_real_array as $shelving_real_line) {
                                            if ($this->y < ($min_product_y)) {
                                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                                $page_count++;
                                                $page_count_shelving_1++;
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                else $this->y = $page_top;
                                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));
                                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));
                                                $items_y_start = $this->y;
                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $arr_page_y_shelving_1[$page_count_shelving_1] = $this->y;
                                            }
                                            $page->drawText($shelving_real_line, $shelfX, $this->y, 'UTF-8');
                                            if($count_shelving_row >0)
                                                        $this->y -= $line_height;
                                        }
                                    }
                                }
                                unset($shelving_real_array);
                                unset($shelving_real);
                                }
                            }

                            //Goto before print shelving.
                            if ($flag_image_newpage) {
                                if ($product_images_yn == 1)
                                    if (($page_count_shelving_1 < 1) || (($page_count_shelving_1 == 1) && ($this->y > $after_print_image_y_newpage))) {
                                        $this->y = $after_print_image_y_newpage;
                                    }
                            } else {
                                if (($product_images_yn == 1) && ($this->y > $after_print_image_y) && ($page_count_shelving_1 < 1)) {
                                    $this->y = $after_print_image_y - $image_y_nudge; //- 15;
                                }
                            }
                            $max_y_1 = $this->y;
                            /***************************PRINTING SHELVING 2**********************/
                            $page_shelving_2 = $page_shelving_1;
                            $page_count_shelving_2 = 0;
                            $flag_print_shelving_2 = false;
                            $arr_page_y_shelving_2 = array();
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            if (isset($product_build_value['shelving']) && ($product_build_value['shelving'] != '') && !$hide_bundle_parent_f) {
                                $print_star_shelving = 0;
                                $shelving_real = $product_build_value['shelving'];
								if($custom_round_yn != '0')
								{
									$shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
								}
                                $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_2_star_specific_value_yn', 0, false, $wonder, $store_id);
                                $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_2_star_specific_value_filter', '', false, $wonder, $store_id)));

                                if($shelving_real_star_specific_value_yn !== 0)
                                {
                                    if(is_array($shelving_real_star_specific_value_filter))
                                    {
                                        foreach($shelving_real_star_specific_value_filter as $text_filter)
                                        {
                                            if( !empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                                            {
                                                $print_star_shelving = 1;
                                                break;
                                            }
                                        }
                                    }
                                }

                                if($print_star_shelving == 1)
                                {
                                    if($shelving_real_star_specific_value_yn == 1)
                                        $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_2_image', $order_storeId);
                                    elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                                    elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                                    elseif($shelving_real_star_specific_value_yn == '18_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-18.png';
                                    elseif($shelving_real_star_specific_value_yn == '21_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-21.png';

                                    if ($shelving_real_image_filename) {
                                        $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                                        $dirImg = $shelving_real_image_path;
                                        $imageObj = new Varien_Image($dirImg);
                                        $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                                        $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                                        $image_ext = '';
                                        $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                                            $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                                            $page->drawImage($shelving_real_image, $shelf2X, $shelving_y_pos - $shelving_image_height/4 , $shelf2X+$shelving_image_width, $shelving_y_pos + $shelving_image_height*.75);
                                        }
                                    }
                                        unset($shelving_real_star_specific_value_yn);
                                        unset($shelving_real_star_specific_value_filter);
                                        unset($shelving_real_image_filename);

                                }
                                else
                                {
                                if ($flag_print_shelving_1) {
                                    $this->y = $yPosTemp;
                                    $page = $this->_getPdf()->pages[$page_shelving_1 - 1];
                                    $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 4), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                }
                                $shelving = $product_build_value['shelving'];
                                if (is_array($shelving)) $shelving = implode(',', $shelving);
                                $shelving = trim($shelving);
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                $next_col_to_shelving = getPrevNext2($this->columns_xpos_array, 'shelf2X', 'next', $padded_right - $page_pad_leftright);
                                $max_shelving_length = ($next_col_to_shelving - $shelf2X);
                                $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_shelving = $this->parseString('1234567890', $font_temp_shelf2, ($this->_general['font_size_body']));
                                $char_width_shelving = $line_width_shelving / 8.5;
                                $max_chars_shelving = round($max_shelving_length / $char_width_shelving);
                                $shelving = wordwrap($shelving, $max_chars_shelving, "\n");
                                $shelving_trim = strip_tags(str_trim($shelving, 'WORDS', $max_chars_shelving - 3, '...'));
                                $token = strtok($shelving, "\n");
                                $msg_line_count = 2;
                                if ($token != false) {
                                    while ($token != false) {
                                        $shelving_array[] = strip_tags($token);
                                        $msg_line_count++;
                                        $token = strtok("\n");
                                    }
                                } else
                                    $shelving_array[] = $shelving;
                                if ($this->_getConfig('shelving_trim_content_yn', 0, false, $wonder, $order_storeId)) {
                                    if($combine_custom_attribute_yn == 1){
                                        $custom_attribute_combined_array[$shelving_title] = $shelving_trim;
                                    }else{
                                        $page->drawText($shelving_trim, $shelf2X, $this->y, 'UTF-8');
                                        $this->y -= $line_height;
                                    }
                                } else {
                                    if($combine_custom_attribute_yn == 1){
                                        $custom_attribute_combined_array[$shelving_title] = $shelving;
                                    }else
                                    foreach ($shelving_array as $shelving_line) {
                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        $page->drawText($shelving_line, $shelf2X, $this->y, 'UTF-8');
                                        //$this->y -= $line_height;
                                        if ($this->y < $min_product_y) {
                                            $page_count_shelving_2++;
                                            if (($flag_print_shelving_1) && ($page_count_shelving_2 <= $page_count_shelving_1)) {
                                                $page = $this->_getPdf()->pages[$page_shelving_1 - 1 + $page_count_shelving_2];
                                                $this->y = $arr_page_y_shelving_1[$page_count_shelving_2];
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                            } else {
                                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                                $page_count++;
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                else $this->y = $page_top;

                                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                                $items_y_start = $this->y;
                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
                                        }
                                    }
                                }
                                unset($shelving_array);
                                unset($shelving);
                                }

                            }
                            $max_y_2 = $this->y;
                            $page = $this->_getPdf()->pages[count($this->_getPdf()->pages) - 1];
                            if ($flag_image_newpage && ($page_count_shelving_1 < 1) && ($page_count_shelving_2 < 1)) {
                                $this->y = $after_print_image_y_newpage; //- 15;
                            }
                            else
                                if ($page_count_shelving_2 > $page_count_shelving_1) {
                                    $this->y = $max_y_2;
                                } else {
                                    $this->y = $max_y_1;
                                }

                            /***************************PRINTING SHELVING 3**********************/
                            if (isset($product_build_value['shelving2']) && ($product_build_value['shelving2'] != '') && !$hide_bundle_parent_f) {
                                $print_star_shelving = 0;
                                $shelving_real = $product_build_value['shelving3'];
								if($custom_round_yn != '0')
								{
									$shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
								}
                                $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_3_star_specific_value_yn', 0, false, $wonder, $store_id);
                                $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_3_star_specific_value_filter', '', false, $wonder, $store_id)));

                                if($shelving_real_star_specific_value_yn !== 0)
                                {
                                    if(is_array($shelving_real_star_specific_value_filter))
                                    {
                                        foreach($shelving_real_star_specific_value_filter as $text_filter)
                                        {
                                            if( !empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                                            {
                                                $print_star_shelving = 1;
                                                break;
                                            }
                                        }
                                    }
                                }

                                if($print_star_shelving == 1)
                                {
                                    if($shelving_real_star_specific_value_yn == 1)
                                        $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_3_image', $order_storeId);
                                    elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                                    elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                                    elseif($shelving_real_star_specific_value_yn == '18_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-18.png';
                                    elseif($shelving_real_star_specific_value_yn == '21_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-21.png';

                                    if ($shelving_real_image_filename) {
                                        $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                                        $dirImg = $shelving_real_image_path;
                                        $imageObj = new Varien_Image($dirImg);
                                        $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                                        $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                                        $image_ext = '';
                                        $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                                            $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                                            $page->drawImage($shelving_real_image, $shelf3X, $shelving_y_pos - $shelving_image_height/4 , $shelf3X+$shelving_image_width, $shelving_y_pos + $shelving_image_height*.75);
                                        }
                                    }
                                        unset($shelving_real_star_specific_value_yn);
                                        unset($shelving_real_star_specific_value_filter);
                                        unset($shelving_real_image_filename);
                                    }
                                    else
                                    {
                                    $this->y = $yPosTemp;

                                    $shelving_2 = $product_build_value['shelving2'];

                                    if (is_array($shelving_2)) $shelving_2 = implode(',', $shelving_2);
                                    $shelving_2 = trim($shelving_2);
                                    $next_col_to_shelving_2 = getPrevNext2($this->columns_xpos_array, 'shelf3X', 'next', $padded_right - $page_pad_leftright);
                                    $max_shelving_2_length = ($next_col_to_shelving_2 - $shelf2X);
                                    $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                                    $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    $line_width_shelving_2 = $this->parseString('1234567890', $font_temp_shelf2, ($this->_general['font_size_body']));
                                    $char_width_shelving_2 = $line_width_shelving_2 / 11;
                                    $max_chars_shelving_2 = round($max_shelving_2_length / $char_width_shelving_2);
                                    $shelving_2 = wordwrap($shelving_2, $max_chars_shelving_2, "\n");
                                    $shelving_2_trim = str_trim($shelving_2, 'WORDS', $max_chars_shelving_2 - 3, '...');
                                    $token = strtok($shelving_2, "\n");
                                    $msg_line_count = 2;
                                    if ($token != false) {
                                        while ($token != false) {
                                            $shelving_2_array[] = $token;
                                            $msg_line_count++;
                                            $token = strtok("\n");
                                        }
                                    } else
                                        $shelving_2_array[] = $shelving_2;
                                    if ($this->_getConfig('shelving_2_trim_content_yn', 0, false, $wonder, $order_storeId)) {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_2_title] = $shelving_2_trim;
                                        }else{
                                            $page->drawText($shelving_2_trim, $shelf3X, $this->y, 'UTF-8');
                                            $this->y -= $line_height;
                                        }
                                    } else {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_2_title] = $shelving_2;
                                        }else
                                        foreach ($shelving_2_array as $shelving_2_line) {
                                            $page->drawText($shelving_2_line, $shelf3X, $this->y, 'UTF-8');
                                            //$line_height=20;
                                            //$this->y -= $line_height;

                                            if ($this->y < $min_product_y) {
                                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                                $page_count++;
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                else $this->y = $page_top;

                                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                                $items_y_start = $this->y;
                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
                                        }
                                    }
                                    unset($shelving_2_array);
                                    unset($shelving_2);
                                }
                            }

                            /***************************PRINTING SHELVING 4**********************/
                            if (isset($product_build_value['shelving3']) && ($product_build_value['shelving3'] != '') && !$hide_bundle_parent_f) {
                                $print_star_shelving = 0;
                                $shelving_real = trim($product_build_value['shelving3']);
								if($custom_round_yn != '0')
								{
									$shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
								}
                                $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_4_star_specific_value_yn', 0, false, $wonder, $store_id);
                                $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_4_star_specific_value_filter', '', false, $wonder, $store_id)));
                                if($shelving_real_star_specific_value_yn !== 0)
                                {

                                    if(is_array($shelving_real_star_specific_value_filter))
                                    {
                                        foreach($shelving_real_star_specific_value_filter as $text_filter)
                                        {
                                            if( !empty($text_filter) && strpos(strtolower($shelving_real),trim(strtolower($text_filter))) !== FALSE)
                                            {
                                                $print_star_shelving = 1;
                                                break;
                                            }
                                        }
                                    }
                                }

                                if($print_star_shelving == 1)
                                {
                                    if($shelving_real_star_specific_value_yn == 1)
                                        $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_4_image', $order_storeId);
                                    elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                                    elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                                    elseif($shelving_real_star_specific_value_yn == '18_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-18.png';
                                    elseif($shelving_real_star_specific_value_yn == '21_flag')
                                        $shelving_real_image_filename = 'default/attribute-flag-21.png';

                                    if ($shelving_real_image_filename) {
                                        $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                                        $dirImg = $shelving_real_image_path;
                                        $imageObj = new Varien_Image($dirImg);
                                        $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                                        $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                                        $image_ext = '';
                                        $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                                            $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                                            $page->drawImage($shelving_real_image, $shelf4X, $shelving_y_pos, $shelf4X+$shelving_image_width, $shelving_y_pos + $shelving_image_height);
                                        }
                                    }
                                        unset($shelving_real_star_specific_value_yn);
                                        unset($shelving_real_star_specific_value_filter);
                                        unset($shelving_real_image_filename);
                                    }
                                else
                                {
                                    $this->y = $yPosTemp;
                                    $shelving_3 = $product_build_value['shelving3'];
                                    if (is_array($shelving_3)) $shelving_3 = implode(',', $shelving_3);
                                    $shelving_3 = trim($shelving_3);
                                    $next_col_to_shelving_3 = getPrevNext2($this->columns_xpos_array, 'shelf3X', 'next', $padded_right - $page_pad_leftright);
                                    $max_shelving_3_length = ($next_col_to_shelving_3 - $shelf2X);
                                    $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                                    $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    $line_width_shelving_3 = $this->parseString('1234567890', $font_temp_shelf2, ($this->_general['font_size_body']));
                                    $char_width_shelving_3 = $line_width_shelving_3 / 11;
                                    $max_chars_shelving_3 = round($max_shelving_3_length / $char_width_shelving_3);
                                    $shelving_3 = wordwrap($shelving_3, $max_chars_shelving_3, "\n");
                                    $shelving_3_trim = str_trim($shelving_3, 'WORDS', $max_chars_shelving_3 - 3, '...');
                                    $token = strtok($shelving_3, "\n");
                                    $msg_line_count = 2;
                                    if ($token != false) {
                                        while ($token != false) {
                                            $shelving_3_array[] = $token;
                                            $msg_line_count++;
                                            $token = strtok("\n");
                                        }
                                    } else
                                        $shelving_3_array[] = $shelving_3;
                                    if ($this->_getConfig('shelving_3_trim_content_yn', 0, false, $wonder, $order_storeId)) {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_3_title] = $shelving_3_trim;
                                        }else{
                                            $page->drawText($shelving_3_trim, $shelf4X, $this->y, 'UTF-8');
                                            $this->y -= $line_height;
                                        }
                                    } else {
                                        if($combine_custom_attribute_yn == 1){
                                            $custom_attribute_combined_array[$shelving_3_title] = $shelving_3;
                                        }else
                                        foreach ($shelving_3_array as $shelving_3_line) {
                                            $page->drawText($shelving_3_line, $shelf4X, $this->y, 'UTF-8');
                                            //$line_height=20;
                                            //$this->y -= $line_height;

                                            if ($this->y < $min_product_y) {
                                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                                $page_count++;
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                else $this->y = $page_top;

                                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                                $items_y_start = $this->y;
                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
                                        }
                                    }

                                    unset($shelving_3_array);
                                    unset($shelving_3);
                            }
                            }
                            /****************************PRINTING COMBINE CUSTOM ATTRIBUTE**********************/
                            if($combine_custom_attribute_yn == 1 && $custom_attribute_combined_array != ''){
                                foreach ($custom_attribute_combined_array as $key => $custom_attribute) {
                                    if($combine_custom_attribute_title_each == 1)
                                        $page->drawText($key . ': ' . $custom_attribute, $combine_custom_attribute_Xpos, $this->y, 'UTF-8');
                                    else
                                        $page->drawText($custom_attribute, $combine_custom_attribute_Xpos, $this->y, 'UTF-8');
                                    //$line_height=20;
                                    $this->y -= $line_height;

                                    if ($this->y < $min_product_y) {
                                        $page = $this->nooPage($this->_packingsheet['page_size']);
                                        $page_count++;
                                        $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                        if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                        else $this->y = $page_top;

                                        $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                        $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                        $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                        $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                        $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                        $items_y_start = $this->y;
                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    }
                                }
                                unset($custom_attribute_combined_array);
                            }
                            /***************************PRINTING INDIVIDUAL MESSAGE**********************/
                            if (($product_gift_message_yn == 'yesunderinvi') && !$hide_bundle_parent_f) {
                                $product_giftmessage_xpos = 40; //$this->_getConfig('individual_product_gift_message_xpos',20, false, $wonder, $store_id);
                                $gift_message_array['items'][$sku]['printed'] = 1;
                                //Product gift message set front size
                                 if ((Mage::helper('giftmessage/message')->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()) ||
                                     isset($answer) && (strlen($answer)>0))
                                {
                                    if ($product_build_value['has_message'] == 1) {
                                        if ($has_shown_product_image == 0)
                                            $this->y -= 5;
                                        $this->y -= $this->_general['font_size_body'];
                                        if ($message_title_tofrom_yn == 1) {
                                            $font_size_temp = $font_size_gift_message;
                                            $this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);
                                            $this->y = $this->showToFrom($message_title_tofrom_yn, $product_build_value['message-to'], $product_giftmessage_xpos, $this->y, $product_build_value['message-from'], $font_size_temp, $page);
                                        }
                                        $this->_setFont($page, $font_style_gift_message, ($font_size_gift_message - 1), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);
                                        $temp_height = 0;
                                        foreach ($product_build_value['message-content'] as $gift_msg_line) {
                                            $temp_height += 2 * $font_size_gift_message + 3;
                                        }

                                        if(is_array($product_build_value['message-content']))
                                        {
                                            foreach ($product_build_value['message-content'] as $gift_msg_line) {
                                                if (($this->y) < 40) {
                                                    $page = $this->nooPage($this->_packingsheet['page_size']);
                                                    $page_count++;
                                                    $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                    if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                    else $this->y = $page_top;

                                                    $paging_text = '-- ' . $order_number_display . ' | ' . Mage::helper('pickpack')->__('Page') . ' ' . $page_count . ' --';
                                                    $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                    $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                                    $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                    $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                                    $items_y_start = $this->y;
                                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                }
                                                $page->drawText(trim($gift_msg_line), $product_giftmessage_xpos, $this->y, 'UTF-8');
                                                $this->y -= ($font_size_gift_message + 3);
                                            }
                                        }
                                        else
                                        {
                                            $gift_msg_line = $product_build_value['message-content'];
                                            if (($this->y) < 40) {
                                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                                $page_count++;
                                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                                else $this->y = $page_top;

                                                $paging_text = '-- ' . $order_number_display . ' | ' . Mage::helper('pickpack')->__('Page') . ' ' . $page_count . ' --';
                                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                                $items_y_start = $this->y;
                                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
                                            $page->drawText(trim($gift_msg_line), $product_giftmessage_xpos, $this->y, 'UTF-8');
                                            $this->y -= ($font_size_gift_message + 3);
                                        }
                                        $this->y -= $font_size_gift_message;
                                    }
                                }
                            }

                            /***************************PRINTING EXTRA FEE**********************/
                            if (isset($magik_product_str[$itemId]) && ($magik_product_str[$itemId] != '') && !$hide_bundle_parent_f) {
                                $offset = 10;
                                $line_height = ($this->_general['font_size_body']);
                                $this->y -= $line_height;

                                $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] - 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                $page->drawText($magik_product_str[$itemId], ($productX + $productXInc + $offset), $this->y, 'UTF-8');
                                $this->y -= $line_height;
                            }
                            /***************************PRINTING BUNDLE OPTIONS**********************/

                $bundle_children_show = $this->_getConfig('bundle_children_yn', 7, false, $wonder, $store_id);
                $bundle_children_split = $this->_getConfig('split_bundles', 7, false, $wonder, $store_id);
                $flag_new_page_bundle = 0;
                if( $show_bundle_parent_yn == 1 && $bundle_children_split == 1 ) {
                    if (isset($product_build_value['bundle_options_sku'])) {
                      if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                       if ($sort_packing != 'none') {
                        if ($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
                            sksort($product_build_value['bundle_children'], $sort_packing, $sortorder_packing_bool);
                        }
                                                foreach ($product_build_value['bundle_children'] as $child) {  $childArray[] = $child ;}
                      }
                   }    // if (isset($product_build_value['bundle_options_sku']))
                }else {
                            if (isset($product_build_value['bundle_options_sku'])) {

                               if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                                    if ($sort_packing != 'none') {
                                            $sortorder_packing_bool = false;
                                            if ($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
                                            //$sort_packing_secondary = 'none';
                                            if($sort_packing_secondary == 'none' || $sort_packing_secondary == ''){
                                                sksort($product_build_value['bundle_children'], $sort_packing, $sortorder_packing_bool);
                                            }
                                            else{
                                                $sortorder_packing_secondary_bool = false;
                                                if ($sortorder_packing_secondary == 'ascending') $sortorder_packing_secondary_bool = true;
                                                $this->sortMultiDimensional($product_build_value['bundle_children'], $sort_packing, $sort_packing_secondary, $sortorder_packing_bool, $sortorder_packing_secondary_bool);
                                            }
                                        }
                                }

                                $offset = 10;
                                $line_height = ($this->_general['font_size_body']);
                                if(isset($after_print_option_y) && $after_print_option_y < $this->y)
                                    $this->y = $after_print_option_y;
                                else
                                    $this->y -= $line_height;
                                $options_y_counter += $line_height;

                                $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                $bundle_x = ($productX + $productXInc + 3);
                                if($after_print_name_y < $this->y)
                                    $this->y = $after_print_name_y;
                                if (($skuX < 800) && ($product_sku_yn == 1)) {
                                    $display_bundle_sku = $product_build_value['bundle_options_sku'];
                                    $display_bundle_sku = str_trim($display_bundle_sku, 'WORDS', $padded_right - 3, '...');

                                    $page->drawText($display_bundle_sku, ($productX + $productXInc + 3), $this->y, 'UTF-8');
                                    $this->y -= $line_height;
                                    $this->y += 7;
                                    $options_y_counter += $line_height;
                                } else $offset = 0;

                                if ($qtyX > $bundle_x) {
                                    $bundle_line_x2 = ($qtyX + 15);
                                    $bundle_options_x = ($tickboxX + 3);
                                    $tickboxX_bundle = ($tickboxX + 4);
                                } else {
                                    $bundle_options_x = ($qtyX + $shift_bundle_children_xpos + 7);
                                    $bundle_line_x2 = (($tickboxX + 3) + (strlen('Bundle Options : ') * ($this->_general['font_size_body'] - 2)) + $shift_bundle_children_xpos + 20);
                                    // tickbox   image    [bundle x] qty    name     code
                                    if ($skuX > $productX) {
                                        $bundle_line_x2 = ($skuX - 10);
                                        $bundle_options_x = ($qtyX - 5); //($tickboxX+3);
                                    }
                                    $tickboxX_bundle = $bundle_options_x - 10;
                                }
                                $bundle_before = 0;
                                if ($this->y >= ($min_product_y + 2 * ($this->_general['font_size_body']))) {
                                    $bundle_before = 1;
                                    $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], '#333333');
                                    $page->drawText($helper->__('Bundle Options') . ' : ', $bundle_options_x, $this->y, 'UTF-8');
                                    $page->setLineWidth(0.5);
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($greyout_color);
                                    $page->drawLine(($bundle_options_x), ($this->y - 2), $bundle_line_x2, ($this->y - 2));
                                }
                                $page->setFillColor($black_color);
                                $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                                    if ($sort_packing != 'none') {
                                        if ($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
                                        sksort($product_build_value['bundle_children'], $sort_packing, $sortorder_packing_bool);
                                    }
                                    //TODO Moo
                                    foreach ($product_build_value['bundle_children'] as $child) {

                                        $temp_count++;
                                        //Check need to create new page or not
                                        if ( ($this->y < $page_bottom)  || $this->y < ($min_product_y + ($this->_general['font_size_body']))) {
                                            $page = $this->nooPage($this->_packingsheet['page_size']);
                                            $page_count++;
                                            $flag_new_page_bundle++;
                                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);

                                            if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                            else $this->y = $page_top;

                                            $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                            $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                            $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                            $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');

                                            $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                            if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                                $page->setFillColor($background_color_subtitles_zend);
                                                $page->setLineColor($background_color_subtitles_zend);
                                                $page->setLineWidth(0.5);
                                                if ($fill_product_header_yn == 0) {
                                                    $page->drawLine($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2), ($padded_right), ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2));
                                                    $page->drawLine($padded_left, ($this->y + $this->_general['font_size_subtitles'] + 2 + 2), ($padded_right), ($this->y + $this->_general['font_size_subtitles'] + 2 + 2));
                                                } else {
                                                    $page->drawRectangle($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ($this->y + $this->_general['font_size_subtitles'] + 2));
                                                }
                                            }

                                            $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                            if ($product_images_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($images_title), $imagesX, $this->y, 'UTF-8');
                                            }
                                            if ($serial_code_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($serial_code_title), ($serial_codeX + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }
                                            $page->drawText(Mage::helper('sales')->__($qty_title), $qtyX + 8, $this->y, 'UTF-8');
                                            if ($show_name_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($items_title), ($productX + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }

                                            if($show_gift_wrap_yn == 1){
                                                $page->drawText(Mage::helper('sales')->__($gift_wrap_title), ($gift_wrap_xpos + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }

                                            if ($product_sku_yn == 1) $page->drawText(Mage::helper('sales')->__($sku_title), ($skuX + $first_item_title_shift_sku), $this->y, 'UTF-8');

                                            if ($product_sku_barcode_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_title), ($sku_barcodeX - 1), $this->y, 'UTF-8');

                                            if ($product_sku_barcode_2_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_2_title), ($sku_barcodeX_2 - 1), $this->y, 'UTF-8');

                                            if ($product_stock_qty_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($product_stock_qty_title), ($stockqtyX), $this->y, 'UTF-8');
                                            }

                                            if ($product_options_yn == 'yescol') {
                                                $page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y, 'UTF-8');
                                            }

                                            if ($combine_custom_attribute_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y, 'UTF-8');
                                            }

                                            if ($prices_yn != '0') {
                                                $page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
                                                $page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
                                            }
                                            if($show_allowance_yn == 1){
                                                $page->drawText(Mage::helper('sales')->__($show_allowance_title), $show_allowance_xpos, $this->y, 'UTF-8');
                                            }
                                            if ($tax_col_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($tax_title), $taxEachX, $this->y, 'UTF-8');
                                            }

                                            $this->y = ($this->y - 28);
                                            $items_y_start = $this->y;

                                            $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], '#333333');
                                            if ($bundle_before == 1) {
                                                $page->drawText($helper->__('Bundle Options Cont\'d...') . ' : ', $bundle_options_x, $this->y, 'UTF-8');
                                            } else
                                                $page->drawText($helper->__('Bundle Options') . ' : ', $bundle_options_x, $this->y, 'UTF-8');
                                            $page->setLineWidth(0.5);
                                            $page->setFillColor($white_color);
                                            $page->setLineColor($greyout_color);
                                            $page->drawLine(($bundle_options_x), ($this->y - 2), $bundle_line_x2, ($this->y - 2));
                                            $page->setFillColor($black_color);
                                            $this->_setFont($page, $this->_general['font_style_body'], ($this->_general['font_size_body'] - 2), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        $product = $_newProduct = $helper->getProductForStore($child->getProductId(), $storeId);
                                        $sku = $child->getSku();
                                        $price = $child->getPriceInclTax();
                                        if(!$price){
                                            $infoBuyRequest = unserialize($child->getData('product_options'));
                                            $infoBuyRequest = unserialize($infoBuyRequest['bundle_selection_attributes']);
                                            $price = $infoBuyRequest['price'];
                                        }
                                        $qty = (int)$child->getQtyOrdered();
                                        if ($store_view == "storeview") {
                                            $name = $child->getName();
                                        } elseif($store_view == "specificstore" && $specific_store_id != ""){
                                            $_product = $helper->getProductForStore($child->getProductId(), $specific_store_id);
                                            if ($_product->getData('name')) $name = trim($_product->getData('name'));
                                            if ($name == '') $name = trim($child->getName());
                                        }
                                        else{
                                            $name = $this->getNameDefaultStore($child);

                                        }
                                        $this->y -= $line_height*1.3;
                                        $options_y_counter += $line_height;
                                        if ($from_shipment == 'shipment') {
                                            $productXInc = 25;
                                            switch ($show_qty_options) {
                                                case 1:
                                                    $price_qty = $qty;
                                                    $productXInc = 0;
                                                    break;
                                                case 2:
                                                    $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                                    $productXInc = 25;
                                                    break;
                                                case 3:
                                                    $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                                    $productXInc = 25;
                                                    break;
                                            }
                                        } else {
                                            switch ($show_qty_options) {
                                                case 1:
                                                    $price_qty = $qty;
                                                    $productXInc = 0;
                                                    break;
                                                case 2:
                                                    $price_qty = (int)$item->getQtyShipped();
                                                    $productXInc = 25;
                                                    break;
                                                case 3:
                                                    $price_qty = (int)$item->getQtyShipped();
                                                    $productXInc = 25;
                                                    break;
                                            }
                                        }
                                        /***get qty string**/
                                        $qty_string = $this->getQtyStringBundle($from_shipment, $product_build_value, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids, $store_id);
                                        $draw_qty_value = $qty_string;
                                        $price_qty = $qty_string;
                                        $addon_shift_x = $shift_bundle_children_xpos;
                                        //TODO Moo
                                        if (($tickbox_yn == 1) || ($tickbox_2_yn == 1)) {
                                            $page->setLineWidth(0.5);
                                            $page->setFillColor($white_color);
                                            $page->setLineColor($black_color);
                                            if ($tickbox_yn == 1) {
                                                $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                                if ($this->_getConfig('tickbox_signature_line', 0, false, $wonder, $order_storeId)){
                                                   $page->drawRectangle($tickboxX +$addon_shift_x, ($this->y - $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX +$addon_shift_x+ $tickbox_width_1*2/3), ($this->y + $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3));
                                                   $page->drawLine(($tickboxX - ($tickbox_width_1 - 2)), ($this->y), ($tickboxX - ($tickbox_width_1 * ($this->_general['font_size_body'] / 2))), ($this->y));
                                                }else{
                                                   $page->drawRectangle($tickboxX_bundle +$addon_shift_x, ($this->y - $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX_bundle +$addon_shift_x+ $tickbox_width_1*2/3), ($this->y + $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3));
                                                }
                                            }
                                            if ($tickbox_2_yn == 1) {
                                                $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                                //$page->drawRectangle($tickboxX_bundle +$addon_shift_x, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX_bundle+$addon_shift_x + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 3));
                                                if ($this->_getConfig('tickbox_2_signature_line', 0, false, $wonder, $order_storeId)){
                                                   $page->drawRectangle($tickbox2X +$addon_shift_x, ($this->y - $tickbox_width_2 / 3 + $this->_general['font_size_body'] / 2 - 3), ($tickbox2X+$addon_shift_x + $tickbox_width_2*2/3), ($this->y + $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3));
                                                   $page->drawLine(($tickbox2X - ($tickbox_width_2 - 2)), ($this->y), ($tickbox2X - ($tickbox_width_2 * ($this->_general['font_size_body'] / 2))), ($this->y));
                                                }else{
                                                    $page->drawRectangle($tickboxX_bundle +$addon_shift_x, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX_bundle+$addon_shift_x + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 3));
                                                }
                                            }
                                            $page->setFillColor($black_color);
                                        }
                                        if ($numbered_product_list_bundle_children_yn == 1) {
                                            $page->drawText($temp_count . $numbered_list_suffix, $numbered_product_list_bundle_children_X +$addon_shift_x, ($this->y), 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE SKU**********************/
                                        if ($product_sku_yn == 1){
                                            if($this->_general['font_family_body'] == 'traditional_chinese' || $this->_general['font_family_body'] == 'simplified_chinese'){
                                                $font_family_body_temp = $this->_general['font_family_body'];
                                                $this->_general['font_family_body'] = 'helvetica';
                                            }
                                            $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($sku, $skuX + $addon_shift_x, $this->y, 'UTF-8');
                                        }

                                        /***************************PRINTING BUNDLE BARCODE**********************/
                                        if (($product_sku_barcode_yn != 0) && !$hide_bundle_parent_f) {
                                            $after_print_barcode_y = $this->y;
                                            $sku_barcodeY = $this->y - 4;
                                            $barcode = $sku;

                                            if ($product_sku_barcode_yn == 2)
                                                $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,1,true,$child->getProductId());
                                            $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        if (($product_sku_barcode_2_yn != 0) && !$hide_bundle_parent_f) {
                                            $after_print_barcode_y = $this->y;
                                            $sku_barcodeY = $this->y - 4;
                                            $barcode = $sku;
                                            if ($product_sku_barcode_2_yn == 2)
                                                $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,2,true,$child->getProductId());
                                            $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX_2,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        if($this->_general['font_family_body'] == 'traditional_chinese' || $this->_general['font_family_body'] == 'simplified_chinese'){
                                            $font_family_body_temp = $this->_general['font_family_body'];
                                            $this->_general['font_family_body'] = 'helvetica';
                                        }
                                        $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                        /***************************PRINTING BUNDLE QTY**********************/
                                        if ($product_qty_upsize_yn == 1 && $qty_string > 1) {
                                            if ($product_qty_red == 1) $this->_setFont($page, 'bold', $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'darkRed');
                                            if ($product_qty_rectangle == 1) {
                                                $page->setLineWidth(1);
                                                $page->setLineColor($black_color);
                                                $page->setFillColor($black_color);
                                                if ($product_qty_red == 1) $page->setLineColor($red_color);
                                                {

                                                    if (($qty_string >= 100) || (strlen($draw_qty_value) >= 3)) {
                                                        $page->drawRectangle(($qtyX +$addon_shift_x+ 7), ($this->y - 3), ($qtyX +$addon_shift_x+ 9 + (strlen($draw_qty_value) * 2*$font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                    } else
                                                        if (($qty_string >= 10) || (strlen($draw_qty_value) > 2)) {
                                                            $page->drawRectangle(($qtyX +$addon_shift_x + 7), ($this->y - 3), ($qtyX +$addon_shift_x + 9 +(strlen($draw_qty_value) *2* $font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                        } else {
                                                            $page->drawRectangle(($qtyX +$addon_shift_x + 7), ($this->y - 3), ($qtyX+$addon_shift_x + 9 + (strlen($draw_qty_value) *2* $font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                        }
                                                }
                                                $this->_setFont($page,'bold', $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'white');
                                                $page->drawText($draw_qty_value, ($qtyX +$addon_shift_x+ 8), ($this->y - 1), 'UTF-8');
                                            } else {
                                                if ($product_qty_underlined == 1) {
                                                    $page->setLineWidth(1);
                                                    $page->setLineColor($black_color);
                                                    $page->setFillColor($white_color);
                                                    if ($product_qty_red == 1) $page->setLineColor($red_color);
                                                    $page->drawLine(($qtyX +$addon_shift_x+ 7), ($this->y - 3), ($qtyX +$addon_shift_x+ 6 + (strlen($qty_string) * $this->_general['font_size_body'])), ($this->y - 3));
                                                }
                                                $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($qty_string, ($qtyX +$addon_shift_x+ 8), ($this->y - 1), 'UTF-8');
                                            }
                                            $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        } else
                                            $page->drawText($qty_string, ($qtyX +$addon_shift_x+ 8), $this->y, 'UTF-8');

                                        if(isset($font_family_body_temp)){
                                            $this->_general['font_family_body'] = $font_family_body_temp;
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }
                                        /***************************PRINTING BUNDLE PRICE**********************/
                                        if ($prices_yn != '0') {
                                            $bundle_options_part_price_total = ($price_qty * $price);
                                            $bundle_price_display = $this->formatPriceTxt($order, $price);
                                            $bundle_price_total_display = $this->formatPriceTxt($order, $bundle_options_part_price_total);

                                            if ($price > 0) $page->drawText('(' . $bundle_price_display . ')', $priceEachX, $this->y, 'UTF-8');
                                            if ($bundle_options_part_price_total > 0) $page->drawText('(' . $bundle_price_total_display . ')', $priceX +$addon_shift_x, $this->y, 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE NAME**********************/
                                        $after_print_name_bundle_y = $this->y;
                                        $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $max_name_length = $next_col_to_product_x - $productX;
                                        $line_width_name = $this->parseString($name, $font_temp_shelf2, ($font_size_options));
                                        $char_width_name = $line_width_name / strlen($name);
                                        $max_chars_name = round($max_name_length / $char_width_name);
                                        $multiline_name = wordwrap($name, $max_chars_name, "\n");
                                        $name_trim = str_trim($name, 'WORDS', $max_chars_name - 3, '...');
                                        $token = strtok($multiline_name, "\n");
                                        $character_breakpoint_name = stringBreak($name, $max_name_length, $font_size_options, $font_helvetica);
                                        $display_name = '';
                                        $name_length = 0;
                                        if (strlen($name) > ($character_breakpoint_name + 2)) {
                                            $display_name = $name_trim;
                                        } else $display_name = htmlspecialchars_decode($name);

                                        $token = strtok($multiline_name, "\n");
                                        $multiline_name_array = array();
                                        $temp_y = $this->y;
                                        if ($show_name_yn == 1 && $after_print_name_y) {
                                          if ($this->_getConfig('product_name_bold_yn', 0, false, $wonder, $order_storeId) || (Mage::getStoreConfig('pickpack_options/general/product_name_style') == 1)){
                                                   $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                           }
                                            if ($this->_getConfig('trim_product_name_yn', 0, false, $wonder, $order_storeId)) {
                                                $page->drawText($display_name, ($productX +$addon_shift_x+ $productXInc + 2), $temp_y, 'UTF-8');
                                            } else {
                                                if ($token != false) {
                                                    while ($token != false) {
                                                        $multiline_name_array[] = $token;
                                                        $token = strtok("\n");
                                                    }

                                                    foreach ($multiline_name_array as $name_in_line) {
                                                        $page->drawText($name_in_line, ($productX+$addon_shift_x + $productXInc + 2), $temp_y, 'UTF-8');
                                                        $temp_y -= $line_height;
                                                    }
                                                    $temp_y += $line_height;
                                                }
                                            }
                                           $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        $this->y = $temp_y;
                                        $after_print_name_bundle_y = $this->y; // - $line_height;
                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        unset($multiline_name_array);

                                        /***************************PRINTING BUNDLE TICKBOX**********************/
                                        // if ($tickbox_yn) {
                                            // $box_x = ($productX + $productXInc + $offset);
                                            // $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                            // $page->setLineWidth(0.5);
                                            // $page->setFillColor($white_color);
                                            // $page->setLineColor($black_color);
                                            // //$page->drawRectangle(($tickboxX_bundle), ($this->y), ($tickboxX_bundle + 6), ($this->y + 6));
                                            // $page->drawRectangle(($tickboxX_bundle), ($this->y - $tickbox_width_1 + $this->_general['font_size_body'] - 3), ($tickboxX_bundle + $tickbox_width_1), ($this->y + $this->_general['font_size_body'] - 3));
                                            // $page->setFillColor($black_color);
                                            // $after_print_ticbox1 = $this->y - $tickbox_width_1 + $this->_general['font_size_body'];
                                        // }

                                        //draw backordered children bundle
                                        if ($product_qty_backordered_yn == 1) {
                                            $backordered_children_bundle = (int)($child->getData("qty_backordered"));
                                            $page->drawText($backordered_children_bundle, ($prices_qtybackorderedX), $this->y, 'UTF-8');
                                        }
                                        if ($product_warehouse_yn == 1) {
                                            $item_warehouse = $child->getWarehouseTitle();
                                            $page->drawText($item_warehouse, ($prices_warehouseX), $this->y, 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE SHELVING**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                                $shelving_real_attribute = $this->_getConfig('shelving_real', 'shelf', false, $wonder, $order_storeId);
                                $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelfX',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);
                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }
                                        /***************************PRINTING BUNDLE SHELVING 2**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                $shelving_real_attribute = $this->_getConfig('shelving', 'shelf', false, $wonder, $order_storeId);
                $shelving_real_yn = $this->_getConfig('shelving_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf2X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);
                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }
                                        /***************************PRINTING BUNDLE SHELVING 3**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                $shelving_real_attribute = $this->_getConfig('shelving_2', 'shelf', false, $wonder, $order_storeId);
                $shelving_real_yn = $this->_getConfig('shelving_2_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf3X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);
                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }

                                        /***************************PRINTING BUNDLE SHELVING 4**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                $shelving_real_attribute = $this->_getConfig('shelving_3', 'shelf', false, $wonder, $order_storeId);
                $shelving_real_yn = $this->_getConfig('shelving_3_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf4X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);
                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }

                /* end of printing bundle shelving */
                                        if ($doubleline_yn == 2) $this->y -= 7.5;
                                        else
                                            if ($doubleline_yn == 1.5) $this->y -= 3.5;
                                            else
                                            $this->y += 3.5;
                                    }
                                    $after_print_name_y = $this->y - $this->_general['font_size_body'];
                                    $this->y -= $line_height*1.2;
                                }
                            }

                        }

                            /************************SET NEXT LINE Y POS TO PRINT THE NEXT ITEM**************/
                            if (isset($next_product_line_ypos) && ($next_product_line_ypos > 0))
                                $this->y = ($next_product_line_ypos);
                            /***************************PRINTING LINE UNDER EACH PRODUCT**********************/
                            $this->y += ($this->_general['font_size_body']);

                            if ($has_shown_product_image == 1) {
                                $this->y -= 15;
                            }
                            if(isset($after_print_name_bundle_y) && $after_print_name_bundle_y < $this->y){
                                    $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] - 2;
                            }
                             if(($flag_new_page_bundle == 0) && ($flag_image_newpage == 0))
                            {
                                 if (isset($after_print_name_y) && ($after_print_name_y < $this->y))
                                    $this->y = $after_print_name_y;
                                if (isset($after_print_option_y) && ($after_print_option_y < $this->y))
                                    $this->y = $after_print_option_y;
                                if (isset($after_print_sku_y) && ($after_print_sku_y < $this->y))
                                    $this->y = $after_print_sku_y;
                                if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                    $this->y = $after_print_barcode_y;

                                if (isset($yPosTempCombine) && ($yPosTempCombine < $this->y))
                                    $this->y = $yPosTempCombine;
                            }
                            if ($background_color_product_temp != '#FFFFFF') {
                                if ($has_shown_product_image == 1) {
                                    if ($product_images_line_nudge != 0) $this->y = $this->y + ($product_images_line_nudge * -1);
                                } else {
                                }

                                if ($temp_count != count($product_build)) {
                                    $this->y += $this->_general['font_size_body'] - 5;
                                    $page->setLineWidth(0.5);
                                    //$this->y -= 5;
                                    $page->setFillColor($background_color_product);
                                    $page->setLineColor($background_color_product);
                                    $page->drawLine($padded_left, ($this->y + 1), $padded_right, ($this->y + 1));
                                    $page->setFillColor($black_color);
                                    $this->y -= (($this->_general['font_size_body']));
                                }

                            }

                            if ($background_color_vert_product_temp != '#FFFFFF') {
                                if ($has_shown_product_image == 1) {
                                    if ($product_images_line_nudge != 0) $vert = ($product_images_line_nudge * -1);
                                    $this->y -= ($this->_general['font_size_body'] + 5);
                                } else {
                                    $vert = ($this->_general['font_size_body'] * 1.5);
                                }
                                $top_y = $this->y;
                                if ($product_count == 1) {
                                    $top_y += ($this->_general['font_size_body'] * 1.5);
                                    $vert = ($vert * 2);
                                }
                                $page->setLineWidth(0.5);
                                $page->setFillColor($background_color_product);
                                $page->setLineColor($background_color_product);
                                $page->drawLine($padded_left, ($top_y), $padded_left, ($top_y - $vert));
                                $page->drawLine($padded_right, ($top_y), $padded_right, ($top_y - $vert));

                                $vert_x_nudge = 5;

                                if ($product_images_yn == 1) {
                                    $page->drawLine(($imagesX - $vert_x_nudge), ($top_y), ($imagesX - $vert_x_nudge), ($top_y - $vert));
                                }
                                $page->drawLine(($qtyX - $vert_x_nudge), ($top_y), ($qtyX - $vert_x_nudge), ($top_y - $vert));

                                $page->drawLine((($productX + $productXInc + $first_item_title_shift_items) - $vert_x_nudge), ($top_y), (($productX + $productXInc + $first_item_title_shift_items) - $vert_x_nudge), ($top_y - $vert));

                                if ($product_sku_yn == 1) $page->drawLine(($skuX - $vert_x_nudge), ($top_y), ($skuX - $vert_x_nudge), ($top_y - $vert));

                                if ($product_options_yn == 'yescol') {
                                    $page->drawLine(($optionsX - $vert_x_nudge), ($top_y), ($optionsX - $vert_x_nudge), ($top_y - $vert));
                                }

                                if ($shelving_real_yn == 1) {
                                    $page->drawLine(($shelfX - $vert_x_nudge), ($top_y), ($shelfX - $vert_x_nudge), ($top_y - $vert));
                                }

                                if ($shelving_yn == 1) {
                                    $page->drawLine(($shelf2X - $vert_x_nudge), ($top_y), ($shelf2X - $vert_x_nudge), ($top_y - $vert));
                                }

                                if ($shelving_2_yn == 1) {
                                    $page->drawLine(($shelf3X - $vert_x_nudge), ($top_y), ($shelf3X - $vert_x_nudge), ($top_y - $vert));
                                }

                                if ($shelving_3_yn == 1) {
                                    $page->drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y, 'UTF-8');
                                }

                                if ($prices_yn != '0') {
                                    $page->drawLine(($priceEachX - $vert_x_nudge), ($top_y), ($priceEachX - $vert_x_nudge), ($top_y - $vert));
                                    $page->drawLine(($priceX - $vert_x_nudge), ($top_y), ($priceX - $vert_x_nudge), ($top_y - $vert));
                                }
                                if($show_allowance_yn == 1){
                                    $page->drawText(Mage::helper('sales')->__($show_allowance_title), $show_allowance_xpos, $this->y, 'UTF-8');
                                }
                                if ($tax_col_yn == 1) {
                                    $page->drawLine(($taxEachX - $vert_x_nudge), ($top_y), ($taxEachX - $vert_x_nudge), ($top_y - $vert));
                                }
                                $page->setFillColor($black_color);
                            }

                            /***************************DOUBLE LINE SPACING**********************/
                            if (isset($after_print_ticbox1) && $after_print_ticbox1 < $this->y && $flag_image_newpage < 1) {
                                $this->y = $after_print_ticbox1 - $this->_general['font_size_body'] - 10;
                            }

                            if (isset($after_print_ticbox2) && ($after_print_ticbox2 < $this->y) && ($flag_image_newpage < 1)) {
                                $this->y = $after_print_ticbox2 - $this->_general['font_size_body'] - 10;
                            }


                            if ($doubleline_yn == 2) $this->y -= 15;
                            else
                                if ($doubleline_yn == 1.5) $this->y -= 7.5;
                                else
                                    if ($doubleline_yn == 3) $this->y -= 20 ;
                                    else $this->y -= 3.5;
                            $product_qty_upsize_yn_morespace = $this->_getConfig('product_qty_upsize_yn', 0, false, $wonder, $store_id);
                            if ($product_qty_upsize_yn_morespace == 'u' || $product_qty_upsize_yn_morespace == 'c') {
                                $this->y -= 0.5 * $this->_general['font_size_body'];
                            }
                        }
                        unset($after_print_option_y);
                        unset($after_print_ticbox1);
                        unset($after_print_ticbox2);
                        unset($after_print_name_bundle_y);
                        unset($after_print_barcode_y);
                        unset($after_print_name_y);
                        unset($after_print_sku_y);
                        unset($next_product_line_ypos);
                        $hide_bundle_parent_f = false;
                    }
                    }
                    /* split bundle options */
                    if( count($childArray) > 0 ) :
                             $tickboxX_bundle = 10;
                             $bundle_array = array();
                             $bundle_quantity = array();
                             $array_remove_keys = array();
                             $qty;
                            foreach( $childArray as $key => $child ) {
                                if( in_array($child->getProductId(),$bundle_array))
                                   {
                                       $qty = $child->getQtyOrdered() + $bundle_quantity[$child->getProductId()]['qty'];
                                       $child->setQtyOrdered($qty);
                                       $array_remove_keys[] = $bundle_quantity[$child->getProductId()]['key'];
                                   }
                                   $bundle_array[] = $child->getProductId();
                                   $bundle_quantity[$child->getProductId()]= array( 'qty' => $child->getQtyOrdered(), 'key' => $key );
                                }
                 foreach($array_remove_keys as $val) unset($childArray[$val]);

                 foreach( $childArray as $child ){
                                       if ( ($this->y < $page_bottom) || ( $this->y < ($min_product_y + ($this->_general['font_size_body'])) ) ) {
                                            $page = $this->nooPage($this->_packingsheet['page_size']);
                                            $page_count++;
                                            $flag_new_page_bundle++;
                                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);

                                            if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                            else $this->y = $page_top;

                                            $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                            $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                            $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                            $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');

                                            $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                            if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                                $line_widths = explode(",", $this->_getConfig('bottom_line_width', '1,2', false, 'general', $store_id));
                                                $page->setFillColor($background_color_subtitles_zend);
                                                $page->setLineColor($background_color_subtitles_zend);
                                                $page->setLineWidth(0.5);

                                                if ($fill_product_header_yn == 0) {
                                                    switch( $fill_bars_options ){
                                                        case 0 :
                                                                $page->drawLine($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2), ($padded_right), ($this->y - ($this->_general['font_size_subtitles'] / 2) - 2));
                                                                $page->drawLine($padded_left, ($this->y + $this->_general['font_size_subtitles'] + 2 + 2), ($padded_right), ($this->y + $this->_general['font_size_subtitles'] + 2 + 2));
                                                                break;
                                                        case 1 :
                                                                if ($invoice_title_linebreak <= 1) {
                                                                    $bottom_fillbar = ceil($this->y - ($this->_general['font_size_subtitles'] / 2) - 2) + $fillbar_padding[1];
                                                                    $top_fillbar = ceil($this->y + $this->_general['font_size_subtitles'] + 2 + 2) + $fillbar_padding[0] ;
                                                                    if(isset($line_widths[0]) && $line_widths[0] > 0){
                                                                        $page->setLineWidth($line_widths[0]);
                                                                        $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                                                      }
                                                                    if(isset($line_widths[1]) && $line_widths[1] > 0){
                                                                            $page->setLineWidth($line_widths[1]);
                                                                            $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                                                    }
                                                                }
                                                                break;
                                                        case 2 :
                                                                break;
                                                    }
                                                } else {
                                                    switch( $fill_bars_options ){
                                                        case 0 :
                                                                $page->drawRectangle($padded_left, ($this->y - ($this->_general['font_size_subtitles'] / 2)), $padded_right, ($this->y + $this->_general['font_size_subtitles'] + 2));
                                                                break;
                                                        case 1 :
                                                                if ($invoice_title_linebreak <= 1) {
                                                                    $bottom_fillbar = ceil($this->y - ($this->_general['font_size_subtitles'] / 2))  + $fillbar_padding[1];
                                                                    $top_fillbar = ceil($this->y + $this->_general['font_size_subtitles'] + 2) + $fillbar_padding[0] ;
                                                                    if(isset($line_widths[0]) && $line_widths[0] > 0){
                                                                        $page->setLineWidth((int)$line_widths[0]);
                                                                        $page->drawLine($padded_left, $top_fillbar, ($padded_right), $top_fillbar);
                                                                      }
                                                                    if(isset($line_widths[1]) && $line_widths[1] > 0){
                                                                            $page->setLineWidth((int)$line_widths[1]);
                                                                            $page->drawLine($padded_left, $bottom_fillbar, ($padded_right), $bottom_fillbar);
                                                                    }
                                                                }
                                                                break;
                                                        case 2 :
                                                                break;
                                                     }
                                                }
                                            }

                                            $this->_setFont($page, $this->_general['font_style_subtitles'], $this->_general['font_size_subtitles'], $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                            if ($product_images_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($images_title), $imagesX, $this->y, 'UTF-8');
                                            }
                                            if ($serial_code_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($serial_code_title), ($serial_codeX + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }
                                            $page->drawText(Mage::helper('sales')->__($qty_title), $qtyX + 8, $this->y, 'UTF-8');
                                            if ($show_name_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($items_title), ($productX + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }

                                            if($show_gift_wrap_yn == 1){
                                                $page->drawText(Mage::helper('sales')->__($gift_wrap_title), ($gift_wrap_xpos + $first_item_title_shift_items), $this->y, 'UTF-8');
                                            }

                                            if ($product_sku_yn == 1) $page->drawText(Mage::helper('sales')->__($sku_title), ($skuX + $first_item_title_shift_sku), $this->y, 'UTF-8');

                                            if ($product_sku_barcode_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_title), ($sku_barcodeX - 1), $this->y, 'UTF-8');

                                            if ($product_sku_barcode_2_yn != 0) $page->drawText(Mage::helper('sales')->__($sku_barcode_2_title), ($sku_barcodeX_2 - 1), $this->y, 'UTF-8');

                                            if ($product_stock_qty_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($product_stock_qty_title), ($stockqtyX), $this->y, 'UTF-8');
                                            }

                                            if ($product_options_yn == 'yescol') {
                                                $page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y, 'UTF-8');
                                            }

                                            if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0) {
                                                $page->drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y, 'UTF-8');
                                            }

                                            if ($combine_custom_attribute_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y, 'UTF-8');
                                            }

                                            if ($prices_yn != '0') {
                                                $page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
                                                $page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
                                            }
                                            if($show_allowance_yn == 1){
                                                $page->drawText(Mage::helper('sales')->__($show_allowance_title), $show_allowance_xpos, $this->y, 'UTF-8');
                                            }
                                            if ($tax_col_yn == 1) {
                                                $page->drawText(Mage::helper('sales')->__($tax_title), $taxEachX, $this->y, 'UTF-8');
                                            }

                                            $this->y = ($this->y - 28);
                                            $items_y_start = $this->y;

                                            $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], '#333333');
                                        }
                                        if ($background_color_product_temp != '#FFFFFF') {
                                            if ($has_shown_product_image == 1) {
                                                if ($product_images_line_nudge != 0) $this->y = $this->y + ($product_images_line_nudge * -1);
                                            } else {
                                            }
                                                $this->y += $this->_general['font_size_body'] - 5;
                                                $page->setLineWidth(0.5);
                                                //$this->y -= 5;
                                                $page->setFillColor($background_color_product);
                                                $page->setLineColor($background_color_product);
                                                $page->drawLine($padded_left, ($this->y), $padded_right, ($this->y ));
                                                $page->setFillColor($black_color);
                                                $this->y -= (($this->_general['font_size_body']));
                                        }

                                        $product = $_newProduct = $helper->getProductForStore($child->getProductId(), $storeId);
                                        $sku = $child->getSku();
                                        $price = $child->getPriceInclTax();
                                        $qty = (int)$child->getQtyOrdered();
                                        if ($store_view == "storeview") {
                                            $name = $child->getName();
                                        } elseif($store_view == "specificstore" && $specific_store_id != ""){
                                            $_product = $helper->getProductForStore($child->getProductId(), $specific_store_id);
                                            if ($_product->getData('name')) $name = trim($_product->getData('name'));
                                            if ($name == '') $name = trim($child->getName());
                                        }
                                        else{
                                            $name = $this->getNameDefaultStore($child);

                                        }
                                        $this->y -= $line_height*1.3;
                                        $options_y_counter += $line_height;
                                        if ($from_shipment == 'shipment') {
                                            $productXInc = 25;
                                            switch ($show_qty_options) {
                                                case 1:
                                                    $price_qty = $qty;
                                                    $productXInc = 0;
                                                    break;
                                                case 2:
                                                    $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                                    $productXInc = 25;
                                                    break;
                                                case 3:
                                                    $price_qty = (int)$shiped_items_qty[$item->getData('product_id')];
                                                    $productXInc = 25;
                                                    break;
                                            }
                                        } else {
                                            switch ($show_qty_options) {
                                                case 1:
                                                    $price_qty = $qty;
                                                    $productXInc = 0;
                                                    break;
                                                case 2:
                                                    $price_qty = (int)$item->getQtyShipped();
                                                    $productXInc = 25;
                                                    break;
                                                case 3:
                                                    $price_qty = (int)$item->getQtyShipped();
                                                    $productXInc = 25;
                                                    break;
                                            }
                                        }
                                        /***get qty string**/
                                        $qty_string = $this->getQtyStringBundle($from_shipment, $product_build_value, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids, $store_id);
                                        $draw_qty_value = $qty_string;
                                        $price_qty = $qty_string;
                                        $addon_shift_x = $shift_bundle_children_xpos;
                                        //TODO Moo
                                        /*if (($tickbox_yn == 1) || ($tickbox_2_yn == 1)) {
                                            $page->setLineWidth(0.5);
                                            $page->setFillColor($white_color);
                                            $page->setLineColor($black_color);
                                            if ($tickbox_yn == 1) {
                                                $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                                $page->drawRectangle($tickboxX_bundle +$addon_shift_x, ($this->y - $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX_bundle +$addon_shift_x+ $tickbox_width_1*2/3), ($this->y + $tickbox_width_1 / 3 + $this->_general['font_size_body'] / 2 - 3));
                                            }
                                            if ($tickbox_2_yn == 1) {
                                                $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                                $page->drawRectangle($tickboxX_bundle +$addon_shift_x, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 3), ($tickboxX_bundle+$addon_shift_x + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 3));
                                            }
                                            $page->setFillColor($black_color);
                                        }*/
                                        /* printing checkbox */
                            /************************PRINTING CHECKBOX**************************/
                            if (isset($sku_supplier_item_action[$supplier][$sku]) && $sku_supplier_item_action[$supplier][$sku] != 'hide' && !$hide_bundle_parent_f) {
                                if ($sku_supplier_item_action[$supplier][$sku] == 'keepGrey') {
                                    $page->setFillColor($greyout_color);
                                } elseif (($tickbox_yn == 1) || ($tickbox_2_yn == 1)) {
                                    $page->setLineWidth(0.5);
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($black_color);
                                    if ($tickbox_yn == 1) {
                                        $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                        if ($doubleline_yn == 1.5)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                        elseif ($doubleline_yn == 2)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                        elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                        else
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                        /* tickbox 1 signature line */
                                        if ($this->_getConfig('tickbox_signature_line', 0, false, $wonder, $order_storeId)){
                                            $page->drawLine(($tickboxX - ($tickbox_width_1 - 2)), ($this->y + 2), ($tickboxX - ($tickbox_width_1 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                        }
                                    }
                                    if ($tickbox_2_yn == 1) {
                                        $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                        if ($doubleline_yn == 1.5)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                        elseif ($doubleline_yn == 2)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                        elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                        else
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                        /* tickbox 2 signature line */
                                        if ($this->_getConfig('tickbox_2_signature_line', 0, false, $wonder, $order_storeId)){
                                            $page->drawLine(($tickbox2X - ($tickbox_width_2 - 2)), ($this->y + 2), ($tickbox2X - ($tickbox_width_2 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                        }
                                    }
                                    $page->setFillColor($black_color);
                                }
                            } elseif ((($tickbox_yn == 1) || ($tickbox_2_yn == 1)) && !$hide_bundle_parent_f) {
                                $page->setLineWidth(0.5);
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                if ($tickbox_yn == 1) {
                                    $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                    if ($doubleline_yn == 1.5)
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                    elseif ($doubleline_yn == 2)
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                    elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                    else
                                        $page->drawRectangle($tickboxX, ($this->y - $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickboxX + $tickbox_width_1), ($this->y + $tickbox_width_1 / 2 + $this->_general['font_size_body'] / 2 - 1));

                                    $after_print_ticbox1 = $this->y - $tickbox_width_1 + $this->_general['font_size_body'];
                                    /* tickbox 1 signature line */
                                    if ($this->_getConfig('tickbox_signature_line', 0, false, $wonder, $order_storeId)){
                                        $page->drawLine(($tickboxX - ($tickbox_width_1 - 2)), ($this->y + 2), ($tickboxX - ($tickbox_width_1 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                    }
                                }
                                if ($tickbox_2_yn == 1) {
                                    $tickbox_width_2 = $this->_getConfig('tickbox2_width', 7, false, $wonder, $store_id);
                                    if ($doubleline_yn == 1.5)
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 1.5));
                                    elseif ($doubleline_yn == 2)
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 2));
                                    elseif ($doubleline_yn == 3)
                                            $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3), ($tickbox2X + $tickbox_width_2), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] - 1 - $this->_general['font_size_body'] / 3));
                                    else
                                        $page->drawRectangle($tickbox2X, ($this->y - $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1), ($tickbox2X + $tickbox_width_1), ($this->y + $tickbox_width_2 / 2 + $this->_general['font_size_body'] / 2 - 1));
                                    $after_print_ticbox2 = $this->y - $tickbox_width_2 + $this->_general['font_size_body'];
                                    /* tickbox 2 signature line */
                                    if ($this->_getConfig('tickbox_2_signature_line', 0, false, $wonder, $order_storeId)){
                                        $page->drawLine(($tickbox2X - ($tickbox_width_2 - 2)), ($this->y +2), ($tickbox2X - ($tickbox_width_2 * ($this->_general['font_size_body'] / 2))), ($this->y + 2));
                                    }
                                }
                                $page->setFillColor($black_color);
                            }
                            if ($numbered_product_list_yn == 1 && !$hide_bundle_parent_f) {
                                $page->drawText($temp_count . $numbered_list_suffix, $numbered_product_list_X, ($this->y), 'UTF-8');
                            }
                            if (!isset($max_chars)) {
                                $maxWidthPage = ($padded_right + 20) - ($productX + $productXInc + $offset);
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($font_size_options);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / 10;
                                $max_chars = round($maxWidthPage / $char_width);
                            }

                            $line_height = (1.15 * $this->_general['font_size_body']);
                            if (is_numeric($product_build_value['qty_string']))
                                $draw_qty_value = round($product_build_value['qty_string'], 2);
                            else
                                $draw_qty_value = $product_build_value['qty_string'];
                            if($this->_general['font_family_body'] == 'traditional_chinese' || $this->_general['font_family_body'] == 'simplified_chinese'){
                                $font_family_body_temp = $this->_general['font_family_body'];
                                $this->_general['font_family_body'] = 'helvetica';
                            }
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $draw_qty_value = round($product_build_value['qty_string'], 2);
                                        /* printing checkbox */

                                        if ($numbered_product_list_bundle_children_yn == 1) {
                                            $page->drawText($temp_count . $numbered_list_suffix, $numbered_product_list_bundle_children_X +$addon_shift_x, ($this->y), 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE SKU**********************/
                                        if ($product_sku_yn == 1)
                                            $page->drawText($sku, $skuX + $addon_shift_x, $this->y, 'UTF-8');

                                        /***************************PRINTING BUNDLE BARCODE**********************/
                                        if (($product_sku_barcode_yn != 0) && !$hide_bundle_parent_f) {
                                            $after_print_barcode_y = $this->y;
                                            $sku_barcodeY = $this->y - 4;
                                            $barcode = $sku;

                                            if ($product_sku_barcode_yn == 2)
                                                $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,1,true,$child->getProductId());
                                            $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        if (($product_sku_barcode_2_yn != 0) && !$hide_bundle_parent_f) {
                                            $after_print_barcode_y = $this->y;
                                            $sku_barcodeY = $this->y - 4;
                                            $barcode = $sku;
                                            if ($product_sku_barcode_2_yn == 2)
                                                $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $store_id,2,true,$child->getProductId());
                                            $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX_2,$sku_barcodeY,$padded_right,$font_family_barcode,11,$white_color);
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }

                                        if($this->_general['font_family_body'] == 'traditional_chinese' || $this->_general['font_family_body'] == 'simplified_chinese'){
                                            $font_family_body_temp = $this->_general['font_family_body'];
                                            $this->_general['font_family_body'] = 'helvetica';
                                        }
                                        $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                        $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 0, false, $wonder, $store_id);

                                        /***************************PRINTING BUNDLE QTY**********************/
                                        if ($product_qty_upsize_yn == 1 && $qty_string > 1) {
                                            if ($product_qty_red == 1) $this->_setFont($page, 'bold', $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'darkRed');
                                            if ($product_qty_rectangle == 1) {
                                                $page->setLineWidth(1);
                                                $page->setLineColor($black_color);
                                                $page->setFillColor($black_color);
                                                if ($product_qty_red == 1) $page->setLineColor($red_color);
                                                {

                                                    if (($qty_string >= 100) || (strlen($draw_qty_value) >= 3)) {
                                                        $page->drawRectangle(($qtyX +$addon_shift_x+ 7), ($this->y - 3), ($qtyX +$addon_shift_x+ 9 + (strlen($draw_qty_value) * 2*$font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                    } else
                                                        if (($qty_string >= 10) || (strlen($draw_qty_value) > 2)) {
                                                            $page->drawRectangle(($qtyX +$addon_shift_x + 7), ($this->y - 3), ($qtyX +$addon_shift_x + 9 +(strlen($draw_qty_value) *2* $font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                        } else {
                                                            $page->drawRectangle(($qtyX +$addon_shift_x + 7), ($this->y - 3), ($qtyX+$addon_shift_x + 9 + (strlen($draw_qty_value) *2* $font_size_options/3)), ($this->y - 5 + $this->_general['font_size_body'] * 1.2));
                                                        }
                                                }
                                                $this->_setFont($page,'bold', $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], 'white');
                                                $page->drawText($draw_qty_value, ($qtyX +$addon_shift_x+ 8), ($this->y - 1), 'UTF-8');
                                            } else {
                                                if ($product_qty_underlined == 1) {
                                                    $page->setLineWidth(1);
                                                    $page->setLineColor($black_color);
                                                    $page->setFillColor($white_color);
                                                    if ($product_qty_red == 1) $page->setLineColor($red_color);
                                                    $page->drawLine(($qtyX +$addon_shift_x+ 7), ($this->y - 3), ($qtyX +$addon_shift_x+ 6 + (strlen($qty_string) * $this->_general['font_size_body'])), ($this->y - 3));
                                                }
                                                $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($qty_string, ($qtyX +$addon_shift_x+ 8), ($this->y - 1), 'UTF-8');
                                            }
                                            $this->_setFont($page, $this->_general['font_style_body'], $font_size_options, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        } else
                                            $page->drawText($qty_string, ($qtyX +$addon_shift_x+ 8), $this->y, 'UTF-8');

                                        if(isset($font_family_body_temp)){
                                            $this->_general['font_family_body'] = $font_family_body_temp;
                                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }
                                        /***************************PRINTING BUNDLE PRICE**********************/
                                        if ($prices_yn != '0') {
                                            $bundle_options_part_price_total = ($price_qty * $price);
                                            $bundle_price_display = $this->formatPriceTxt($order, $price);
                                            $bundle_price_total_display = $this->formatPriceTxt($order, $bundle_options_part_price_total);

                                            if ($price > 0) $page->drawText($bundle_price_display , $priceEachX, $this->y, 'UTF-8');
                                            if ($bundle_options_part_price_total > 0) $page->drawText( $bundle_price_total_display, $priceX +$addon_shift_x, $this->y, 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE NAME**********************/
                                        $after_print_name_bundle_y = $this->y;
                                        $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $next_col_to_product_x = getPrevNext2($this->columns_xpos_array, 'productX', 'next');
                                        $max_name_length = $next_col_to_product_x - $productX;
                                        $line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->_general['font_size_body']));
                                        $char_width_name = ceil($line_width_name / strlen($name));
                                        $max_chars_name = round($max_name_length / $char_width_name);
                                        $multiline_name = wordwrap($name, $max_chars_name, "\n");
                                        $name_trim = str_trim($name, 'WORDS', $max_chars_name - 3, '...');
                                        $token = strtok($multiline_name, "\n");
                                        $character_breakpoint_name = stringBreak($name, $max_name_length, $this->_general['font_size_body'], $font_helvetica);
                                        $display_name = '';
                                        $name_length = 0;

                                        if (strlen($name) > ($character_breakpoint_name + 2)) {
                                            $display_name = $name_trim;
                                        } else $display_name = htmlspecialchars_decode($name);

                                        $token = strtok($multiline_name, "\n");
                                        $multiline_name_array = array();
                                        $temp_y = $this->y;
                                        $after_print_name_y = 1;
                                        if ($show_name_yn == 1 && $after_print_name_y) {
                                            if ($this->_getConfig('product_name_bold_yn', 0, false, $wonder, $order_storeId) || (Mage::getStoreConfig('pickpack_options/general/product_name_style') == 1)){
                                                $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            }
                                            if ($this->_getConfig('trim_product_name_yn', 0, false, $wonder, $order_storeId)) {
                                                $page->drawText($display_name, ($productX +$addon_shift_x+ $productXInc + 2), $temp_y, 'UTF-8');
                                            } else {
                                                if ($token != false) {
                                                    while ($token != false) {
                                                        $multiline_name_array[] = $token;
                                                        $token = strtok("\n");
                                                    }

                                                    foreach ($multiline_name_array as $name_in_line) {
                                                        $page->drawText($name_in_line, ($productX+$addon_shift_x + $productXInc + 2), $temp_y, 'UTF-8');
                                                        $temp_y -= $line_height;
                                                    }
                                                    $temp_y += $line_height;
                                                }
                                            }
                                        }

                                        $this->y = $temp_y;
                                        $after_print_name_bundle_y = $this->y; // - $line_height;
                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        unset($multiline_name_array);

                                        /***************************PRINTING BUNDLE TICKBOX**********************/
                                        // if ($tickbox_yn) {
                                            // $box_x = ($productX + $productXInc + $offset);
                                            // $tickbox_width_1 = $this->_getConfig('tickbox_width', 7, false, $wonder, $store_id);
                                            // $page->setLineWidth(0.5);
                                            // $page->setFillColor($white_color);
                                            // $page->setLineColor($black_color);
                                            // //$page->drawRectangle(($tickboxX_bundle), ($this->y), ($tickboxX_bundle + 6), ($this->y + 6));
                                            // $page->drawRectangle(($tickboxX_bundle), ($this->y - $tickbox_width_1 + $this->_general['font_size_body'] - 3), ($tickboxX_bundle + $tickbox_width_1), ($this->y + $this->_general['font_size_body'] - 3));
                                            // $page->setFillColor($black_color);
                                            // $after_print_ticbox1 = $this->y - $tickbox_width_1 + $this->_general['font_size_body'];
                                        // }

                                        //draw backordered children bundle
                                        if ($product_qty_backordered_yn == 1) {
                                            $backordered_children_bundle = (int)($child->getData("qty_backordered"));
                                            $page->drawText($backordered_children_bundle, ($prices_qtybackorderedX), $this->y, 'UTF-8');
                                        }
                                        if ($product_warehouse_yn == 1) {
                                            $item_warehouse = $child->getWarehouseTitle();
                                            $page->drawText($item_warehouse, ($prices_warehouseX), $this->y, 'UTF-8');
                                        }
                                        /***************************PRINTING BUNDLE SHELVING**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                    $shelving_real_attribute = $this->_getConfig('shelving_real', 'shelf', false, $wonder, $order_storeId);
                    $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelfX',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);

                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }


                                        /***************************PRINTING BUNDLE SHELVING 2**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                    $shelving_real_attribute = $this->_getConfig('shelving', 'shelf', false, $wonder, $order_storeId);
                    $shelving_real_yn = $this->_getConfig('shelving_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf2X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);

                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }
                                        /***************************PRINTING BUNDLE SHELVING 3**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
                $shelving_real_attribute = $this->_getConfig('shelving_2', 'shelf', false, $wonder, $order_storeId);
                $shelving_real_yn = $this->_getConfig('shelving_2_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf3X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);

                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }

                                        /***************************PRINTING BUNDLE SHELVING 4**********************/
                                        $shelving_real = '';
                                        $flag_newpage_shelving_real = 0;
               $shelving_real_attribute = $this->_getConfig('shelving_3', 'shelf', false, $wonder, $order_storeId);
               $shelving_real_yn = $this->_getConfig('shelving_3_yn', 'shelf', false, $wonder, $order_storeId);
                                        $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$padded_right,'shelf4X',$addon_shift_x,$order_storeId,$page_count,$items_header_top_firstpage,$page_top,$order_number_display);

                                        if($after_print_name_bundle_y < $this->y)
                                            $this->y = $after_print_name_bundle_y - $this->_general['font_size_body'] / 2;
                                        if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                        {
                                            $this->y = $after_print_barcode_y;
                                        }

               /* end of printing bundle shelving */
                                        if ($doubleline_yn == 2) $this->y -= 15;
                                        else
                                            if ($doubleline_yn == 1.5) $this->y -= 7.5;
                                            else
                                                if ($doubleline_yn == 3) $this->y -= 20;
                                                else
                                                    $this->y += 3.5;

                            $product_qty_upsize_yn_morespace = $this->_getConfig('product_qty_upsize_yn', 0, false, $wonder, $store_id);
                            if ($product_qty_upsize_yn_morespace == 'u' || $product_qty_upsize_yn_morespace == 'c') {
                                $this->y -= 0.5 * $this->_general['font_size_body'];
                            }
                            unset($after_print_name_bundle_y);
                    }
    endif;
    /* split bundle option */
    $this->y -= 10;

                    $this->_general['font_color_body'] = $font_color_body_temp;
                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                    if ($print_item_count == 0) {
                        if (($show_qty_options == 4))
                            $page->drawText('There are no invoiced items for this order', 50, $this->y, 'UTF-8');
                        else if ($show_qty_options == 3)
                            $page->drawText('There are no unshipped items for this order', 50, $this->y, 'UTF-8');
                    }
                    //$this->y -= $this->_general['font_size_body'];
                    unset($product_build);
                    unset($product_build_value);
                    unset($sku);

                    /***************************PRINTING TOTALS********************************/
                    $use_magento_subtotal = $this->_getConfig('use_magento_subtotal', 0, false, $wonder, $store_id);
                    $subtotal_align = $this->_getConfig('subtotal_align', 1, false, $wonder, $store_id);
                    $subtotal_align_pos = explode(',', $this->_getConfig('subtotal_align_xpos', '410,460', false, $wonder, $store_id));
                    $subtotal_count = 0;
                    $paid_or_due_shown = 0;             
                        
                    $default_total_position = 1;
                    $default_total_position_footer = 100;
                        
                    if($use_magento_subtotal == 1)
                        $use_defaul_total = 2;
                    else
                        $use_defaul_total = 0;
                        
                    if($use_defaul_total == 2)
                    {
                        $totals = array();
                        $moo_pdf_abstract = new Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Abstract();
                        $totals2 = $moo_pdf_abstract->PrepareTotals($order,$store_id);
                        $counter_temp1 = 0;
                        $counter_temp2 = 100;
                        foreach($totals2 as $total_type => $arr)
                        {
                            if(is_array($arr))
                            {                               
                                foreach ($arr as $type => $total_ele)
                                {
                                    if($total_type == 'grand_totals')
                                        $counter_temp = $counter_temp2++;
                                    else
                                        $counter_temp = $counter_temp1++;
                                     $total_ele['text'] = trim($total_ele['text']);
                                     $totals[$counter_temp] = $total_ele;
                                }
                            }
                        }
                        
                        $order_block = new Moogento_Pickpack_Block_Adminhtml_Sales_Order_Totals();
                        $order_block->setOrder($order);
                        $order_totals_tax  =$order_block->getFullTaxInfo(); 
                        foreach ($order_totals_tax as  $_total) 
                        {
                            $temp_index = $counter_temp1++;
                            $tax_title_temp  = 'Total Tax:';//Mage::helper('pickpack/functions')->clean_method($_total['title'],'pdf_more'); 
                            $totals[$temp_index]['key'] = $tax_title_temp;
                            $totals[$temp_index]['text'] = $tax_title_temp;
                            $totals[$temp_index]['value'] = $_total['tax_amount'];
                            $totals[$temp_index]['base_value'] = $_total['base_tax_amount'];
                            $totals[$temp_index]['percent'] = $_total['percent'];
                    
                        }   
                        
                        ksort($totals);
                    }
                    else
                    if($use_defaul_total == 1)
                    {
                        $order_block = new Moogento_Pickpack_Block_Adminhtml_Sales_Order_Totals();
                        $order_block->setOrder($order);
                        $order_block->_initTotals();
                        $block_order =  $order_block->getOrder();
                        $order_totals = $order_block->getTotals();
                        $order_totals_tax  =$order_block->getFullTaxInfo();     
                        $totals = array();
                        foreach ($order_totals as $_code => $_total) 
                        {  
                            if($_total->getData('area') == 'footer')
                            {
                                $temp_index = $default_total_position_footer;
                                $default_total_position_footer++;
                            }
                            else
                            {
                                $temp_index = $default_total_position;
                                $default_total_position++;
                            }

                            $totals[$temp_index]['key'] = $_total->getData('code');
                            $totals[$temp_index]['text'] = $_total->getData('label');
                            $totals[$temp_index]['value'] = $_total->getData('value');
                    
                        }
                        foreach ($order_totals_tax as  $_total) 
                        {
                            $temp_index = $default_total_position;
                            $default_total_position++;
                            $tax_title_temp  = 'Tax';//Mage::helper('pickpack/functions')->clean_method($_total['title'],'pdf_more'); 
                            $totals[$temp_index]['key'] = $tax_title_temp;
                            $totals[$temp_index]['text'] = $tax_title_temp;
                            $totals[$temp_index]['value'] = $_total['tax_amount'];
                            $totals[$temp_index]['base_value'] = $_total['base_tax_amount'];
                            $totals[$temp_index]['percent'] = $_total['percent'];
                    
                        }

                        ksort($totals);
                    }
                    
                   if (isset($totals) && is_array($totals) && ($prices_yn != 0) && ($use_defaul_total > 0))
                    {
                        $this->y -= 2;
                        if ($doubleline_yn == 2) $this->y += 10;
                        elseif ($doubleline_yn == 1.5) $this->y += 2.5;
                        // take account of extra tax line in subtotal
                        if ($prices_yn == 1 && ($tax_yn == 'yessubtotal' || $tax_yn == 'yesboth')) $min_product_y += ($this->_general['font_size_subtitles'] + 2);
                        $priceTextX = 410;
                        if ($this->_packingsheet['page_size'] == 'a5-portrait') $priceTextX = 250;

                        $shipping_cost = 0;
                        $tax_amount = 0;
                        if ($this->y < ($min_product_y + ($this->_general['font_size_body'] + 2) * (count($totals)) + $packedByXY[1])) {
                            $page = $this->nooPage($this->_packingsheet['page_size']);
                            $page_count++;
                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                            if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                            else $this->y = $page_top - $this->_general['font_size_body'];
                            $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                            $font_temp = $this->getFontName2($this->_general['font_family_subtitles'], $this->_general['font_style_subtitles'], $this->_general['non_standard_characters']);
                            $paging_text_width = $this->parseString($paging_text, $font_temp, $this->_general['font_size_subtitles'] - 2);
                            $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));
                            $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');

                            $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2) - 5);
                            if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                $page->setFillColor($background_color_subtitles_zend);
                                $page->setLineColor($background_color_subtitles_zend);
                                $page->setLineWidth(0.5);
                                $page->drawLine($padded_left, ($this->y), $padded_right, ($this->y));
                            }
                        } else {
                            /***PRINTING draw line before totals***/
                            if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                $page->setFillColor($background_color_subtitles_zend);
                                $page->setLineColor($background_color_subtitles_zend);
                                $page->setLineWidth(0.5);
                                $page->drawLine($padded_left, ($this->y), $padded_right, ($this->y));
                            }
                        }
                        //End new logic for custom image after product list and total showing

                        if ($background_color_subtitles != '#FFFFFF') $this->y -= 20;
                        else $this->y -= 10;
                        // take account of extra tax line in subtotal
                        if ($prices_yn == 1 && ($tax_yn == 'yessubtotal' || $tax_yn == 'yesboth'))
                            $min_product_y += ($this->_general['font_size_subtitles'] + 2);
                        // Fix for subtotal padding top.
                        // Fix for subtotal padding top.
                        $this->y += $this->_general['font_size_body'] * 0.75;

                        if ($this->_getConfig('subtotal_price_xpos_options', 1, false, $wonder, $store_id) == 2)
                            $subtotal_price_xpos = $this->_getConfig('subtotal_price_xpos', 1, false, $wonder, $store_id);
                        else {
                            if ($priceX > $full_page_width && $priceEachX > $full_page_width / 2)
                                $subtotal_price_xpos = $priceEachX;
                            else

                                $subtotal_price_xpos = $priceX;
                        }
                        $storeSymbolCode = Mage::app()->getLocale()->currency($order->getStore()->getOrderCurrencyCode())->getSymbol();
                        $orderSymbolCode = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();
                        $order_currency_code = $order->getOrderCurrencyCode();
                        $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
                        $storeSymbolCode = Mage::app()->getLocale()->currency($store_currency_code)->getSymbol();
                        $orderSymbolCode = Mage::app()->getLocale()->currency($order_currency_code)->getSymbol();   
                        $show_base_currency_value = $this->_getConfig('show_base_currency_value', 0, false, $wonder, $store_id);
                        $show_currency_exchange_rate = $this->_getConfig('show_currency_exchange_rate', 0, false, $wonder, $store_id);

                        /****PRINTING TOTALS***/
                     
                        foreach ($totals as $key => $value) {
                            $is_coupon = false;
                            if ($value > 0) {
                                // don't show zero value shipping
                                if (($this->_getConfig('show_zero_shipping_fee', 0, false, $wonder, $store_id) == 0) && ($totals[$key]['key'] == 'shipping_base') && ($totals[$key]['value'] == '0.0000')) {
                                    continue;
                                }

                                 


                                    if($key >= 100)
                                    {
                                        $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        if ($paid_or_due_shown == 0) {
                                            if ($fix_subtotal == 0) {
                                                $this->y+=8;
                                                $page->drawLine(($priceTextX - ($this->_general['font_size_body'] * 9)), ($this->y), $padded_right, ($this->y));
                                                if ($background_color_subtitles != '#FFFFFF') $this->y -= (20 - ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4)));
                                                else $this->y -= (10 - ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4)));
                                                $this->y -=2;
                                            }
                                            $paid_or_due_shown = 1;
                                        }
                                    }
                                    else
                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                                    
                                        
                                    if ($subtotal_align == 1) {
                                        $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                        $subtotal_label_xpos = $this->rightAlign2($totals[$key]['text'], Zend_Pdf_Font::FONT_HELVETICA, $page->getFontSize(), 12, $subtotal_label_rightalign_xpos);
                                    } else $subtotal_label_xpos = $subtotal_align_pos[0];
                                    if ($title_invert_color == 1) {
                                        $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] + 1), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], '#FFFFFF');
                                        $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                        $this->_setFont($page, 'bold', ($this->_general['font_size_subtitles'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    } else $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                    $page->drawText($this->formatPriceTxt($order,number_format($totals[$key]['value'], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                    if (($order_currency_code != $store_currency_code) && ($show_base_currency_value == 1)) {
                                        $this->y -= $this->_general['font_size_body'];
                                        $convert_to_store_currency = round($this->convertCurrency(number_format($totals[$key]['value'], 2, '.', ','), $order_currency_code, $store_currency_code), 2);
                                        $convert_to_store_currency_text = '[' . $storeSymbolCode . $convert_to_store_currency . ']';
                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        $page->drawText($convert_to_store_currency_text, $subtotal_price_xpos, $this->y, 'UTF-8');
                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    }
                                    $this->y -= 15;
                                $subtotal_count++;
                            }
                        }
                    }
                    else
                    {
                        if ($prices_yn != 0) {
                            if(method_exists(Mage::helper('tax'), 'getCalculatedTaxes'))
                                $_fullInfo = Mage::helper('tax')->getCalculatedTaxes($order);
                            else{
                            if (Mage::registry('current_invoice')) {
                                $current = Mage::registry('current_invoice');
                            } elseif (Mage::registry('current_creditmemo')) {
                                $current = Mage::registry('current_creditmemo');
                            } else {
                                $current = $order;
                            }

                            $taxClassAmount = array();
                            if ($current && $order) {
                                if ($current == $order) {
                                    // use the actuals
                                    $rates = Mage::getModel('tax/sales_order_tax')->getCollection()->loadByOrder($order)->toArray();
                                    foreach ($rates['items'] as $rate) {
                                        $taxClassId = $rate['tax_id'];
                                        $taxClassAmount[$taxClassId]['tax_amount'] = $rate['amount'];
                                        $taxClassAmount[$taxClassId]['base_tax_amount'] = $rate['base_amount'];
                                        $taxClassAmount[$taxClassId]['title'] = $rate['title'];
                                        $taxClassAmount[$taxClassId]['percent'] = $rate['percent'];
                                    }
                                } else {
                                    // regenerate tax subtotals
                                    // Calculate taxes for shipping
                                    $shippingTaxAmount = $current->getShippingTaxAmount();
                                    if ($shippingTaxAmount) {
                                        $shippingTax    = Mage::helper('tax')->getShippingTax($current);
                                        $taxClassAmount = array_merge($taxClassAmount, $shippingTax);
                                    }

                                    foreach ($current->getItemsCollection() as $item) {
                                        $taxCollection = Mage::getResourceModel('tax/sales_order_tax_item')
                                            ->getTaxItemsByItemId(
                                                $item->getOrderItemId() ? $item->getOrderItemId() : $item->getItemId()
                                            );

                                        foreach ($taxCollection as $tax) {
                                            $taxClassId = $tax['tax_id'];
                                            $percent = $tax['tax_percent'];

                                            $price = $item->getRowTotal();
                                            $basePrice = $item->getBaseRowTotal();
                                            if (Mage::helper('tax')->applyTaxAfterDiscount($item->getStoreId())) {
                                                $price = $price - $item->getDiscountAmount() + $item->getHiddenTaxAmount();
                                                $basePrice = $basePrice - $item->getBaseDiscountAmount() + $item->getBaseHiddenTaxAmount();
                                            }
                                            $tax_amount = $price * $percent / 100;
                                            $base_tax_amount = $basePrice * $percent / 100;

                                            if (isset($taxClassAmount[$taxClassId])) {
                                                $taxClassAmount[$taxClassId]['tax_amount'] += $tax_amount;
                                                $taxClassAmount[$taxClassId]['base_tax_amount'] += $base_tax_amount;
                                            } else {
                                                $taxClassAmount[$taxClassId]['tax_amount'] = $tax_amount;
                                                $taxClassAmount[$taxClassId]['base_tax_amount'] = $base_tax_amount;
                                                $taxClassAmount[$taxClassId]['title'] = $tax['title'];
                                                $taxClassAmount[$taxClassId]['percent'] = $tax['percent'];
                                            }
                                        }
                                    }
                                }

                                foreach ($taxClassAmount as $key => $tax) {
                                    if ($tax['tax_amount'] == 0 && $tax['base_tax_amount'] == 0) {
                                        unset($taxClassAmount[$key]);
                                    }
                                }
                                $taxClassAmount = array_values($taxClassAmount);
                            }
                            $_fullInfo =  $taxClassAmount;
                            }
                            $this->y -= 2;
                            if ($doubleline_yn == 2) $this->y += 10;
                            elseif ($doubleline_yn == 1.5) $this->y += 2.5;
                            // take account of extra tax line in subtotal
                            if ($prices_yn == 1 && ($tax_yn == 'yessubtotal' || $tax_yn == 'yesboth')) $min_product_y += ($this->_general['font_size_subtitles'] + 2);
                            $priceTextX = 410;
                            if ($this->_packingsheet['page_size'] == 'a5-portrait') $priceTextX = 250;

                            $totals = array();
                            $shipping_cost = 0;
                            $tax_amount = 0;

                            /*
                             [shipping_plus_tax] => 21.6500
                             [shipping_ex_tax] => 20.0000

                             [surcharge]         => 0
                             [surcharge_label]     =>
                             [sub_total]         => 619.9600
                             [taxamount]         => 13.2000
                             [shipping_base]     => 20.0000
                             --------------------------------
                             [grand_total]         => 653.1600

                             subtotal_order[0]Subtotal
                             [1]Discounts
                             [2]Tax
                             [3]Shipping
                             */
                            /****PREPARE DATA BEFORE PRINTING TOTALS****/
                            /**
                             * extra charges
                             */
                            if ($order->getData('fooman_surcharge_amount_invoiced') && $order->getData('fooman_surcharge_amount_invoiced') > 0) {
                                $fooman_surcharge_position = 96;
                                $totals[$fooman_surcharge_position]['key'] = 'fooman_surcharge_amount_invoiced';
                                $totals[$fooman_surcharge_position]['text'] = $order->getData('fooman_surcharge_amount_invoiced');
                                $totals[$fooman_surcharge_position]['value'] = $order->getData('fooman_surcharge_amount_invoiced');
                            }
                            if (@$order->getMultifees() && ($order->getMultifees() > 0)) {
                                $mage_surcharge_position = 90;
                                $totals[$mage_surcharge_position]['key'] = 'mage_surcharge';
                                $totals[$mage_surcharge_position]['text'] = Mage::helper('multifees')->__('Additional Fees');
                                $totals[$mage_surcharge_position]['value'] = $order->getMultifees();
                            }

                            /**
                             * surcharge
                             */

                            $totals_xtra['surcharge'] = null;
                            $totals_xtra['surcharge_label'] = null;
                            // fooman surcharge
                            // if(@$order->getBaseFoomanSurchargeAmount() &&$order->getBaseFoomanSurchargeAmount()>0)             $totals_xtra['surcharge'] += $order->getBaseFoomanSurchargeAmount();
                            if (@$order->getFoomanSurchargeAmount() && $order->getFoomanSurchargeAmount() > 0) $totals_xtra['surcharge'] += $order->getFoomanSurchargeAmount();
                            // if(@$order->getBaseFoomanSurchargeTaxAmount() && $order->getBaseFoomanSurchargeTaxAmount()>0)     $totals_xtra['surcharge'] += $order->getBaseFoomanSurchargeTaxAmount();
                            if (@$order->getFoomanSurchargeTaxAmount() && $order->getFoomanSurchargeTaxAmount() > 0) $totals_xtra['surcharge'] += $order->getFoomanSurchargeTaxAmount();
                            if (@$order->getFoomanSurchargeDescription() && trim($order->getFoomanSurchargeDescription()) != '') $totals_xtra['surcharge_label'] = $order->getFoomanSurchargeDescription();
                            if ($totals_xtra['surcharge'] > 0) {
                                $surcharge_position = 80;
                                $totals[$surcharge_position]['key'] = 'surcharge';
                                $totals[$surcharge_position]['text'] = $helper->__('Surcharge');
                                $totals[$surcharge_position]['value'] = $totals_xtra['surcharge'];

                                if ($totals_xtra['surcharge_label'] != null) $totals[$surcharge_position]['text'] = $totals_xtra['surcharge_label'];
                            }

                            /**
                             * shipping
                             */
                            $totals_xtra['shipping_ex_tax'] = $order->getShippingAmount();
                            $totals_xtra['shipping_plus_tax'] = $order->getShippingInclTax();
                            if($multi_prices_yn == 1 && $order_attribute_value != ''){
                                $order_attribute_value_dec = str_replace('%', '', $order_attribute_value) / 100;
                                $totals_xtra['shipping_ex_tax'] = $totals_xtra['shipping_ex_tax'] * $order_attribute_value_dec;
                                $totals_xtra['shipping_plus_tax'] = $totals_xtra['shipping_plus_tax'] * $order_attribute_value_dec;
                            }
                            $totals_xtra['shipping_tax'] = ($totals_xtra['shipping_plus_tax'] - $totals_xtra['shipping_ex_tax']);
                            $totals[$subtotal_order[3]]['key'] = 'shipping_base';
                            $totals[$subtotal_order[3]]['text'] = $helper->__('Shipping ');
                            if ($tax_displayed_in_shipping_yn == 1) $totals[$subtotal_order[3]]['value'] = $totals_xtra['shipping_plus_tax'];
                            else $totals[$subtotal_order[3]]['value'] = $totals_xtra['shipping_ex_tax'];

                            /**
                             * sub total
                             */
                            $totals[$subtotal_order[0]]['key'] = 'sub_total';
                            $totals[$subtotal_order[0]]['text'] = $helper->__('Subtotal');


                            if ($filter_items_by_status == 1)
                                $subtotal_value = $order->getData('subtotal_invoiced');
                            else
                                if ($filter_items_by_status == 2)
                                    $subtotal_value = $order->getData('subtotal_invoiced');
                                else
                                    $subtotal_value = $order->getData('subtotal');

                            if (isset($show_subtotal_options) && $show_subtotal_options == 1 && $multi_prices_yn == 0)
                            {
                                $totals[$subtotal_order[0]]['value'] = $order->getData('subtotal');                                    
                            }
                            else {
                                $totals[$subtotal_order[0]]['value'] = $order_subtotal_value;
                            }
                            if (($tax_displayed_in_shipping_yn == 1) && ($remove_shipping_tax_from_subtotal_yn == 1)) {
                                $totals[$subtotal_order[0]]['value'] -= $totals_xtra['shipping_tax'];
                            }

                            if (isset($subtotal_addon['magikfee']) && $subtotal_addon['magikfee'] != '') {
                                $totals[$subtotal_order[0]]['value'] += $subtotal_addon['magikfee'];
                            }
                                                                
                            /**VAT rateable && zero_rate**/
                            $vat_rateable_yn = $this->_getConfig('total_taxed_product_value', 0, false, $wonder, $store_id);
                            $vat_rateable_title = $this->_getConfig('total_taxed_product_value_title', 'VAT Rateable', false, $wonder, $store_id);
                            $zero_rate_yn = $this->_getConfig('total_untaxed_product_value', 0, false, $wonder, $store_id);
                            $zero_rate_title = $this->_getConfig('total_untaxed_product_value_title', 0, false, $wonder, $store_id);
                            /**
                             * tax total
                             */

                            if ($tax_yn == 'no' || $tax_yn == 'yescol') {
                                if ($filter_items_by_status == 1)
                                    $subtotal_include_tax_value = $order->getData('subtotal_invoiced') + $order->getData('tax_invoiced');
                                else
                                    if ($filter_items_by_status == 2)
                                        $subtotal_include_tax_value = $order->getData('subtotal_invoiced') + $order->getData('tax_invoiced');
                                    else
                                        $subtotal_include_tax_value = $order->getData('subtotal_incl_tax');
                                if (isset($show_subtotal_options) && $show_subtotal_options == 1 && $multi_prices_yn == 0)
                                {
                                    if($order->getSubtotalInclTax() > 0)
                                    $totals[$subtotal_order[0]]['value'] = $order->getSubtotalInclTax();
                                else
                                    {
                    $totals[$subtotal_order[0]]['value'] = $order->getData('base_subtotal') + $order->getData('tax_amount') - $order->getData('base_shipping_tax_amount');
                    }
                                }
                                else
                                    $totals[$subtotal_order[0]]['value'] = $order_subtotal_value;
                            } else {
                                if ($tax_bands_yn == 0) {
                                    $totals[$subtotal_order[2]]['key'] = 'taxamount';
                                    //'Tax';
                                    if ($filter_items_by_status == 1)
                                        $tax_value = $order->getData('tax_invoiced');
                                    else
                                        if ($filter_items_by_status == 2)
                                            $tax_value = $order->getData('tax_invoiced');
                                        else
                                            $tax_value = $order->getData('tax_amount');

                                    $totals[$subtotal_order[2]]['value'] = $tax_value;
                                    $totals[$subtotal_order[2]]['text'] = $tax_label;

                                    if ($tax_yn == 'yesboth' || $tax_yn == 'noboth') {
                                        $totals[$subtotal_order[2]]['text'] = $tax_label . Mage::helper('pickpack')->__(' Incl.');
                                        // add tax to subtotal if already shown tax in product line
                                        if (isset($show_subtotal_options) && $show_subtotal_options == 1 && $multi_prices_yn == 0)
                                        {
                                            if($order->getSubtotalInclTax())
                                            {
                                            $totals[$subtotal_order[0]]['value'] = $order->getSubtotalInclTax();
                                            }
                                            else
                                            {
                                                 $totals[$subtotal_order[0]]['value'] = $order->getData('base_subtotal') + $order->getData('tax_amount') - $order->getData('base_shipping_tax_amount');
                                            }
                                        }
                                        else
                                            $totals[$subtotal_order[0]]['value'] = $order_subtotal_value;
                                    }

                                    if ($remove_shipping_tax_from_tax_subtotal_yn == 1) {
                                        $totals[$subtotal_order[2]]['value'] -= $order->getShippingTaxAmount();
                                    }

                                    if ($totals[$subtotal_order[2]]['value'] < 0.01) {
                                        if (($this->_getConfig('show_zero_tax_value', 0, false, $wonder, $store_id) == 0))
                                            $totals[$subtotal_order[2]] = null;
                                    }
                                } else {
                                    // Subtotal[0]   Discounts[1]    Tax [2]    Shipping[3]

                                    if (isset($show_subtotal_options) && $show_subtotal_options == 1 && $multi_prices_yn == 0)
                                    {
                                        if($order->getSubtotalInclTax() > 0)
                                        $totals[$subtotal_order[0]]['value'] = $order->getSubtotalInclTax();
                                    else
                                        {
                                             $totals[$subtotal_order[0]]['value'] = $order->getData('base_subtotal') + $order->getData('tax_amount') - $order->getData('base_shipping_tax_amount');
                                        }
                                    }
                                    else
                                        $totals[$subtotal_order[0]]['value'] = $order_subtotal_value;
                                    $counted_tax_amount = 0;
                                    
                                    foreach ($tax_percents as $tax_percent => $tax_percent_amount) {
                                        // 20%
                                        // 20.07%
                                        // 20.10%
                                        // -> should all be 20%
                                        // if(!isset($rounded_tax[round($tax_percent,0)])) $rounded_tax[round($tax_percent,0)] = $tax_percent_amount;
                                        // else $rounded_tax[round($tax_percent,0)] = ($rounded_tax[round($tax_percent,0)] + $tax_percent_amount);
                                        if ($tax_percent_amount > 0) {
                                            $tax_percent = number_format($tax_percent, 2, '.', ''); //trim(preg_replace('~\.00(.*)$~','',$tax_percent));
                                            $tax_percent = preg_replace('~\.0(.*)$~', '', $tax_percent);
                                            $tax_percent = preg_replace('~\.1(.*)$~', '', $tax_percent);
                                            $counted_tax_amount += $tax_percent_amount;
                                        }
                                    }

                                    $full_tax_amount = $order->getTaxAmount();
                                    $maybe_shipping_tax = ($full_tax_amount - $counted_tax_amount);
                                    if ($maybe_shipping_tax > 0 && $order->getShippingAmount() > 0) {
                                        $shipping_tax_percent = number_format((($maybe_shipping_tax / $order->getShippingAmount()) * 100), 2, '.', '');
                                        $shipping_tax_percent = preg_replace('~\.0(.*)$~', '', $shipping_tax_percent);
                                        $shipping_tax_percent = preg_replace('~\.1(.*)$~', '', $shipping_tax_percent);
                                        if ($shipping_tax_percent > 0) {
                                            $tax_rate_code[$shipping_tax_percent] = 'Shipping';
                                            if (isset($tax_percents[$shipping_tax_percent])) $tax_percents[$shipping_tax_percent] += $maybe_shipping_tax;
                                            else $tax_percents[$shipping_tax_percent] = $maybe_shipping_tax;
                                        }
                                    }

                                    $tax_position = $subtotal_order[2];
                                    $tax_position_count = 1;
                                    $total_tax_amount = 0;
                                    if ($tax_yn == 'yesboth') {
                                        // add tax to subtotal if already shown tax in product line
                                        /**
                                         * Option to add shipping tax to tax subtotal needed?
                                         */
                                        // add shpping tax to shipping subtotal - MAY NEED SEPARATE OPTION
                                        $totals[$subtotal_order[3]]['value'] += $order->getShippingTaxAmount();
                                    }
                                    foreach ($tax_percents as $tax_percent => $tax_percent_amount) {
                                        if ($tax_percent_amount > 0) {
                                            $total_tax_amount += $tax_percent_amount;
                                            $tax_key_pos = ($tax_position + ($tax_position_count * 10));

                                            if (isset($totals[$tax_key_pos]) && stripos($totals[$tax_key_pos]['text'], 'Shipping') !== false) {
                                                $tax_text = $tax_rate_code[$tax_percent] . " " .$tax_percent . '%)';
                                            } else
                                                if(strpos($tax_rate_code[$tax_percent], $tax_percent . '%') === false)
                                                    $tax_text = $tax_rate_code[$tax_percent] . " " . $tax_percent . '%';
                                                else
                                                    $tax_text = $tax_rate_code[$tax_percent];
                                            foreach ($_fullInfo as $info)
                                            {
                                                if (isset($info['hidden']) && $info['hidden']) continue;
                                                if(round($tax_percent,2) == round($info['percent'],2))
                                                    if(round($tax_percent_amount,2) == round($info['tax_amount'],2))
                                                    {
                                                        $tax_text = trim($info['title']);
                                                    }
                                                break;
                                            }
                                            if (isset($totals[$tax_key_pos]) && stripos($totals[$tax_key_pos]['key'], 'shipping') !== false)
                                                $totals[$tax_key_pos]['key'] = 'shipping_include_tax';
                                            else
                                                $totals[$tax_key_pos]['key'] = 'taxamount_percent';
                                            if (isset($totals[$tax_key_pos]['text']))
                                                $totals[$tax_key_pos]['text'] .= $tax_text; //'Tax';
                                            else
                                                $totals[$tax_key_pos]['text'] = $tax_text; //'Tax';
                                            if (isset($totals[$tax_key_pos]['value']))
                                                $totals[$tax_key_pos]['value'] += $tax_percent_amount; //'Tax';
                                            else
                                                $totals[$tax_key_pos]['value'] = $tax_percent_amount; //'Tax';
                                            $tax_position_count++;
                                        }
                                    }


                                    if ($tax_yn == 'yesboth' || $tax_yn == 'noboth') {
                                        $tax_label = $tax_label . Mage::helper('pickpack')->__(' Incl.');
                                    }
                                        $totals[$subtotal_order[2]]['value'] = $order->getData('base_tax_amount'); 
                                        $totals[$subtotal_order[2]]['key'] = 'taxamount';
                                        $totals[$subtotal_order[2]]['text'] = $tax_label;
                                        // $totals[$subtotal_order[2]]['key'] = 'taxtamount';
                                        // $totals[$subtotal_order[2]]['value'] = $total_tax_amount;                                       
                                        // $totals[$subtotal_order[2]]['text'] = $tax_label;
                                }
                            }
                            if($list_total_by_tax_class == 1){
                                $tax_position_count = 0;
                                $total_tax = array();
                                
                                foreach ($tax_percents as $tax_percent => $tax_percent_amount) {
                                    if ($tax_percent_amount > 0) {
                                        if (stripos($tax_label, 'moms') === false) {
                                            $tax_text = $tax_rate_code[$tax_percent] . " " . $tax_percent . '%';
                                        } else {
                                            $tax_text = $tax_rate_code[$tax_percent] . " " .$tax_percent . '%';
                                        }

                                        if (isset($total_tax[$tax_position_count]['text']))
                                            $total_tax[$tax_position_count]['text'] .= $tax_text; //'Tax';
                                        else
                                            $total_tax[$tax_position_count]['text'] = $tax_text; //'Tax';

                                        foreach ($_fullInfo as $info)
                                        {
                                            if (isset($info['hidden']) && $info['hidden']) continue;
                                            if(round($tax_percent,2) == round($info['percent'],2))
                                                if(round($tax_percent_amount,2) == round($info['tax_amount'],2))
                                                {
                                                    $tax_text = trim($info['title']);
                                                }
                                            break;
                                        }

                                        if (isset($total_tax[$tax_position_count]['value']))
                                            $total_tax[$tax_position_count]['value'] += $tax_percent_amount; //'Tax';
                                        else
                                            $total_tax[$tax_position_count]['value'] = $tax_percent_amount; //'Tax';

                                        if (isset($total_tax[$tax_position_count]['text_value_total']))
                                            $total_tax[$tax_position_count]['text_value_total'] .= "Nettobetrag"; //'Tax';
                                        else
                                            $total_tax[$tax_position_count]['text_value_total'] = "Nettobetrag"; //'Tax';

                                        if(isset($total_tax[$tax_position_count]["value_total"]))
                                            $total_tax[$tax_position_count]["value_total"] += $tax_percents_total[$tax_percent];
                                        else $total_tax[$tax_position_count]["value_total"] = $tax_percents_total[$tax_percent];

                                        $tax_position_count++;
                                    }
                                }
                            }
                            if (isset($totals[$subtotal_order[2]]['value'])) {
                                if ($totals[$subtotal_order[2]]['value'] < 0.01) {
                                    if (($this->_getConfig('show_zero_tax_value', 0, false, $wonder, $store_id) == 0))
                                        $totals[$subtotal_order[2]] = null; // don't show tax if value is 0
                                }
                            } else $totals[$subtotal_order[2]] = null;
                            /**
                             * refunds
                             */
                            $total_refunded = $order->getTotalRefunded();
                            if ($total_refunded > 0) {
                                $totals[95]['key'] = 'refunds'; // put in same order as discounts
                                $totals[95]['text'] = $helper->__('Refunds');
                                $totals[95]['value'] = ($total_refunded * -1);
                            }

                            /**
                             * discounts
                             */
                            if (($this->_getConfig('show_zero_discount_value', 0, false, $wonder, $store_id) == 1)) {
                                if (($discount_line_or_subtotal == 'subtotal')) {
                                    $totals[$subtotal_order[1]]['key'] = 'discount';
                                    $totals[$subtotal_order[1]]['text'] = $helper->__('Discount') . '~~discount~~' . $order->getDiscountDescription();
                                    $totals[$subtotal_order[1]]['value'] = $order->getDiscountAmount();
                                }
                            } else
                                if (($order->getDiscountAmount() < 0) && ($discount_line_or_subtotal == 'subtotal')) {
                                    $totals[$subtotal_order[1]]['key'] = 'discount';
                                    $totals[$subtotal_order[1]]['text'] = $helper->__('Discount') . '~~discount~~' . $order->getDiscountDescription();
                                    $totals[$subtotal_order[1]]['value'] = $order->getDiscountAmount();
                                }
                    
                            /**
                             * grand total
                             */
                            $totals[100]['key'] = 'grand_total';
                            $totals[100]['text'] = $helper->__('Grand Total');

                            if ($filter_items_by_status == 1)
                                $grand_total_value = $order->getData('total_invoiced');
                            else
                                if ($filter_items_by_status == 2)
                                    $grand_total_value = $order->getData('total_invoiced');
                                else
                                    $grand_total_value = $order->getData('grand_total');
                            if ($totals_xtra['surcharge'] > 0) {
                                $grand_total_value = $totals[$subtotal_order[0]]['value'] + $totals[$subtotal_order[3]]['value'] + $totals[$surcharge_position]['value']; //grand total = subtotal + shipping + surcharge.            
                            } else
                                $grand_total_value = $totals[$subtotal_order[0]]['value'] + $totals[$subtotal_order[3]]['value']; //grand total = subtotal + shipping + surcharge.            
                            if ($tax_yn == 'yessubtotal' && isset($totals[$subtotal_order[2]]['value']))
                                $grand_total_value = $grand_total_value + $totals[$subtotal_order[2]]['value'];
                            if ($discount_line_or_subtotal == 'subtotal' && isset($totals[$subtotal_order[1]]))
                                $grand_total_value = $grand_total_value + $totals[$subtotal_order[1]]['value'];
                            if (isset($totals[1000]['value']))
                                $grand_total_value +=$totals[1000]['value'];
                    
                            /**
                             * Multi Fee
                             */
                    
                            $details_multifees = array();                            
                            
                            if(Mage::helper('pickpack')->isInstalled('MageWorx_MultiFees') && $order->getData('multifees_amount') > 0){
                                $details_multifees = unserialize($order->getData("details_multifees"));
                                foreach ($details_multifees as $key => $fee) {
                                    $totals[50 + $key]['key'] = 'multifees';
                                    $totals[50 + $key]['text'] = Mage::helper('multifees')->__($fee["title"]);
                                    $totals[50 + $key]['value'] = $fee["price"];
                                    $grand_total_value = $grand_total_value + $totals[50 + $key]['value'];
                                }
                        
                            }
                            if(Mage::helper('pickpack')->isInstalled("MageWorx_CustomerCredit") && $order->getData("customer_credit_amount") > 0){
                                $totals[70]['key'] = 'credit';
                                $totals[70]['text'] = Mage::helper('pickpack')->__('Internal Credit');
                                $totals[70]['value'] = (-1) * $order->getData("customer_credit_amount");
                                $grand_total_value = $grand_total_value + $totals[70]['value'];

                            }
                            /* check for Phoenix Cash On Delivery */
                            if(Mage::helper('pickpack')->isInstalled('Phoenix_CashOnDelivery') && $order->getCodFee() > 0){
                                $totals[80]['key'] = 'cod';
                                $totals[80]['text'] = Mage::helper('pickpack')->__('Cash On Delivery');
                                $totals[80]['value'] =  $order->getCodFee();
                                $grand_total_value = $grand_total_value + $totals[80]['value'];                            
                            }                                 
//Spent Points: $order->getRewardpointsSpent()
//Earned Points: $order->getRewardpointsEarn()
//$this->__('Point Discount'): -$order->getRewardpointsDiscount()
                            if(Mage::helper('pickpack')->isInstalled("Magestore_RewardPoints")){
                                $magestore_points = array();
                                $magestore_points []='show_rewardpoint_spent_yn';
                                $magestore_points []='show_rewardpoint_earn_yn';
                                $magestore_points []='show_rewardpoint_discount_yn';    
                                $temp_index = 90;               
                                $temp_key = '';
                                $temp_text = '';
                                $temp_value = 0;            
                                foreach($magestore_points as $reward_point_key)
                                {
                                    if($this->_getConfig($reward_point_key, 0, false, $wonder, $store_id) == 1)
                                    {
                                        if($reward_point_key == 'show_rewardpoint_discount_yn')
                                        {
                                            $temp_key = 'pointdiscount';
                                            $temp_text = Mage::helper('pickpack')->__('Point Discount');
                                            $temp_value = (-1) * $order->getRewardpointsDiscount();
                                            $grand_total_value +=$temp_value;
                                        }
                                        else
                                            if($reward_point_key == 'show_rewardpoint_spent_yn')
                                            {
                                                $temp_key = 'spent_points';
                                                $temp_text = Mage::helper('pickpack')->__('Spent Points');
                                                $temp_value = $order->getRewardpointsSpent();
                                            }
                                            else
                                            {
                                                $temp_key = 'earned_points';
                                                $temp_text = Mage::helper('pickpack')->__('Earned Points');
                                                $temp_value = $order->getRewardpointsEarn();
                                            }
                                            
                                        $totals[$temp_index]['key'] = $temp_key;
                                        $totals[$temp_index]['text'] = $temp_text;
                                        $totals[$temp_index]['value'] = $temp_value;
                                        $temp_index += 2; 
                                    }
                                }
                                unset($temp_index);

                            }
                            
                            $use_default_magento_grand_total = $this->_getConfig('use_default_magento_grand_total', 0, false, $wonder, $store_id);
                            if($use_default_magento_grand_total == 1)
                            {
                                    $grand_total_value = $order->getData('grand_total');
                            }
                            $totals[100]['value'] = $grand_total_value - $total_refunded; //($order->getGrandTotal() - $total_refunded);

                            /**
                             * total paid / due
                             */
                            if ($total_paid_yn_subtotal == 1) {
                                $totals[200]['key'] = 'total_paid';
                                $totals[200]['text'] = $helper->__('Total Paid');
                                $totals[200]['value'] = $order->getTotalPaid();
                            }
                            if ($total_due_yn_subtotal == 1) {
                                $totals[210]['key'] = 'total_due';
                                $totals[210]['text'] = $helper->__('Total Due');
                                $totals[210]['value'] = $order->getTotalDue();
                            }

                            ksort($totals);

                            // Subtotal[0]   Discounts[1]    Tax [2]    Shipping[3]
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);

                            $subtotal_count = 0;
                            $paid_or_due_shown = 0;

                            $subtotal_align = $this->_getConfig('subtotal_align', 1, false, $wonder, $store_id);
                            $subtotal_align_pos = explode(',', $this->_getConfig('subtotal_align_xpos', '410,460', false, $wonder, $store_id));
                            if ($fix_subtotal == 1) {
                                if (isset($bottom_image_y2) && $bottom_image_y2 > 0)
                                    $this->y = $bottom_image_y2 + (count($totals)) * (1.5 * $this->_general['font_size_body']);
                                if ($bottom_shipping_address_yn == 1 && isset($bottom_shipping_address_pos['y']))
                                    $this->y = $addressFooterXY[1] + (count($totals)) * ($this->_general['font_size_body'] + $this->_general['font_size_body']);
                            }

                            /**CHECK NEED TO CREATE NEW PAGE BEFORE PRINTING TOTALS OR NOT**/
                            // new logic for custom image after product list and total showing
                            if ($this->y < ($min_product_y + ($this->_general['font_size_body'] + 2) * (count($totals) + $zero_rate_yn + $vat_rateable_yn) + $packedByXY[1])) {
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                else $this->y = $page_top - $this->_general['font_size_body'];
                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                $font_temp = $this->getFontName2($this->_general['font_family_subtitles'], $this->_general['font_style_subtitles'], $this->_general['non_standard_characters']);
                                $paging_text_width = $this->parseString($paging_text, $font_temp, $this->_general['font_size_subtitles'] - 2);
                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));
                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');

                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2) - 5);
                                if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                    $page->setFillColor($background_color_subtitles_zend);
                                    $page->setLineColor($background_color_subtitles_zend);
                                    $page->setLineWidth(0.5);
                                    $page->drawLine($padded_left, ($this->y), $padded_right, ($this->y));
                                }
                            } else {
                                /***PRINTING draw line before totals***/
                                if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                    $page->setFillColor($background_color_subtitles_zend);
                                    $page->setLineColor($background_color_subtitles_zend);
                                    $page->setLineWidth(0.5);
                                    $page->drawLine($padded_left, ($this->y), $padded_right, ($this->y));
                                }
                            }
                            //End new logic for custom image after product list and total showing

                            if ($background_color_subtitles != '#FFFFFF') $this->y -= 20;
                            else $this->y -= 10;
                            // take account of extra tax line in subtotal
                            if ($prices_yn == 1 && ($tax_yn == 'yessubtotal' || $tax_yn == 'yesboth'))
                                $min_product_y += ($this->_general['font_size_subtitles'] + 2);
                            // Fix for subtotal padding top.
                            // Fix for subtotal padding top.
                            $this->y += $this->_general['font_size_body'] * 0.75;

                            if ($this->_getConfig('subtotal_price_xpos_options', 1, false, $wonder, $store_id) == 2)
                                $subtotal_price_xpos = $this->_getConfig('subtotal_price_xpos', 1, false, $wonder, $store_id);
                            else {
                                if ($priceX > $full_page_width && $priceEachX > $full_page_width / 2)
                                    $subtotal_price_xpos = $priceEachX;
                                else

                                    $subtotal_price_xpos = $priceX;
                            }
                           
                           //$storeSymbolCode = Mage::app()->getLocale()->currency($order->getStore()->getOrderCurrencyCode())->getSymbol();
                           $orderSymbolCode = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();
                           $order_currency_code = $order->getOrderCurrencyCode();
                           $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
                           $storeSymbolCode = Mage::app()->getLocale()->currency($store_currency_code)->getSymbol(); 
                                   
                            $show_base_currency_value = $this->_getConfig('show_base_currency_value', 0, false, $wonder, $store_id);
                            $show_currency_exchange_rate = $this->_getConfig('show_currency_exchange_rate', 0, false, $wonder, $store_id);
                            

                            /****PRINTING TOTALS***/
                            foreach ($totals as $key => $value) {
                                if(strlen($key) ==0)
                                continue;
                                $is_coupon = false;
                                if ($value > 0) {
                                    // don't show zero value shipping
                                    if (($this->_getConfig('show_zero_shipping_fee', 0, false, $wonder, $store_id) == 0) && ($totals[$key]['key'] == 'shipping_base') && ($totals[$key]['value'] == '0.0000')) {
                                        continue;
                                    }

                                    if (($totals[$key]['key'] != 'grand_total') && ($totals[$key]['key'] != 'total_paid') && ($totals[$key]['key'] != 'total_due')) {

                                        $tax_incl_left_bkt = '';
                                        $tax_incl_rt_bkt = '';

                                        if (($totals[$key]['key'] == 'taxamount'  && ($tax_yn == 'yesboth' || $tax_yn == 'noboth') && $show_bracket_tax == 1) 
                                        || ($totals[$key]['key'] == 'taxamount_percent'))
                                        {
                                            $tax_incl_left_bkt = '(';
                                            $tax_incl_rt_bkt = ')';
                                        }

                                        // webtex giftcard code
                                        if(isset($gift_card_array['width']) && isset($gift_card_array['code']))
                                            if (($totals[$key]['key'] == 'discount') && ($gift_card_array['width'] > 0) && ($gift_card_array['code'] != '')) {
                                                $page->drawText($gift_card_array['code'], ($priceTextX - 20 - $gift_card_array['width']), $this->y, 'UTF-8');
                                            }

                                        $coupon_label = '';
                                        $coupon_text = '';
                                        $subtotal_label_rightalign_xpos = 0;
                                        $subtotal_label_xpos = 0;

                                        $coupon_text = trim($totals[$key]['text']);
                                        if (strpos($coupon_text, '~~discount~~') !== false) {
                                            $is_coupon = true;
                                            $coupon_label_array = array();
                                            preg_match('/^(.*)~~discount~~(.*)$/ui', $coupon_text, $coupon_label_array);
                                            $coupon_label = $coupon_label_array[2];
                                            $coupon_text = $coupon_label_array[1];
                                        }

                                        if ($subtotal_align == 1) {
                                            $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                            $subtotal_label_xpos = $this->rightAlign2($coupon_text, $this->_general['font_family_body'], $this->_general['font_size_body'], 'regular', $subtotal_label_rightalign_xpos);
                                        } else $subtotal_label_xpos = $subtotal_align_pos[0];
                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        $print_coupon_text = ucwords(Mage::helper('sales')->__($coupon_text));
                                        if(strlen(trim($print_coupon_text)) == 0)
                                            $print_coupon_text = ucwords($coupon_text);
                                        $page->drawText($tax_incl_left_bkt .$print_coupon_text , $subtotal_label_xpos, $this->y, 'UTF-8');

                                        $count_coupon_line = 1;
                                        $max_chars_coupon = 55;

                                        if ((($is_coupon === true) && (strlen($coupon_label) > 3))) {
                                            $maxWidthCoupon = ($subtotal_label_rightalign_xpos - $padded_left);
                                            $coupon_array = array();
                                            $subtotal_couponlabel_xpos = 0;
                                            $coupon_array = wordwrap($coupon_label, $max_chars_coupon, "\n", false);

                                            $token = strtok($coupon_array, "\n");
                                            $y_text_coupon = ($this->y - ($this->_general['font_size_body'] * 1.5));

                                            $this->_setFont($page, 'regular', ($this->_general['font_size_body'] - 2), $this->_general['font_family_body'], $this->_general['non_standard_characters'], '#777777');

                                            while ($token != false) {
                                                $count_coupon_line++;
                                                if ($subtotal_align == 1) {
                                                    $subtotal_couponlabel_xpos = $this->rightAlign2(trim($token), $this->_general['font_family_body'], $this->_general['font_size_body'] - 2, 'regular', $subtotal_label_rightalign_xpos);
                                                } else {
                                                    $subtotal_couponlabel_xpos = $subtotal_align_pos[0];
                                                }

                                                $page->drawText($token, $subtotal_couponlabel_xpos, $y_text_coupon, 'UTF-8');
                                                $y_text_coupon -= ($page->getFontSize() * 1.4);
                                                $token = strtok("\n");
                                            }

                                            unset($coupon_array);
                                        } 
                                        else {
                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            if (strlen($coupon_label) > 3) $coupon_text = $coupon_text . ' (' . $coupon_label . ')';
                                            $print_coupon_text =ucwords(Mage::helper('sales')->__($coupon_text));
                                            if(strlen(trim($print_coupon_text)) == 0)
                                                $print_coupon_text = $coupon_text;
                                            $page->drawText($tax_incl_left_bkt . $print_coupon_text, $subtotal_label_xpos, $this->y, 'UTF-8');
                                            $count_coupon_line = 1;
                                        }

                                        $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        if($totals[$key]['key'] == 'credit')
                                            $page->drawText($this->formatPriceTxt($order, number_format($totals[$key]['value'], 2, '.', ',')) . $tax_incl_rt_bkt, $subtotal_price_xpos - 2, $this->y, 'UTF-8');
                                        else
                                            if($totals[$key]['key'] == 'spent_points' || $totals[$key]['key'] == 'earned_points')
                                                $page->drawText($totals[$key]['value'] . $tax_incl_rt_bkt, $subtotal_price_xpos, $this->y, 'UTF-8');
                                            else                                                
                                            $page->drawText($this->formatPriceTxt($order, number_format($totals[$key]['value'], 2, '.', ',')) . $tax_incl_rt_bkt, $subtotal_price_xpos, $this->y, 'UTF-8');
                                        if (($order_currency_code != $store_currency_code) && ($show_base_currency_value == 1)) {
                                            $this->y -= $this->_general['font_size_body'];
                                            $convert_to_store_currency = round($this->convertCurrency(number_format($totals[$key]['value'], 2, '.', ','), $order_currency_code, $store_currency_code), 2);
                                            $convert_to_store_currency_text = '[' . $storeSymbolCode . $convert_to_store_currency . ']';
                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($convert_to_store_currency_text, $subtotal_price_xpos, $this->y, 'UTF-8');
                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }
                                        /*draw vat_rateable && zero*/
                                        if (isset($totals[$key]) && $totals[$key]['key'] == 'sub_total' && !isset($totals[$subtotal_order[1]])) {
                                            if ($vat_rateable_yn == 1) {
                                                $vate_rateable_title = '(' . $vat_rateable_title . ')';
                                                if ($subtotal_align == 1) {
                                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                    $subtotal_label_xpos = $this->rightAlign2($vate_rateable_title, $this->_general['font_family_body'], $this->_general['font_size_body'], 'regular', $subtotal_label_rightalign_xpos);
                                                } else $subtotal_label_xpos = $subtotal_align_pos[0];
                                                $this->y -= 15;
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($vate_rateable_title, $subtotal_label_xpos, $this->y, 'UTF-8');
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($this->formatPriceTxt($order, number_format($vat_rateable_value, 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');

                                            }
                                            if ($zero_rate_yn == 1) {
                                                $zero_rate_title = '(' . $zero_rate_title . ')';
                                                if ($subtotal_align == 1) {
                                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                    $subtotal_label_xpos = $this->rightAlign2($zero_rate_title, $this->_general['font_family_body'], $this->_general['font_size_body'], 'regular', $subtotal_label_rightalign_xpos);
                                                } else $subtotal_label_xpos = $subtotal_align_pos[0];
                                                $this->y -= 15;
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($zero_rate_title, $subtotal_label_xpos, $this->y, 'UTF-8');
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($this->formatPriceTxt($order, number_format($zero_rate_value, 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');

                                            }
                                        } 
                                        elseif ($totals[$key]['key'] == 'discount') {
                                            $this->y -= 15 * $count_coupon_line;
                                            if ($vat_rateable_yn == 1) {
                                                $vate_rateable_title = '(' . $vat_rateable_title . ')';
                                                if ($subtotal_align == 1) {
                                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                    $subtotal_label_xpos = $this->rightAlign2($vate_rateable_title, $this->_general['font_family_body'], $this->_general['font_size_body'], 'regular', $subtotal_label_rightalign_xpos);
                                                } else $subtotal_label_xpos = $subtotal_align_pos[0];

                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($vate_rateable_title, $subtotal_label_xpos, $this->y, 'UTF-8');
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($this->formatPriceTxt($order, number_format($vat_rateable_value, 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                                $this->y -= 15;
                                            }
                                            if ($zero_rate_yn == 1) {
                                                $zero_rate_title = '(' . $zero_rate_title . ')';
                                                if ($subtotal_align == 1) {
                                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                    $subtotal_label_xpos = $this->rightAlign2($zero_rate_title, $this->_general['font_family_body'], $this->_general['font_size_body'], 'regular', $subtotal_label_rightalign_xpos);
                                                } else $subtotal_label_xpos = $subtotal_align_pos[0];

                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($zero_rate_title, $subtotal_label_xpos, $this->y, 'UTF-8');
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($this->formatPriceTxt($order, number_format($zero_rate_value, 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');

                                            }
                                            if ($zero_rate_yn == 0 && $vat_rateable_yn == 0)
                                                $this->y += 15 * $count_coupon_line;
                                            else
                                                $this->y += 15 * ($count_coupon_line - 1);
                                        }
                                        //Need to re-calculate this->y. If print in multiline coupon code.
                                        $this->y -= 1.5 * $this->_general['font_size_body'] * $count_coupon_line;
                                    }
                                    // elseif ($totals[$key]['key'] == 'multifees' || $totals[$key]['key'] == 'credit') {
                                    //     $this->y -= ($this->_general['font_size_body']);
                                    //     $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                    //     $subtotal_label_xpos = $this->rightAlign(strlen($totals[$key]['text']), Zend_Pdf_Font::FONT_HELVETICA_BOLD, $page->getFontSize(), 11, $subtotal_label_rightalign_xpos);
                                    //     $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                    //     $page->drawText($this->formatPriceTxt($order,number_format($totals[$key]['value'], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                    // } 
                                    elseif ($totals[$key]['key'] == 'grand_total') {                                        
                                        if ($address_pad[1] > 0) 
                                        {
                                            $this->y += ($this->_general['font_size_subtitles'] - ($this->_general['font_size_subtitles'] / 2));
                                        }
                                        else 
                                        {
                                            $this->y += ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 3));
                                        }
                                        

                                        
                                        if ($fix_subtotal == 0) {
                                            $page->drawLine(($priceTextX - ($this->_general['font_size_body'] * 2)), ($this->y), $padded_right, ($this->y));
                                            $this->y -= (20 - ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4)));
                                            $this->y -= ($this->_general['font_size_body']) * 0.2;
                                        } else {
                                            $this->y -= $this->_general['font_size_body'];
                                        }

                                        $this->_setFont($page, 'bold', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        if ($title_invert_color == 1) {
                                            if ($subtotal_align == 1) {
                                                $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                $subtotal_label_xpos = $this->rightAlign2($totals[$key]['text'], $this->_general['font_family_body'], $this->_general['font_size_body'], Zend_Pdf_Font::FONT_HELVETICA_BOLD, $subtotal_label_rightalign_xpos);
                                                $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                            } else {
                                                $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_align_pos[0], $this->y, 'UTF-8');
                                            }
                                        } else {
                                            if ($subtotal_align == 1) {
                                                $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                                $subtotal_label_xpos = $this->rightAlign2($totals[$key]['text'], $this->_general['font_family_body'], $this->_general['font_size_body'], Zend_Pdf_Font::FONT_HELVETICA_BOLD, $subtotal_label_rightalign_xpos);
                                                $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                            } else {
                                                $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_align_pos[0], $this->y, 'UTF-8');
                                            }
                                        }

                                        $page->drawText($this->formatPriceTxt($order,number_format($totals[$key]['value'], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                        if (($order_currency_code != $store_currency_code)) {
                                            if ($show_base_currency_value == 1) {
                                                $this->y -= $this->_general['font_size_body'];
                                                $convert_to_store_currency = round($this->convertCurrency(number_format($totals[$key]['value'], 2, '.', ','), $order_currency_code, $store_currency_code), 2);
                                                $convert_to_store_currency_text = '[' . $storeSymbolCode . $convert_to_store_currency . ']';
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $page->drawText($convert_to_store_currency_text, $subtotal_price_xpos, $this->y, 'UTF-8');
                                            }
                                            if ($show_currency_exchange_rate == 1) {
                                                $this->y -= $this->_general['font_size_body'];
                                                $this->_setFont($page, 'regular', $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                                $convert_rate = round($this->convertCurrency(1, $order_currency_code, $store_currency_code), 2);
                                                $convert_text = '[1 ' . $order_currency_code . ' = ' . $convert_rate . ' ' . $store_currency_code . ']';
                                                if ($subtotal_align == 1) {
                                                    $convert_rate_rightalign_xpos = $subtotal_align_pos[1];
                                                    $convert_rate_xpos = $this->rightAlign(strlen($convert_text), Zend_Pdf_Font::FONT_HELVETICA_BOLD, $page->getFontSize(), 11, $convert_rate_rightalign_xpos);
                                                    $page->drawText(Mage::helper('sales')->__($convert_text), $convert_rate_xpos + 5, $this->y + 10, 'UTF-8');
                                                } else {
                                                    $page->drawText(Mage::helper('sales')->__($convert_text), $subtotal_align_pos[0] + 5, $this->y + 10, 'UTF-8');
                                                }
                                            }
                                            //$page->drawText($convert_text,$subtotal_price_xpos, $this->y , 'UTF-8');

                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }
                                        $this->y += ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4));
                                    } 
                                    else {
                                        // paid/due
                                        if ($paid_or_due_shown == 0) {
                                            if ($address_pad[1] > 0) $this->y += ($this->_general['font_size_subtitles'] - ($this->_general['font_size_subtitles'] / 4));
                                            else $this->y -= 15;
                                            if ($fix_subtotal == 0) {

                                                $page->drawLine(($priceTextX - ($this->_general['font_size_body'] * 2)), ($this->y), $padded_right, ($this->y));
                                                if ($background_color_subtitles != '#FFFFFF') $this->y -= (20 - ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4)));
                                                else $this->y -= (10 - ($this->_general['font_size_body'] - ($this->_general['font_size_body'] / 4)));
                                            } else {
                                                $this->y -= 5;
                                            }
                                            $paid_or_due_shown = 1;
                                        }


                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        if ($subtotal_align == 1) {
                                            $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                            $subtotal_label_xpos = $this->rightAlign2($totals[$key]['text'], Zend_Pdf_Font::FONT_HELVETICA, $page->getFontSize(), 12, $subtotal_label_rightalign_xpos);
                                        } else $subtotal_label_xpos = $subtotal_align_pos[0];
                                        if ($title_invert_color == 1) {
                                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] + 1), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], '#FFFFFF');
                                            $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                            $this->_setFont($page, 'bold', ($this->_general['font_size_subtitles'] + 1), $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        } else $page->drawText(Mage::helper('sales')->__($totals[$key]['text']), $subtotal_label_xpos, $this->y, 'UTF-8');
                                        $page->drawText($this->formatPriceTxt($order,number_format($totals[$key]['value'], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                        if (($order_currency_code != $store_currency_code) && ($show_base_currency_value == 1)) {
                                            $this->y -= $this->_general['font_size_body'];
                                            $convert_to_store_currency = round($this->convertCurrency(number_format($totals[$key]['value'], 2, '.', ','), $order_currency_code, $store_currency_code), 2);
                                            $convert_to_store_currency_text = '[' . $storeSymbolCode . $convert_to_store_currency . ']';
                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                            $page->drawText($convert_to_store_currency_text, $subtotal_price_xpos, $this->y, 'UTF-8');
                                            $this->_setFont($page, 'regular', $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                        }
                                        $this->y -= 15;
                                    }
                                    $subtotal_count++;
                                }
                            }
                        }
                        // show tax class in subtotal
                        if($list_total_by_tax_class == 1){
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            $this->y -= $this->_general['font_size_body'];
                            $page->drawLine(($priceTextX - ($this->_general['font_size_body'] * 2)), ($this->y), $padded_right, ($this->y));
                            $this->y -= 3;
                            $page->drawLine(($priceTextX - ($this->_general['font_size_body'] * 2)), ($this->y), $padded_right, ($this->y));
                            $this->y -= $this->_general['font_size_body'];
                            foreach ($total_tax as $key => $each_tax_class) {
                                if ($subtotal_align == 1) {
                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                    $subtotal_label_xpos = $this->rightAlign2($each_tax_class["text_value_total"], $this->_general['font_family_body'], $this->_general['font_size_body'], Zend_Pdf_Font::FONT_HELVETICA_BOLD, $subtotal_label_rightalign_xpos);
                                    $page->drawText(Mage::helper('sales')->__($each_tax_class["text_value_total"]), $subtotal_label_xpos, $this->y, 'UTF-8');
                                } else {
                                    $page->drawText(Mage::helper('sales')->__($each_tax_class["text_value_total"]), $subtotal_align_pos[0], $this->y, 'UTF-8');
                                }
                                $page->drawText($this->formatPriceTxt($order,number_format($each_tax_class["value_total"], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                $this->y -= ($this->_general['font_size_body'] + 2);

                                if ($subtotal_align == 1) {
                                    $subtotal_label_rightalign_xpos = $subtotal_align_pos[1];
                                    $subtotal_label_xpos = $this->rightAlign2($each_tax_class["text"], $this->_general['font_family_body'], $this->_general['font_size_body'], Zend_Pdf_Font::FONT_HELVETICA_BOLD, $subtotal_label_rightalign_xpos);
                                    $page->drawText(Mage::helper('sales')->__($each_tax_class["text"]), $subtotal_label_xpos, $this->y, 'UTF-8');
                                } else {
                                    $page->drawText(Mage::helper('sales')->__($each_tax_class["text"]), $subtotal_align_pos[0], $this->y, 'UTF-8');
                                }
                                $page->drawText($this->formatPriceTxt($order,number_format($each_tax_class["value"], 2, '.', ',')), $subtotal_price_xpos, $this->y, 'UTF-8');
                                $this->y -= 1.5*$this->_general['font_size_body'];
                            }
                    
                        }
                        if ($paid_or_due_shown == 1) $this->y += 15;

                        /******************************************FULL PAYMENT**************************************************/
                        if($pickpack_show_full_payment_yn == 1){
                            if(isset($full_payment) && $full_payment != ''){
                                $full_payment_arr = explode('{{pdf_row_separator}}', $full_payment);
                                $pickpack_show_full_payment_nudge = explode(",", $this->_getConfig('pickpack_show_full_payment_nudge', '0,0', true, $wonder, $store_id));
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'] - 2, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                
                                foreach ($full_payment_arr as $key => $payment_message) {
                                    $payment_message = trim($payment_message);
                                    $payment_message = trim(strip_tags(str_replace(array('<br/>', '<br />', '<span>', '</span>'), ' ', $payment_message)));
                                    if($payment_message != ''){
                                        $maxWidthPage = $padded_right - $pickpack_show_full_payment_nudge[0];
                                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $line_width = $this->parseString($payment_message, $font_temp, $this->_general['font_size_body'] - 2); // bigger = left
                                        $char_width = $line_width / strlen($payment_message);
                                        $max_chars = round($maxWidthPage / $char_width);
                                        if(strlen($payment_message) > $max_chars){
                                            $message_arr = explode("\n", wordwrap($payment_message, $max_chars, "\n"));
                                            foreach ($message_arr as $key => $value) {
                                               $page->drawText($value, $pickpack_show_full_payment_nudge[0], $pickpack_show_full_payment_nudge[1], 'UTF-8');
                                               $pickpack_show_full_payment_nudge[1] -= ($this->_general['font_size_body'] + 1);
                                            }
                                        }
                                        else{
                                            $page->drawText($payment_message, $pickpack_show_full_payment_nudge[0], $pickpack_show_full_payment_nudge[1], 'UTF-8');
                                            $pickpack_show_full_payment_nudge[1] -= ($this->_general['font_size_body'] + 1);
                                        }
                                    }
                                }
                            }
                        }
                
                }
                    
                   $this->y -=20;


                    /**********************************GIFT MESSAGE, CUSTOM MESSAGE AND NOTES***********************************/
                    // custom message image, if 'under products = yes'
                    $packlogo_filename = null;
                    $packlogo_path = null;
                    $bottom_image_width = null;
                    $bottom_image_height = null;
                    $character_breakpoint = 50;
                    $test_name = 'abcdefghij';
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $test_name_length = round($this->parseString($test_name, $font_temp, ($this->_general['font_size_body'])));
                    $pt_per_char = ($test_name_length / 11);
                    $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $store_id);
                    $message_character_breakpoint = round(($positional_message_box_fixed_position_demension_x / $pt_per_char));
                    $msg_line_count = 5;
                    /*************************** PRINTING CUSTOM MESSAGE *******************************/
                    $packlogo_filename = null;
                    $packlogo_path = null;
                    $bottom_image_width = null;
                    $bottom_image_height = null;
                    $customer_group = ucwords(strtolower(Mage::getModel('customer/group')->load((int)$order->getCustomerGroupId())->getCode()));
                    if ($message_yn == 'yes2') {
                        if (strpos(strtolower($message_filter), strtolower($customer_group)) !== false)
                            $message = $messageB;
                    }
                    /*************************** PRINTING CUSTOM MESSAGE (Image) *******************************/
                    if (($message_yn == 'yesimage')) {
                        $this->y -= 40;

                        // 2250 x 417  (540 x 100)
                        // Dimensions 540pt(A4)|562pt(US Letter) x 100pt @ 300dpi : non-interlaced .png
//                         if ($invoice_or_pack == 'invoice') 
                        $packlogo_filename = $this->_getConfig('custom_message_image', null, false, $wonder, $store_id);
//                         else $packlogo_filename = $this->_getConfig('custom_message_image_pack', null, false, $wonder, $store_id);

                        if ($packlogo_filename) {
                           //  $sub_folder = 'message_pack';
//                             $option_group = 'wonder';
// 
//                             if ($wonder != 'wonder') {
                                $sub_folder = 'message_invoice';
                                $option_group = 'wonder_invoice';
//                             }
                            if ($packlogo_filename) {
                                $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                                $dirImg = $packlogo_path;
                                $imageObj = new Varien_Image($dirImg);
                                $bottom_image_width = $imageObj->getOriginalWidth();
                                $bottom_image_height = $imageObj->getOriginalHeight();
                            }
                            $image_ext = '';
                            $image_ext = substr($packlogo_path, strrpos($packlogo_path, '.') + 1);
                            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($packlogo_path))) {
                                if ($this->_packingsheet['page_size'] == "letter")
                                    $logo_maxdimensions2 = explode(',', '612,41');
                                elseif ($this->_packingsheet['page_size'] == "a4")
                                    $logo_maxdimensions2 = explode(',', '595,41');
                                else
                                    $logo_maxdimensions2 = explode(',', '556,41');
                                try {
                                    if ($bottom_image_width > $logo_maxdimensions2[0]) {
                                        $bottom_img_height = ceil(($logo_maxdimensions2[0] / $bottom_image_width) * $bottom_image_height);
                                        $bottom_img_width = $logo_maxdimensions2[0];
                                    } //Fix for auto height --> Need it?
                                    else
                                        if ($bottom_image_height > $logo_maxdimensions2[1]) {
                                            $temp_var = $logo_maxdimensions2[1] / $bottom_image_height;
                                            $bottom_img_height = $logo_maxdimensions2[1];
                                            $bottom_img_width = $temp_var * $bottom_image_width;
                                        }
                                    if ($this->y < (20 + $bottom_img_height)) {
                                        $page = $this->nooPage($this->_packingsheet['page_size']);
                                        $page_count++;
                                        $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                        if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                        else $this->y = $page_top;

                                        $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                        $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                        $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                        $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                        $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                        $items_y_start = $this->y;
                                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                    }
                                    $bottom_image_x1 = 0;
                                    $bottom_image_x2 = $bottom_img_width;
                                    $bottom_image_y1 = 0;
                                    $bottom_image_y2 = $bottom_img_height;
                                    $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                                    if ($custom_message_image_locked_yn == 1) {
                                        $bottom_image_y2 = (10 + $bottom_img_height);
                                        $bottom_image_y1 = 10;

                                        $bottom_image_x1 += $custom_message_image_nudge[0];
                                        $bottom_image_x2 += $custom_message_image_nudge[0];
                                        $bottom_image_y1 += $custom_message_image_nudge[1];
                                        $bottom_image_y2 += $custom_message_image_nudge[1];
                                        $pdf->pages[$start_page_for_order]->drawImage($packlogo, $bottom_image_x1, $bottom_image_y1, $bottom_image_x2, $bottom_image_y2);
                                    }
                                    else
                                        $page->drawImage($packlogo, $bottom_image_x1, $bottom_image_y1, $bottom_image_x2, $bottom_image_y2);
                                } catch (Exception $e) {
                                }
                            }
                        }
                    } else
                        /*************************** PRINTING CUSTOM MESSAGE (Text) *******************************/
                        if ($message != null) {
                            $line_count = 0;
                            $next_page_box = 0;
                            if ($message_yn == 'yesbox') {
                                if($this->y < $custom_message_position[1] - 10)
                                    $next_page_box = 1;
                                $this->y = $custom_message_position[1];

                                //$maxWidthPage = ($padded_right + 20 - $custom_message_position[0] - 20);
                                $maxWidthPage = ($positional_message_box_fixed_position_demension_x);
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($font_size_comments);
                                $line_width = $this->parseString($message, $font_temp, $font_size_compare); // bigger = left
                                $char_width = $line_width / strlen($message);
                                $max_chars = round($maxWidthPage / $char_width);

                                if (strlen($message) > $max_chars) {
                                    $chunks = explode("\n", wordwrap($message, $max_chars, "\n"));
                                } else $chunks = explode("\n", $message);
                                $line_count = count($chunks);

                                $custom_message_box_left = ($custom_message_position[0] - 7);
                                $custom_message_box_right = $custom_message_position[0] + $positional_message_box_fixed_position_demension_x + 15;

                            } else {
                                $custom_message_box_left = $padded_left;
                                $custom_message_box_right = $padded_right;
                                
                                // shift up message box
                                if (isset($has_shown_product_image) && $has_shown_product_image == 1) $this->y += ($img_height / 2);
                                $message_array = explode("\n", $message);
                                $line_count = count($message_array);
                                $this->y -= (($this->_general['font_size_subtitles'] - 4) / 2);
                            }
                            if ($this->y < (20 + ($line_count + 1) * ($font_size_comments + 2)) || $next_page_box == 1) {
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                else $this->y = $page_top;

                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                $items_y_start = $this->y;
                                //$this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
                            if (strtoupper($background_color_message) != '#FFFFFF' && $message_yn != 'yes' && $message_yn != 'yes2') {
                                $page->setFillColor($background_color_comments);
                                $page->setLineColor($background_color_comments);
                                $page->setLineWidth(0.5);
                                $page->drawRectangle($custom_message_box_left, ($this->y - ($line_count * ($font_size_comments + 2)) - 7), $custom_message_box_right, ($this->y + 11 - 10));
                            }

                            $this->_setFont($page, $font_style_comments, $font_size_comments, $font_family_comments, $this->_general['non_standard_characters'], $font_color_message);

                            if ($message_yn == 'yesbox') {
                                if (isset($chunks) && is_array($chunks)) {
                                    foreach ($chunks as $key => $chunk) {
                                        $chunk_display = '';
                                        if ($chunk != '') {
                                            $this->y -= ($font_size_comments +2);
                                            $page->drawText($chunk, ($custom_message_position[0]), $this->y, 'UTF-8');
                                        }
                                    }

                                    unset($chunks);
                                }
                            } elseif (($message_yn != 'yes') && ($message_yn != 'yes2')) {
                                foreach ($message_array as $value) {
                                    if ($this->_general['non_standard_characters'] == 0) {
                                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    } else {
                                        $font_temp = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                                    }
                                    $line_width = ceil($this->parseString($value, $font_temp, ($font_size_message * 0.96))); //*0.77)); // bigger = left

                                    $left_margin = ceil((($padded_right - $line_width) / 2));
                                    if ($left_margin < 0) $left_margin = 0;

                                    if ($line_width == 0) // some issue with non-standard fonts
                                    {
                                        $left_margin = 25;
                                    }

                                    if (isset($value) && isset($left_margin) && ($this->y > 9)) $page->drawText($value, $left_margin, $this->y, 'UTF-8');
                                    $this->y -= ($font_size_message + 2);
                                    if ($this->y < 10) $this->y = 10;
                                }
                            }
                        }

                    $line_count_message = 0;
                    if ($message_yn == 'yes' || $message_yn == 'yes2') {
                        $this->y -= ($font_size_message * 1.5);
                        if (($this->y) < $min_product_y) {
                            $page = $this->nooPage($this->_packingsheet['page_size']);
                            $page_count++;
                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                            if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                            else $this->y = $page_top;
                            $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                            $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                            $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                            $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                            $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 1.5));

                            $items_y_start = $this->y;
                            $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        }
                        if (!isset($custom_message_box_left)) $custom_message_box_left = $padded_left;
                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $max_chars = $this->getMaxCharMessage2($message, $padded_right, $font_size_message, $font_temp, $custom_message_box_left);
                        $message_array = explode("\n", wordwrap($message, $max_chars, "\n"));
                        if (!isset($line_count_message) || ($line_count_message == 0))
                            $line_count_message = count($message_array);
                        
						if ($background_color_gift_message_zend != '#FFFFFF') {
                                $page->setFillColor($background_color_gift_message_zend);
                                $page->setLineColor($background_color_gift_message_zend);
                                $page->setLineWidth(0.5);
                                if ($fill_background_color_comments_under_product == 0) {
	                                $page->drawLine($custom_message_box_left, ($this->y), $padded_right, ($this->y));
	                                $page->drawLine($custom_message_box_left,  ($this->y - ($line_count_message * ($font_size_message + 2)) - 10), $padded_right,  ($this->y - ($line_count_message * ($font_size_message + 2)) - 10));
	                                $page->drawLine($custom_message_box_left, ($this->y), $custom_message_box_left,  ($this->y - ($line_count_message * ($font_size_message + 2)) - 10));
	                                $page->drawLine($padded_right, ($this->y - ($line_count_message * ($font_size_message + 2)) - 10), $padded_right,  ($this->y));
                                }
                                else
                                   $page->drawRectangle($custom_message_box_left, ($this->y - ($line_count_message * ($font_size_message + 2)) - 10), $padded_right, ($this->y));   
                        }
						
                        $bottom_message_pos = ($this->y - ($line_count_message * ($font_size_message + 2)) - 10);
                        $this->_setFont($page, $font_style_message, $font_size_message, $font_family_message, $this->_general['non_standard_characters'], $font_color_message);
                        $this->y -= ($font_size_message * 1.25);
                        $left_margin = 0;
                        foreach ($message_array as $value) {
                            if ($this->_general['non_standard_characters'] == 0) {
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            } else {
                                $font_temp = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                            }
                            $line_width = ceil($this->parseString($value, $font_temp, ($font_size_message * 0.96))); //*0.77)); // bigger = left
                            $left_margin = 0;
                            if($custom_message_fixed == 0)
                                $left_margin = ceil((($padded_right - $line_width) / 2));
                            if ($left_margin < 0) $left_margin = 0;
                            if ($line_width == 0 || $custom_message_fixed== 1) // some issue with non-standard fonts
                            {
                                $left_margin = 25;
                            }
                            if (isset($value) && isset($left_margin) && ($this->y > 9)) $page->drawText($value, $left_margin, $this->y, 'UTF-8');
                            $this->y -= ($font_size_message + 2);
                            if ($this->y < 10) $this->y = 10;
                        }
                    }
                    $order_notes_was_set = false;


                    /***********PRINTING ORDER NOTES***********/
                    if (($notes_position != 'no') && ($notes_position != 'yesshipping')) {
                        $notesX = 0;
                        $orderNote = true;
                        if (strlen($customer_comments) > 0){
                            $orderComments[0] = array(
                                                    'comment' =>  $customer_comments,
                                                    'is_visible_on_front' => 1,
                                                    'created_at' => $order->getCreatedAt()
                                                );
                            $orderNote = false;
                        } 
                        if($orderNote){
                            if($order->getStatusHistoryCollection(true)){
                                $orderComments = $order->getStatusHistoryCollection(true);
                                
                            }
                        }
                        if ($orderComments) {
                            $note_line = array();
                            $note_comment_count = 0;
                            $line_count_note = 0;
                            $i = 0;
                            $comment_body = '';
                            $character_breakpoint = 50;
                            $test_name = 'abcdefghij'; //10
                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $test_name_length = round($this->parseString($test_name, $font_temp, ($font_size_comments)));
                            $pt_per_char = ($test_name_length / 10);
                            $max_name_length = $positional_message_box_fixed_position_demension_x;
                            $character_breakpoint = round(($max_name_length / $pt_per_char));

                            foreach ($orderComments as $comment) {
                                if($orderNote)
                                    $comment_body = trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $comment->getData('comment'))));
                                else
                                     $comment_body = '';
                                     //$comment_body = $comment['comment'];
                                
                                if(Mage::helper('pickpack')->isInstalled('Brainvire_OrderComment')){
                                    if($_item['is_customer_notified'] != 0)
                                        $_item['is_visible_on_front'] = 1;
                                }

                                if($orderNote){
                                    if ($notes_filter_options == 'yestext' && ($this->checkFilterNotes($comment_body, $notes_filter))) {
                                        $comment_body = '';
                                    } elseif (($notes_filter_options == 'yesfrontend') && ($comment['is_visible_on_front'] != 1)) {
                                        $comment_body = '';
                                    }
                                }

                                if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                                    $check_comments_for_gift_message_filter = $this->_getConfig('check_comments_for_gift_message_filter', 'Checkout Message', false, $wonder, $store_id);
                                    $pos = strpos($comment['comment'], 'M2E Pro Notes');
                                    $pos2 = strpos($comment['comment'], $check_comments_for_gift_message_filter);
                                    if (($pos !== false) && ($pos2 !== false)) {

                                        $start_pos1 = strlen('M2E Pro Notes') + 1;
                                        $start_pos2 = strlen('Checkout Message From Buyer:') + 1;
                                        $str_1 = trim(substr($comment_body, $start_pos1));
                                        $str_2 = trim(substr($str_1, $start_pos2));
                                        $gift_message_array['notes'][] = $str_2;
                                        //$comment_body = '';
                                    }
                                }

                                if ($comment_body == '') {
                                    continue;
                                }

                                $comment['created_at'] = date('m/d/y', strtotime($comment['created_at']));
                                if (trim($comment_body) != '') $comment_body = $comment['created_at'] . ' : ' . $comment_body;

                                if ($notes_position == 'yesbox') {
                                    $comment_body = wordwrap($comment_body, $message_character_breakpoint, "\n", false);
                                }

                                $note_line[$i]['date'] = $comment['created_at'];
                                $note_line[$i]['comment'] = $comment_body;

                                if ((($notes_filter_options == 'yesfrontend' && $comment['is_visible_on_front'] == 1) || $notes_filter_options == 'no' || (($notes_filter_options == 'yestext') && !preg_match('~' . $notes_filter . '~i', $comment_body))) && ($comment_body != '')) {
                                    if ($note_line[$i]['comment'] != '') $note_comment_count = 1;
                                } else $note_line[$i]['comment'] = '';
                                $comment_array = explode("\n", wordwrap($note_line[$i]['comment'], $character_breakpoint, "\n", false));
                                $line_count_note += count($comment_array);
                                $i++;
                            }

                            // for the bottom of gift message
                            $comments_y = $this->y;

                            if ($note_comment_count > 0) {
                                $character_breakpoint = 50;
                                $test_name = 'abcdefghij'; //10
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $test_name_length = round($this->parseString($test_name, $font_temp, ($this->_general['font_size_body'])));
                                $pt_per_char = ($test_name_length / 10);
                                $max_name_length = $padded_right;
                                $character_breakpoint = round(($max_name_length / $pt_per_char) - 25);

                                $this->y -= ($font_size_comments + 5);

                                if ($notes_position == 'yesbox') {
                                    $this->y = $positional_message_box_fixed_position[1];
                                    $notesX = $positional_message_box_fixed_position[0];
                                }
                                $temp_height = 1;
                                while (isset($note_line[$i]['date'])) {
                                    $note_line[$i]['comment'] = wordwrap(Mage::helper('pickpack/functions')->clean_method($note_line[$i]['comment'], 'pdf_more'), $character_breakpoint, "\n", false);
                                    // wordwrap characters
                                    $token = strtok($note_line[$i]['comment'], "\n");
                                    while ($token != false) {
                                        $temp_height *= 2 * $font_size_comments;
                                        $token = strtok("\n");
                                    }
                                }

                                if (($this->y - $temp_height) < 10) {
                                    $page = $this->nooPage($this->_packingsheet['page_size']);
                                    $page_count++;
                                    $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                    if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                    else $this->y = $page_top;

                                    $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                    $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                    $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                    $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                    $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));
                                    $flag_print_newpage = 1;
                                    $items_y_start = $this->y;
                                    $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                                } else
                                    $this->y -= $font_size_comments * 1.7;

                                $msgX = $positional_message_box_fixed_position[0];
                                $right_bg_gift_msg = $positional_message_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;

                                if (($background_color_comments_pre != '') && ($background_color_comments_pre != '#FFFFFF')) {
                                    $page->setFillColor($background_color_comments);
                                    $page->setLineColor($background_color_comments);
                                    $page->setLineWidth(0.5);
                                    if ($line_count_note < 3)
                                        $page->drawRectangle($msgX, ($this->y + $font_size_comments + 7), $right_bg_gift_msg, ($this->y - ($line_count_note * ($font_size_comments)) - 12));
                                    else
                                        $page->drawRectangle($msgX, ($this->y + $font_size_comments + 7), $right_bg_gift_msg, ($this->y - ($line_count_note * ($font_size_comments))));
                                }
                                $this->_setFont($page, 'bold', $font_size_comments, $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);
                                $page->drawText(Mage::helper('sales')->__($notes_title), ($msgX + 4), $this->y, 'UTF-8');
                                $this->y -= ($font_size_comments + 2);
                                //$this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);

                                sksort($note_line, 'date', true);
                                $i = 0;
                                $font_temp = $this->getFontName2($font_family_comments, 'regular', 0);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_comments); // bigger = left
                                $char_width = $line_width / 11;
                                $character_breakpoint = round(($right_bg_gift_msg - $msgX) / $char_width);
                                while (isset($note_line[$i]['date'])) {
                                    $note_line[$i]['comment'] = wordwrap(Mage::helper('pickpack/functions')->clean_method($note_line[$i]['comment'], 'pdf_more'), $character_breakpoint, "\n", false);
//                                  $note_line[$i]['comment'] = $this->createMsgArray2($note_line[$i]['comment'],$positional_message_box_fixed_position_demension_x,$font_size_comments,$font_family_comments);
                                    // wordwrap characters
                                    $token = strtok($note_line[$i]['comment'], "\n");
                                    while ($token != false) {
                                        if ($i == 0)
                                            $addon_height_comment = 2;
                                        else
                                            $addon_height_comment = 10;

                                        $this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_comments);
                                        $page->drawText(trim($token), ($msgX + 4), $this->y, 'UTF-8');

                                        $this->y -= $font_size_comments;
                                        $token = strtok("\n");
                                    }
                                    $order_notes_was_set = true;
                                    $i++;
                                }
                                // for the bottom of gift message
                                $comments_y = $this->y;
                            }
                            unset($note_line);
                            unset($orderComments);
                        }
                    }
                    $gift_msg_array = array();
                    /***********PRINTING QC MESSAGE***********/
                    if ($packed_by_yn == 1) {
                        $this->_setFont($page_temp_first, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $page_temp_first->drawText(Mage::helper('sales')->__($packed_by_text), $packedByXY[0], $packedByXY[1], 'UTF-8');
                    }
                    /***********PRINTING SUPPLIER ATTRIBUTE***********/
                    if ($supplier_attribute_yn == 1) {
                        $supplier_attribute_text = $supplier;
                        if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
                        {
                            if($supplier_attribute == 'warehouse')
                            {
                                if(isset($this->warehouse_title[$supplier]))
                                    $supplier_attribute_text = trim(strtoupper($this->warehouse_title[$supplier]));
                            }
                        }
                        $this->_setFont($page, $this->_general['font_style_body'], $font_size_supplier_attribute, $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $page->drawText(Mage::helper('sales')->__($supplier_attribute_text), $supplier_attributeXY[0], $supplier_attributeXY[1], 'UTF-8');
                    }
                    /***********PRINTING ORDER GIFT MESSAGE***********/
                    $msg_line_count = 0;
                    if (($gift_message_yn == 'yesunder' || $gift_message_yn == 'yesbox' || $gift_message_yn == 'yesnewpage')) {
                        $gift_sender = '';
                        $gift_recipient = '';
                        $gift_message = '';
                        if((!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL)){  
                            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $giftWrap_info, $gift_msg_array);
                            $gift_sender = $gift_msg_array[1];
                            $gift_recipient = $gift_msg_array[2];
                            $gift_message = $gift_msg_array[0];
                            
                            $to_from = '';
                            $to_from_from = '';
                            if (isset($gift_recipient) && $gift_recipient != '') {
                                if ($gift_message_yn != 'yesnewpage') $to_from .= 'Message to: ' . $gift_recipient;
                                else $to_from .= 'To ' . $gift_recipient;
                            }
                            if (isset($gift_sender) && $gift_sender != '') $to_from_from = 'From: ' . $gift_sender;
                        }
                        if (Mage::helper('pickpack')->isInstalled('Webtex_GiftRegistry')){
                            $customerId = $order->getData("customer_id");
                            
                            $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
                            $gift_registry_message = '';
                            if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
                                $gift_registry_message = 'This is a Gift Registry Order ' . '(' . $gift_registry["giftregistry_id"] . ')'  ;
                                $gift_message = $gift_message . $gift_registry_message;
                            }
                        }
                        if($gift_message != ''){
                            if ($gift_message_yn == 'yesbox') {
                                $this->y = $positional_message_box_fixed_position[1];
                                $msgX = $positional_message_box_fixed_position[0];
                                $gift_message = wordwrap($gift_message, $message_character_breakpoint, "\n", false);
                                $character_message_breakpoint = 66;
                                $background_color_temp = $background_color_comments;
                                $font_style_temp = $font_style_comments;
                                $font_family_temp = $font_family_comments;
                                $font_size_temp = $font_size_comments;
                                $font_color_temp = $font_color_comments;
                                $right_bg_gift_msg = $positional_message_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
                                $gift_msg_array = $this->createMsgArray2($gift_message, $positional_message_box_fixed_position_demension_x, $font_size_temp, $font_family_temp);
                            } elseif ($gift_message_yn == 'yesunder') {
                                $msgX = $padded_left;
                                $character_message_breakpoint = 96;
                                $gift_message = wordwrap($gift_message, 96, "\n", false);
                                $background_color_temp = $background_color_gift_message_zend;
                                $font_style_temp = $font_style_gift_message;
                                $font_family_temp = $font_family_gift_message;
                                $font_size_temp = $font_size_gift_message;
                                $font_color_temp = $font_color_gift_message;
                                $right_bg_gift_msg = $padded_right;
                                $gift_msg_array = $this->createMsgArray($gift_message);
                            } elseif ($gift_message_yn == 'yesnewpage') {
                                $msgX = $padded_left;
                                $gift_message = wordwrap($gift_message, 96, "\n", false);
                                $character_message_breakpoint = 96;
                                $background_color_temp = $background_color_comments;
                                $font_style_temp = $font_style_gift_message;
                                $font_family_temp = $font_family_gift_message;
                                $font_size_temp = $font_size_gift_message;
                                $font_color_temp = $font_color_gift_message;
                                $right_bg_gift_msg = $padded_right;
                                $gift_msg_array = $this->createMsgArray($gift_message);
                            }
                            $y_before_order_gift_message = $this->y;
                            if ($notes_position == 'yesbox' && $gift_message_yn == 'yesbox')
                                $this->y = $comments_y;
                            elseif ($message_yn == "yes")
                                $this->y = $bottom_message_pos - 15;
                            else
                                $this->y = $this->y - $vertical_spacing;
                            $line_tofrom = 0;
                            if ($message_title_tofrom_yn == 1)
                                $line_tofrom = 2.5;
                            $msg_line_count = count($gift_msg_array) + $line_tofrom;
                            // Caculate necessary height for print gift message.
                            $temp_height = 0;
                            foreach ($gift_msg_array as $gift_msg_line) {
                                $temp_height += 2 * $font_size_temp;
                            }
                            /***********PRINTING ORDER GIFT MESSAGE NEWPAGE***********/
                            if ($gift_message_yn == 'yesnewpage') {
                                $page_before = $page;
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                if ($background_color_temp != '#FFFFFF') {
                                    $page->setFillColor($background_color_temp);
                                    $page->setLineColor($background_color_temp);
                                    $page->setLineWidth(0.5);
                                    $page->drawRectangle($padded_left, ($this->y - ($font_size_temp / 2)), $padded_right, ($this->y + $font_size_temp + 2));
                                }
                                $this->_setFont($page, 'bold', ($font_size_temp), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                                $page->drawText($helper->__('Order Gift Message for Order') . ' #' . $order->getRealOrderId(), ($msgX + $font_size_gift_message / 3), $this->y, 'UTF-8');
                                $this->y = ($this->y - $font_size_temp * 0.8);
                            }

                            /***********PRINTING ORDER GIFT MESSAGE PISITIONAL BOX***********/
                            if ($gift_message_yn == 'yesbox' && $notes_position != 'yesbox') {
                                $this->y = $positional_message_box_fixed_position[1];
                            }
                            $flag_print_newpage = 0;
                            if (($this->y - $temp_height) < 10) {
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                else $this->y = $page_top;

                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));
                                $flag_print_newpage = 1;
                                $items_y_start = $this->y;
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            } else
                                $this->y -= $font_size_temp * 1.7;
                            $left_bg_gift_msg = $msgX;

                            if (($gift_message_yn == 'yesbox')) {
                                if ($flag_print_newpage == 0) {
                                    $bottom_bg_gift_msg = $this->y - $msg_line_count * ($font_size_temp + 1) - $font_size_temp * 0.5;
                                    if ($notes_position != 'yesbox')
                                        $top_bg_gift_msg = $positional_message_box_fixed_position[1];
                                    else
                                        $top_bg_gift_msg = $comments_y + 2;
                                    $this->y += $font_size_temp * 0.5;
                                } else {
                                    $bottom_bg_gift_msg = $this->y - $msg_line_count * ($font_size_temp + 1) - $font_size_temp * 0.5;
                                    $top_bg_gift_msg = ($this->y + $font_size_temp);
                                }
                            } else {
                                if ($msg_line_count < 4)
                                    $bottom_bg_gift_msg = $this->y - ($msg_line_count - 1) * ($font_size_temp + 3) - 5;
                                else
                                    $bottom_bg_gift_msg = $this->y - ($msg_line_count -1) * ($font_size_temp + 3) - 5;
                                $top_bg_gift_msg = ($this->y + $font_size_temp);
                            }
                            $this->drawBackgroundGiftMessage($background_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
                            $this->_setFont($page, 'bold', ($font_size_temp), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                           // $this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $msgX + 4, $this->y, $to_from_from, $font_size_temp, $page);
                             $this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $email_X, $this->y, $to_from_from, $font_size_temp, $page);

                            $this->_setFont($page, $font_style_temp, ($font_size_gift_message - 1), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                            $this->y = $this->drawOrderGiftMessage($gift_msg_array, $email_X, $font_size_temp, $this->y, $page);
                            unset($gift_msg_array);
                            if (isset($giftWrap_info['wrapping_paper'])) {
                                $wrapping_paper_text = trim($giftWrap_info['wrapping_paper']);
                                if ($wrapping_paper_text != '') {
                                    if ($gift_message_yn == 'yesnewpage') {
                                        $this->y -= ($font_size_gift_message + 3);
                                        if (strtoupper($background_color_message) != '#FFFFFF') {
                                            $page->setFillColor($background_color_message_zend);
                                            $page->setLineColor($background_color_message_zend);
                                            $page->setLineWidth(0.5);
                                            $page->drawRectangle($padded_left, ($this->y - ($font_size_gift_message / 2)), $padded_right, ($this->y + $font_size_gift_message + 2));
                                        }

                                        $this->_setFont($page, $font_style_gift_message, ($font_size_gift_message), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);

                                        $this->y -= ($font_size_gift_message + 2);
                                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                    } else {
                                        $this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);

                                        $this->y -= ($font_size_gift_message + 2);
                                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                    }
                                    $this->y -= ($font_size_gift_message + 2);
                                    $this->_setFont($page, 'regular', ($font_size_gift_message - 1), $font_family_gift_message, $this->_general['non_standard_characters'], $font_color_gift_message);
                                    $page->drawText($wrapping_paper_text, ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
                                }
                            }
                        }
                    }

                    /***********PRINTING PRODUCT GIFT MESSAGE***********/
                    if (($product_gift_message_yn == 'yesnewpage' || $product_gift_message_yn == 'yesbox' || $product_gift_message_yn == 'yesunder') && isset($gift_message_array['items']) && ($gift_message_combined = $this->getProductGiftMessage($gift_message_array))) {
                        // add product gift message and history ebay note to order message
                        if ($product_gift_message_yn == 'yesbox') {
                            $message_character_breakpoint = 66;
                            $this->y = $positional_message_box_fixed_position[1];
                            $msgX = $positional_message_box_fixed_position[0];
                            $background_color_temp = $background_color_comments;
                            $font_style_temp = $font_style_comments;
                            $font_family_temp = $font_family_comments;
                            $font_size_temp = $font_size_comments;
                            $font_color_temp = $font_color_comments;
                            $right_bg_gift_msg = $positional_message_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
                            $gift_msg_array = $this->createMsgArray2($gift_message_combined, $positional_message_box_fixed_position_demension_x, $font_size_temp, $font_family_temp);
                        } elseif ($product_gift_message_yn == 'yesunder') {
                            $message_character_breakpoint = 96;
                            $msgX = $padded_left;
                            $background_color_temp = $background_color_gift_message_zend;
                            $font_style_temp = $font_style_gift_message;
                            $font_family_temp = $font_family_gift_message;
                            $font_size_temp = $font_size_gift_message;
                            $font_color_temp = $font_color_gift_message;
                            $right_bg_gift_msg = $padded_right;
                            $gift_msg_array = $this->createMsgArray($gift_message_combined);
                        } elseif ($product_gift_message_yn == 'yesnewpage') {
                            $message_character_breakpoint = 96;
                            $msgX = $padded_left;
                            $background_color_temp = $background_color_comments;
                            $font_style_temp = $font_style_gift_message;
                            $font_family_temp = $font_family_gift_message;
                            $font_size_temp = $font_size_gift_message;
                            $font_color_temp = $font_color_gift_message;
                            $right_bg_gift_msg = $padded_right;
                            $gift_msg_array = $this->createMsgArray($gift_message_combined);
                        }
                        if (($gift_message_yn == $product_gift_message_yn) && (!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL)) {
                            $this->y = $bottom_bg_gift_msg;
                        } elseif ($product_gift_message_yn == $notes_position)
                            $this->y = $comments_y;
                        elseif ($product_gift_message_yn == "yesunder" && $gift_message_yn != "no" && $gift_message_yn != "yesundership" && (!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL))
                            $this->y = $y_before_order_gift_message;
                        $flag_print_newpage = 0;
                        if (($product_gift_message_yn == 'yesbox')) {
                            if ($flag_print_newpage == 0) {
                                $bottom_bg_gift_msg = $this->y - $msg_line_count * ($font_size_temp + 1) - $font_size_temp * 0.5;
                                $top_bg_gift_msg = $positional_message_box_fixed_position[1];
                                $this->y += $font_size_temp * 0.5;
                            } else {
                                $bottom_bg_gift_msg = $this->y - $msg_line_count * ($font_size_temp + 1) - $font_size_temp * 0.5;
                                $top_bg_gift_msg = ($this->y + $font_size_temp * 1.4);
                            }
                        } else {
                            $bottom_bg_gift_msg = $this->y - $msg_line_count * ($font_size_temp + 1);
                            $top_bg_gift_msg = ($this->y + $font_size_temp * 1.4);
                        }
                        $line_tofrom = 0;
                        $msg_line_count = count($gift_msg_array) + $line_tofrom;
                        if ($product_gift_message_yn != 'yesnewpage') {
                            $temp_height = 0;
                            foreach ($gift_msg_array as $gift_msg_line) {
                                $temp_height += 2 * $font_size_temp;
                            }

                            if (($this->y - $temp_height) < 10 && count($gift_msg_array) > 0) {
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                                else $this->y = $page_top;

                                $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                                $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                                $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                                $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                                $items_y_start = $this->y;
                                $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                            }
                            //draw background gift message
                            $left_bg_gift_msg = $msgX;
                            $top_bg_gift_msg = ($this->y + $font_size_temp);
                            $bottom_bg_gift_msg = ($this->y - ($msg_line_count * ($font_size_temp - 1)));
                            $page_temp = $page;
                            if ($gift_message_yn == "yesnewpage" && $product_gift_message_yn != "yesnewpage") {
                                $page = $page_before;
                            }
                            $this->drawBackgroundGiftMessage($background_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
                            $this->_setFont($page, $font_style_temp, ($font_size_temp - 1), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                            $this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $font_size_temp / 3, $font_size_temp, $this->y, $page);
                            unset($gift_msg_array);
                            $page = $page_temp;
                        } else {
                            if ($gift_message_yn != 'yesnewpage') {
                                $page = $this->nooPage($this->_packingsheet['page_size']);
                                $page_count++;
                                if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                            } elseif (!is_null($gift_message_id))
                                $this->y = $bottom_bg_gift_msg - 25;
                            $this->_setFont($page, 'bold', ($font_size_temp), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                            $page->drawText($helper->__('Product Gift Message for Order') . ' #' . $order->getRealOrderId(), ($msgX), $this->y, 'UTF-8');
                            $this->y = ($this->y - 10 - $font_size_temp);
                            $left_bg_gift_msg = $msgX;
                            $top_bg_gift_msg = ($this->y + $font_size_temp);
                            $bottom_bg_gift_msg = ($this->y - ($msg_line_count * ($font_size_temp - 1)));
                            $this->drawBackgroundGiftMessage($background_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
                            $this->_setFont($page, $font_style_temp, ($font_size_temp - 1), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
                            foreach ($gift_msg_array as $gift_msg_line) {
                                $page->drawText(trim($gift_msg_line), ($msgX + $font_size_temp / 3), $this->y, 'UTF-8');
                                $this->y -= ($font_size_temp + 3);
                            }
                            unset($gift_msg_array);
                        }
                    }

                    /*************************REPEAT GIFT MESSAGE **************************/
                    if($repeat_gift_message_yn == 1){

                        $this->printRepeatGiftMessage($page,$order, $gift_message_array, $background_color_comments,$font_style_comments, $font_family_comments, $font_size_comments, $font_color_comments, $positional_remessage_box_fixed_position, $positional_message_box_fixed_position_demension_x, $giftWrap_info, $gift_message_item, $background_color_gift_message,$gift_message_id);
                    }
                    if (Mage::helper('pickpack')->isInstalled('Moogento_Cn22'))
                    if($this->_getConfig('show_custom_declaration',0, false, $wonder, $store_id) == 1)
                    {
                        if($case_rotate > 0)
                            $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                        try{
                            $custom_section_model = new Moogento_Cn22_Model_Pdf();
                            $custom_section_model->printCustomSection(0,$page,$order,$wonder,$show_custom_declaration_nudge[0],$show_custom_declaration_nudge[1],$this->_item_qty_array,$show_custom_declaration_dimension[0],$show_custom_declaration_dimension[1]);                        
                        }
                        catch(Exception $e)
                        {
                            echo $e->getMessage(); exit;
                        }
                        if($case_rotate > 0)
                            $this->reRotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                    }

                    /*************************** PRINT GIFT WRAP ICON AT TOP RIGHT *******************************/
                    if($show_gift_wrap_yn){
                        if($show_top_right_gift_icon){
                            $media_path = Mage::getBaseDir('media');
                            $image = Zend_Pdf_Image::imageWithPath($media_path.'/moogento/pickpack/big-gift_wrap.png');
                            $x2 = $padded_right - $show_gift_wrap_top_right_xpos;
                            $x1 = $x2 - 50;
                            $y2 = $page_top + 5  - $show_gift_wrap_top_right_ypos;
                            $y1 = $y2 - 50;
                            $pdf->pages[$current_header_page_index]->drawImage($image, $x1, $y1 , $x2, $y2);
                        }
                    }
                    /*************************** END PRINT GIFT WRAP ICON AT TOP RIGHT *******************************/
                    
                    if (Mage::helper('pickpack')->isInstalled('Moogento_CourierRules'))
                        if($this->_getConfig('show_courierrules_shipping_label',0, false, $wonder, $store_id) == 1)
                        {
                            if($case_rotate > 0)
                                $this->rotateLabel($case_rotate,$page,$page_top,$padded_right,$nudge_rotate_address_label);
                            try{
                                if (mageFindClassFile('Moogento_CourierRules_Helper_Connector')){

                                    $show_courierrules_label_nudge = explode(',',$this->_getConfig('show_courierrules_shipping_label_nudge', '50,50', false, $wonder, $store_id));
                                    $show_courierrules_label_dimension = explode(',',$this->_getConfig('show_courierrules_shipping_label_dimension', '0,0', false, $wonder, $store_id));

                                    $labels = Mage::helper('moogento_courierrules/connector')->getConnectorLabels($order);
                                    $i = 0;
                                    foreach($labels as $label) {
                                        if($i > 0) {
                                            $page = $this->nooPage($this->_packingsheet['page_size']);
                                        }
                                        $tmpFile = Mage::helper('pickpack')->getConnectorLabelTmpFile($label);
                                        $imageObj = Zend_Pdf_Image::imageWithPath($tmpFile);
                                        $page->drawImage($imageObj, $show_courierrules_label_nudge[0] , $show_courierrules_label_nudge[1],  $show_courierrules_label_nudge[0] + $show_courierrules_label_dimension[0], $show_courierrules_label_nudge[1] + $show_courierrules_label_dimension[1]);
                                        unset($tmpFile);
                                        $i++;
                                    }
                                }
                            }
                            catch(Exception $e)
                            {
                                echo $e->getMessage(); exit;
                            }
                        }
                    
                    
                    $page_count = 1;
                }
                $count_item = $count_item -1;
                }while($count_item > 0);
            }
            if ((!isset($supplier_ubermaster[($s + 1)])) || ($split_supplier_yn == 'no')) {
                $loop_supplier = 0;
            }
            $s++;
        } while ($loop_supplier != 0);
        $this->_afterGetPdf();
        return $pdf;

    }
    
   private function getQrcodeText($pattern,$order)
    {
        $date_format = 'd/m/Y';
        $invoice_title = $pattern;
        $store_id = $order->getStore()->getId();
        $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $date_format);
        if ($invoice_title != '') {
            ////Order date. n/a if empty
            $order_date_title = 'n/a';
            $dated_title = $order->getCreatedAt();
            $dated_timestamp = strtotime($dated_title);

            if ($dated_title != '') {
                $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);

            } else {
                //This field is empty.
                $from_date = "{{if order_date}}";
                $end_date = "{{endif order_date}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }

                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);

            }
            //////////// Invoice date  n/a if empty
            if ($order->getCreatedAtStoreDate()) {
                $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $date_format);
                $invoice_title = str_replace("{{if invoice_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_date}}", '', $invoice_title);
            } else {
                //This field is empty.
                $from_date = "{{if invoice_date}}";
                $end_date = "{{endif invoice_date}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }
                $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);
                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);
            }

            $invoice_number_display = '';

            foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                if ($_tmpInvoice->getIncrementId()) {
                    if ($invoice_number_display != '') $invoice_number_display .= ',';
                    $invoice_number_display .= $_tmpInvoice->getIncrementId();
                }
                break;
            }

            if ($invoice_number_display == '') {
                //This field is empty.
                $from_date = "{{if invoice_id}}";
                $end_date = "{{endif invoice_id}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }
                $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);
            } 
            else {
                $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
            }

            /*****  Get Warehouse information ****/
            if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                $warehouse_helper = Mage::helper('warehouse');
                $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                $resource = Mage::getSingleton('core/resource');
                /**
                 * Retrieve the read connection
                 */
                $readConnection = $resource->getConnection('core_read');
                $query = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse") . ' WHERE entity_id=' . $order->getData('entity_id');
                $warehouse_stock_id = $readConnection->fetchOne($query);
                if ($warehouse_stock_id) {
                    $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                    $warehouse_title = ($warehouse->getData('title'));
                } else {
                    $warehouse_title = '';
                }
            } else {
                $warehouse_title = '';
            }

            $from_date = "{{if warehouse}}";
            $end_date = "{{endif warehouse}}";
            $from_date_pos = strpos($invoice_title, $from_date);
            if ($from_date_pos !== false) {
                $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                $date_length = $end_date_pos - $from_date_pos;
                $date_str = substr($invoice_title, $from_date_pos, $date_length);
                $invoice_title = str_replace($date_str, '', $invoice_title);
            } else {
                $invoice_title = str_replace("{{if warehouse}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif warehouse}}", '', $invoice_title);
            }
            unset($from_date);
            unset($end_date);
            unset($from_date_pos);
            unset($end_date_pos);
            unset($date_length);
            unset($date_str);
            /*****  Get Warehouse information ****/
            if ($date_format_strftime !== true) $printing_date_title = date($date_format, Mage::getModel('core/date')->timestamp(time()));
            else $printing_date_title = strftime($date_format, Mage::getModel('core/date')->timestamp(time()));
            if ($printing_date_title != '') {
                $invoice_title = str_replace("{{if printing_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif printing_date}}", '', $invoice_title);
            }

            $order_number_display_title = $order->getRealOrderId();
            if ($order_number_display_title != '') {
                $invoice_title = str_replace("{{if order_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_id}}", '', $invoice_title);
            }

            //market place order ID
            $marketPlaceOrderId = $this->getMarketPlaceId($order);
            if($marketPlaceOrderId != ''){
                $invoice_title = str_replace("{{if marketplace_order_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif marketplace_order_id}}", '', $invoice_title);
            }
            //ebay sale number
            $ebay_sale_number = $this->getEbaySaleNumber($order);
            if($ebay_sale_number != ''){
                $invoice_title = str_replace("{{if ebay_sales_number}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif ebay_sales_number}}", '', $invoice_title);
            }

            //[fixed text] [%customer_id%] [%order_id%] [%invoice_id%] [%order_date%] [%invoice_date%] [%printed_date%] [%postcode%] [%shipping_lastname%] [%shipping_name%]
            //
            $customer_id = trim($order->getCustomerId());
            $printed_date = date('d/m/Y', Mage::getModel('core/date')->timestamp(time()));
            
            $shipping_address = $order->getShippingAddress();
            if(is_object($shipping_address))
            {
            if ($shipping_address->getPostcode())
            $postcode = Mage::helper('pickpack/functions')->clean_method(strtoupper($shipping_address->getPostcode()),'pdf');
            else
            $postcode ='';
            if($shipping_address->getLastname())
            $shipping_lastname = Mage::helper('pickpack/functions')->clean_method($shipping_address->getLastname(),'pdf');
            else
            $shipping_lastname = '';
            if($shipping_address->getPrefix() && $shipping_address->getFirstname() && $shipping_address->getLastname())
            $shipping_name = Mage::helper('pickpack/functions')->clean_method($shipping_address->getPrefix() . ' ' . $shipping_address->getFirstname() . ' ' . $shipping_address->getLastname(),'pdf');
            else
            $shipping_name = '';
            }
            else
            {
                $postcode ='';
                $shipping_lastname = '';
                $shipping_name = '';
            }


            $arr_1 = array('{{order_date}}', '{{invoice_date}}', '{{printing_date}}', '{{order_id}}', '{{invoice_id}}', '{{marketplace_order_id}}', '{{ebay_sales_number}}','{{customer_id}}','{{printed_date}}','{{postcode}}','{{shipping_lastname}}','{{shipping_name}}');

            $arr_2 = array($order_date_title, $invoice_date_title, $printing_date_title, $order_number_display_title, $invoice_number_display, $marketPlaceOrderId, $ebay_sale_number,$customer_id,$printed_date,$postcode,$shipping_lastname,$shipping_name);

            $invoice_title_print = str_replace($arr_1, $arr_2, $invoice_title);
            return $invoice_title_print;
            } 
        return '';            
    }    

    public function printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,$barcode_font_size,$white_color)
    {
        $nextCollumnX = getPrevNext2($this->columns_xpos_array, 'sku_barcodeX', 'next');

        $after_print_barcode_y = $sku_barcodeY - $barcode_font_size;
        $barcodeString = $this->convertToBarcodeString($barcode, $barcode_type);
        $barcodeWidth = $this->parseString($barcode, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
        $page->setFillColor($white_color);
        $page->setLineColor($white_color);
        $page->drawRectangle(($sku_barcodeX - 5), ($sku_barcodeY - 2), ($sku_barcodeX + $barcodeWidth + 5), ($sku_barcodeY - 2 + ($barcode_font_size * 1.6)));
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);

        if ($sku_barcodeX + $barcodeWidth > $padded_right){
            $this->_setFont($page, $this->_pageFonts['font_style_body'], $this->_pageFonts['font_size_body'], $this->_pageFonts['font_family_body'], $this->_pageFonts['non_standard_characters'], '#FF3333');
            $page->drawText("!! TRIMMED BARCODE !!", ($sku_barcodeX), ($sku_barcodeY), 'UTF-8');
        }
        else if ($sku_barcodeX + $barcodeWidth >= $nextCollumnX){
            $page->drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY - (1.5*$barcode_font_size)), 'CP1252');
        }
        else {
            $page->drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY), 'CP1252');
        }
        return $after_print_barcode_y;
    }
}
?>
