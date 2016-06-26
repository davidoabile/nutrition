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
 * File        Invoices.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://www.moogento.com/License.html
 */

/**
1.7
* Sales Order Invoice / Packing slip PDF model
*
* @category   Mage
* @package    Mage_Sales
* @author     Moogento.com <moo@moogento.com>
* This extension is only licensed for the single original Magento Instance that it was purchased for
*/
if (defined('COMPILER_INCLUDE_PATH')) {
    
    include_once "Moogento_Pickpack_Model_Sales_Order_Pdf_Functions.php";
} else {
    include_once "Functions.php";
}

define('LATIN1_UC_CHARS', 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝº');
define('LATIN1_LC_CHARS', 'àáâãäåæçèéêëìíîïðñòóôõöøùúûüýº');

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices extends Moogento_Pickpack_Model_Sales_Order_Pdf_Aabstract
{
    protected $_nudgeY;
    protected $_itemsY;

    protected $_printing_format = array();
    protected $_product_config = array();
    protected $_order_config = array();
    protected $_helper = '';
    protected $_logo_maxdimensions = array();
    protected $_columns_xpos_array = array();
    protected $_columns_xpos_array_order = array();

    //need to add to obj group option
    protected $_wonder = '';
    protected $_bottom_shipping_address_id_yn = 0;
    protected $_case_rotate = 0;
    protected $_non_standard_characters = 0;
    protected $_nudge_rotate_address_label = array();
    protected $_addressFooterXYDefault = "";
    protected $_addressFooterXY = array();
    protected $_bottom_shipping_address_yn = 0;
    //end need to add to obj group option

    protected $_general = array(); //general config for pickpack
    protected $_packingsheet = array(); //packing-sheet/invoice config for pickpack
    
    public function __construct()
    {
        $this->action_path = Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/';
        $this->setGeneralConfig();
    }

    // remove function getPdfLetter()  --- not sure the use of this
    // remove function getCsvDhlEasylogExport()  --- not sure the use of this
    
    public function getPdf($invoices = array())
    {
        
        /**
         * get store id
         */
        $store_id = Mage::app()->getStore()->getId();
        
        $show_price = 0;
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($page, 10);
        
        
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
            }
            
            $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
            
            if ($page_size == 'letter') {
                $page         = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
                $page_top     = 770;
                $padded_right = 587;
            } elseif ($page_size == 'a4') {
                $page         = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $page_top     = 820;
                $padded_right = 570;
            } elseif ($page_size == 'a5-landscape') {
                $page         = $pdf->newPage('596:421');
                $page_top     = 395;
                $padded_right = 573;
            } elseif ($page_size == 'a5-portrait') {
                $page         = $pdf->newPage('421:596');
                $page_top     = 573;
                $padded_right = 395;
            }
            
            $pdf->pages[] = $page;
            
            $order = $invoice->getOrder();
            
            /* Add image */
            $this->insertLogo($page, $page_size, 'pack', $order->getStore());
            
            /* Add address */
            $store_address = $this->insertAddress($page, 'top', $order->getStore());
            
            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));
            
            $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $store_id);
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
            
            $barcode = Mage::getStoreConfig('pickpack_options/wonder/pickpack_packbarcode');
            if ($barcode == 1) {
                $barcodeString = $this->convertToBarcodeString($order->getRealOrderId(), $barcode_type);
                $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
                $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), 18);
                $page->drawText($barcodeString, 452, 800, 'CP1252');
            }
            
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            
            /* Add products list table */
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
            $this->_setFontItalic($page, 14);
            
            $page->drawRectangle(25, $this->y, $padded_right, $this->y - 20);
            
            // HEIGHT PRODUCTS HEADER lINE
            $this->y -= 15;
            
            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $page->drawText(Mage::helper('sales')->__('items'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('codes'), 255, $this->y, 'UTF-8');
            if ($show_price > 0)
                $page->drawText(Mage::helper('sales')->__('Price'), 370, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('numbers'), 420, $this->y, 'UTF-8');
            if ($show_price > 0)
                $page->drawText(Mage::helper('sales')->__('Subtotal'), 525, $this->y, 'UTF-8');
            
            
            $this->y -= 20;
            
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                
                if ($this->y < 15) {
                    $page = $this->newPage(array(
                        'table_header' => true
                    ));
                }
                
                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
                $this->y -= 8;
            }
            
            /* Add totals */
            if ($show_price > 0)
                $page = $this->insertTotals($page, $invoice);
            
            // bottom address label
            $this->y = 115;
            
            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            
            /* Add bottom store address */
            $store_address = $this->insertAddress($page, 'bottom', $order->getStore());
            $this->y -= 20;
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
            
        }
        
        
        
        $this->_afterGetPdf();
        return $pdf;
    }
    
    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $page_top              = 770;
            $padded_right          = 587;
        } else if ($page_size == 'a4') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_A4;
            
            // $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top     = 820;
            $padded_right = 570;
        } elseif ($page_size == 'a5-landscape') {
            $settings['page_size'] = '596:421';
            
            // $page = $pdf->newPage('596:421');
            $page_top     = 395;
            $padded_right = 573;
        } elseif ($page_size == 'a5-portrait') {
            $settings['page_size'] = '421:596';
            
            // $page = $pdf->newPage('596:421');
            $page_top     = 573;
            $padded_right = 395;
        }
        
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page     = $this->_getPdf()->newPage($pageSize);
        
        $this->_getPdf()->pages[] = $page;
        $this->y                  = ($page_top - 20);
        
        return $page;
    }
    
    
    public function nooPage($page_size = '')
    {
        if (!$page_size || $page_size == '')
            $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $page_top              = 770;
            $padded_right          = 587;
        } else if ($page_size == 'a4') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_A4;
            $page_top              = 820;
            $padded_right          = 570;
        } elseif ($page_size == 'a5-landscape') {
            $settings['page_size'] = '596:421';
            $page_top              = 395;
            $padded_right          = 573;
        } elseif ($page_size == 'a5-portrait') {
            $settings['page_size'] = '421:596';
            $page_top              = 573;
            $padded_right          = 395;
        }
        
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page     = $this->_getPdf()->newPage($pageSize);
        
        $this->_getPdf()->pages[] = $page;
        $this->y                  = ($page_top - 20);
        
        return $page;
    }
    
    public function newPageLabel(array $settings = array())
    {
        $page_size = $this->_getConfig('page_size', 'a4', false, 'label');
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $page_top              = 770;
            $padded_right          = 587;
        } else if ($page_size == 'a4') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_A4;
            
            // $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top     = 820;
            $padded_right = 570;
        } elseif ($page_size == 'a5-landscape') {
            $settings['page_size'] = '596:421';
            
            // $page = $pdf->newPage('596:421');
            $page_top     = 395;
            $padded_right = 573;
        } elseif ($page_size == 'a5-portrait') {
            $settings['page_size'] = '421:596';
            
            // $page = $pdf->newPage('596:421');
            $page_top     = 573;
            $padded_right = 395;
        } elseif ($page_size == 'zebra') {
            $settings['page_size'] = '288:432';
            
            // $page = $pdf->newPage('596:421');
            $page_top     = 286;
            $padded_right = 430;
        }
        
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page     = $this->_getPdf()->newPage($pageSize);
        
        $this->_getPdf()->pages[] = $page;
        $this->y                  = ($page_top - 20);
        
        return $page;
    }
    
    public function newPageZebra(array $settings = array())
    {
        $page_size    = 'zebra';
        $page_top     = 430;
        $padded_right = 286;
        $pageSize     = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page         = $this->_getPdf()->newPage($pageSize);
        
        $this->_getPdf()->pages[] = $page;
        $this->y                  = ($page_top - 20);
        
        return $page;
    }
    
    protected function newPage2()
    {
        
        $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
        
        if ($page_size == 'letter') {
            $page         = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $page_top     = 770;
            $padded_right = 587;
        } elseif ($page_size == 'a4') {
            $page         = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top     = 820;
            $padded_right = 570;
        }
        
        $this->_getPdf()->pages[] = $page;
        $this->y                  = $this->_itemsY;
        
        $font_size_productline = $this->_getConfig('pickpack_fontsizeproductline', 9, false);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $font_size_productline);
        return $page;
    }
    
    protected function _getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true,$section = 'pickpack_options')
    {
        if ($trim)
            $value = trim(Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store));
        else
            $value = Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store);
        if (strstr($field, '_color') !== FALSE) {
            if ($value != 0 && $value != 1) {
                $value = checkColor($value);
            }
        }
        /* check for the page body font color white */
        if( $field == 'font_color_body'  ){
            if( $value == '#ffffff' || $value ==  strtoupper('#ffffff') ){
	         $value = '#222222';			
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
    
    protected function parseString($string, $font = null, $fontsize = null)
    {
        if (is_null($font))
            $font = $this->_font;
        if (is_null($fontsize))
            $fontsize = $this->_fontsize;
        
        $drawingString = iconv('UTF-8', 'UTF-16BE//TRANSLIT//IGNORE', $string);
        $characters    = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        if (!is_object($characters)) {
            $glyphs      = $font->glyphNumbersForCharacters($characters);
            $widths      = $font->widthsForGlyphs($glyphs);
            $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontsize;
            return $stringWidth;
        } else {
            // fudge for other extensions bad characters
            return (strlen($string) * $fontsize);
        }
    }
    
    /**
     * Create pdf for current invoice
     */
    public function printAction($order_id)
    {
        $this->getPdfDefault($order_id, 'order', 'pack');
        parent::printAction();
    }
    
    public function printInvoiceAction($order_id)
    {
        $this->getPdfDefault($order_id, 'order', 'invoice');
        parent::printAction();
    }
    
    protected function rightAlign($str, $font_family = Zend_Pdf_Font::FONT_HELVETICA, $font_size = 10, $extra_number = 12, $subtotal_label_rightalign_xpos)
    {
        $font_temp           = Zend_Pdf_Font::fontWithName($font_family);
        $line_width          = $this->parseString('1234567890', $font_temp, $font_size);
        $char_width          = $line_width / $extra_number;
        $width_need_to_print = $char_width * ($str);
        
        return $subtotal_label_rightalign_xpos - $width_need_to_print;
    }
    
    protected function rightAlign2($str, $font_family, $font_size, $style = 'regular', $subtotal_label_rightalign_xpos)
    {
        //Real string, real font, real size, real style.
        $font_temp  = $this->getFontName2($font_family, $style);
        $line_width = $this->parseString($str, $font_temp, $font_size);
        return $subtotal_label_rightalign_xpos - $line_width; 
    }
    
    protected function getFontName2($font = 'helvetica', $style = 'regular', $non_standard_characters = 0)
    {
        switch ($font) {
            case 'helvetica':
                switch ($style) {
                    case 'regular':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                        break;
                    case 'italic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'ariali.ttf');
                        break;
                    case 'bold':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbd.ttf');
                        break;
                    case 'bolditalic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbi.ttf');
                        break;
                    default:
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                        break;
                }
                break;

            case 'courier':
                switch ($style) {
                    case 'regular':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'cour.ttf');
                        break;
                    case 'italic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'couri.ttf');
                        break;
                    case 'bold':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'courbd.ttf');
                        break;
                    case 'bolditalic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'courbi.ttf');
                        break;
                    default:
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'cour.ttf');
                        break;
                }
                break;

            case 'times':
                switch ($style) {
                    case 'regular':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'times.ttf');
                        break;
                    case 'italic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesi.ttf');
                        break;
                    case 'bold':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesbd.ttf');
                        break;
                    case 'bolditalic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesbi.ttf');
                        break;
                    default:
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'times.ttf');
                        break;
                }
                break;

            case 'msgothic':
                $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'msgothic.ttf');
                break;

            case 'tahoma':
                $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'tahoma.ttf');
                break;

            case 'garuda':
                switch ($style) {
                    case 'regular':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garuda.ttf');
                        break;
                    case 'italic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudai.ttf');
                        break;
                    case 'bold':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudabd.ttf');
                        break;
                    case 'bolditalic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudabi.ttf');
                        break;
                    default:
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garuda.ttf');
                        break;
                }
                break;

            case 'sawasdee':
                switch ($style) {
                    case 'regular':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdee.ttf');
                        break;
                    case 'italic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeei.ttf');
                        break;
                    case 'bold':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeebd.ttf');
                        break;
                    case 'bolditalic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeebi.ttf');
                        break;
                    default:
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdee.ttf');
                        break;
                }
                break;

            case 'kinnari':
                switch ($style) {
                    case 'regular':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnari.ttf');
                        break;
                    case 'italic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnarii.ttf');
                        break;
                    case 'bold':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnaribd.ttf');
                        break;
                    case 'bolditalic':
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnaribi.ttf');
                        break;
                    default:
                        $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnari.ttf');
                        break;
                }
                break;

            case 'purisa':
                $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'purisa.ttf');
                break;

            case 'custom':
                $font = Zend_Pdf_Font::fontWithPath($style);
                break;

            default:
                switch ($style) {
                    case 'regular':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                        break;
                    case 'italic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'ariali.ttf');
                        break;
                    case 'bold':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbd.ttf');
                        break;
                    case 'bolditalic':
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbi.ttf');
                        break;
                    default:
                        if ($non_standard_characters != 1) {
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        } else
                            $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                        break;
                }
                break;
        }
        return $font;
    }

    protected function widthNeedToPrint($str, $font_family = Zend_Pdf_Font::FONT_HELVETICA, $font_size = 10, $extra_number = 12, $subtotal_label_rightalign_xpos)
    {
        $font_temp           = Zend_Pdf_Font::fontWithName($font_family);
        $line_width          = $this->parseString('1234567890', $font_temp, $font_size);
        $char_width          = $line_width / $extra_number;
        $width_need_to_print = $char_width * ($str);

        return $width_need_to_print;
    }

    protected function getShippingAddressMaxPriority($order, $shipping_address_background)
    {

        $print_row                                = -1;
        $max_priority_row                         = 9999;
        $shipping_background_type                 = '';
        $find_shipping_pattern_in_shipping_detail = 0;
        $shipping_description                     = $order->getShippingDescription();


        if (is_array($shipping_address_background)) {

            foreach ($shipping_address_background as $rowId => $row_value) {

                $row_type = $row_value['type'][0];
                if (($row_type == 'shipping_method') && ($shipping_description != '')) {

                    $shipping_description   = strtolower($shipping_description);
                    $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                    foreach ($list_carriers_name_row as $k => $v) {

                        $v = strtolower($v);
                        if (!empty($v)) {
                            $pos = strpos($shipping_description, $v);
                        } else {
                            $pos = false;
                        }
                        if (($pos !== false) || ($v == '')) {

                            if ($row_value['priority'] == '') {
                                $row_value['priority'] = 999;
                            }
                            if ($row_value['priority'] < $max_priority_row) {

                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                            $find_shipping_pattern_in_shipping_detail = 1;
                        }
                    }
                    unset($list_carriers_name_row);
                } else if ($row_type == 'courier_rules') {
                    if(Mage::helper('pickpack')->isInstalled('Moogento_CourierRules'))
                    {
                        $courierrules_description = $order->getData('courierrules_description');
                        if(strlen(trim($courierrules_description)) > 0)
                        {
                            $shipping_description = $courierrules_description;
                        }

                        $shipping_description   = strtolower($shipping_description);
                        $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                        foreach ($list_carriers_name_row as $k => $v) {
                            $v = strtolower($v);
                            if (!empty($v)) {
                                $pos = strpos($shipping_description, $v);
                            } else {
                                $pos = false;
                            }
                            if (($pos !== false) || ($v == '')) {

                                if ($row_value['priority'] == '') {
                                    $row_value['priority'] = 999;
                                }
                                if ($row_value['priority'] < $max_priority_row) {

                                    $print_row                = $rowId;
                                    $max_priority_row         = $row_value['priority'];
                                    $shipping_background_type = $row_type;
                                }

                                $find_shipping_pattern_in_shipping_detail = 1;
                            }
                        }
                        unset($list_carriers_name_row);
                    }
                } else if ($row_type == 'shipping_zone') {

                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    $zone_collection = mage::getModel("moogento_courierrules/zone")->getCollection();
                    foreach ($zone_collection as $item){
                        $item_data = $item->getData();
                        if ( in_array($customer_country_id,$item_data['countries']) ){
                            if ($row_value['priority'] == '') {
                                $row_value['priority'] = 999;
                            }
                            if ($row_value['priority'] < $max_priority_row) {
                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                        }

                    }

                } else if ($row_type == 'country_group') {
                    $country_in_group     = 0;
                    $image_position_nudge = array();
                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    if ((Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy'))) {
                        $countryGroups                = Mage::getStoreConfig('moogento_shipeasy/country_groups');
                        $country_label_group          = $row_value['country_group'][0];
                        $country_group_list_key       = str_replace('label', 'countries', $country_label_group);
                        $country_group_list_value     = $countryGroups[$country_group_list_key];
                        $country_group_list_value_arr = explode(",", $country_group_list_value);

                        foreach ($country_group_list_value_arr as $k => $v) {
                            $pos = strpos($v, $customer_country_id);
                            if ($pos !== false) {
                                $country_in_group = 1;
                            if ($row_value['priority'] == '') {
                                $row_value['priority'] = 999;
                            }
                                if ($row_value['priority'] < $max_priority_row) {
                                    $print_row                = $rowId;
                                    $max_priority_row         = $row_value['priority'];
                                    $shipping_background_type = $row_type;
                                }
                            }
                        }

                    }
                }
            }
        }
        return $print_row;
    }
    protected function printShippingAddressBackground($order, $scale, $shipping_address_background, $page_top_or_bottom, $page, $padded_left, $label_width = 0, $nudge_shipping_addressX = 0,$resolution,$image_zebra=null)
    {
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
        $image_simple = new SimpleImage();
        $print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
        if ((($print_row != -1))) {
            $image_file_name = Mage::getBaseDir('media') . '/moogento/pickpack/image_background/' . $shipping_address_background[$print_row]['file'];
            if ($image_file_name) {
                $image_part                  = explode('.', $image_file_name);
                $image_ext                   = array_pop($image_part);
                $shipping_background_nudge_x = $shipping_address_background[$print_row]['xnudge'];
                $shipping_background_nudge_y = $shipping_address_background[$print_row]['ynudge'];


                if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($image_file_name))) {
                    $logo_shipping_maxdimensions[0] = $label_width - $nudge_shipping_addressX;
                    $logo_shipping_maxdimensions[1] = 300;

                    $imageObj        = Mage::helper('pickpack')->getImageObj($image_file_name);

                    $orig_img_width  = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_height = $imageObj->getOriginalHeight();
                    $img_width  = $imageObj->getOriginalWidth();
                    if ($orig_img_width > ($logo_shipping_maxdimensions[0])) {
                        $img_height = ceil(($logo_shipping_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                        $img_width  = $logo_shipping_maxdimensions[0];
                    }
					if(isset($image_simple))
					{
						//Create new temp image
						$final_image_path2 = $image_file_name;//$media_path . '/' . $image_url_after_media_path;
    					 $image_source = $final_image_path2;
						$io = new Varien_Io_File();
						$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');

                        $img_width1 = intval($img_width*300/72);
                        $img_height1 = intval($img_height*300/72);

                        $filename = pathinfo($image_source, PATHINFO_FILENAME)."_".$img_width1."X".$img_height1.".jpeg";
						$image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$filename;

                        if(!(file_exists($image_target))){
                            $image_simple->load($image_source);
                            $image_simple->resize($img_width1,$img_height1);
                            $image_simple->save($image_target, IMAGETYPE_JPEG, 100);
                        }
                        $image_file_name = $image_target;
                    }
                    $image = Zend_Pdf_Image::imageWithPath($image_file_name);
                    $x1 = $padded_left + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y1 = $page_top_or_bottom - $img_height + $shipping_background_nudge_y;
                    $x2 = $padded_left + $img_width + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y2 = $page_top_or_bottom + $shipping_background_nudge_y;
                    if($scale && is_numeric($scale) && $scale!= 100){
                        if($scale < 100){
                            $y1 =  $y1+(($y2-$y1)*$scale/100);
                            $x2 =  $x2-(($x2-$x1)*$scale/100);
                        }
                        else{
                            $y1 =  $y1-(($y2-$y1)*($scale-100)/100);
                            $x2 =  $x2+(($x2-$x1)*($scale-100)/100);
                        }
                    }
                    $page->drawImage($image, $x1 ,$y1 , $x2, $y2);
                }
            }
        }
        unset($image_zebra);
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


    protected function showShippingAddresBackground($order, $page_top, $wonder = "", $store_id, $page, $padded_left, $scale = 100, $label_width = 250, $nudge_shipping_addressX = 0, $resolution = null)
    {

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
        $top_or_bottom = $page_top;
        $this->printShippingAddressBackground($order, $scale, $shipping_address_background, $top_or_bottom, $page, $padded_left, $label_width, $nudge_shipping_addressX, $resolution);
    }

    public function checkCourrierrulesAndM2epro($shipping_address_background){
        if (Mage::helper('pickpack')->isInstalled("Moogento_CourierRules")){
            return $shipping_address_background;
        }
        if (Mage::helper('pickpack')->isInstalled("Ess_M2ePro")){
            $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
            $_allShippingMethodDescription = array();
            foreach($methods as $_ccode => $_carrier)
            {
                if($_methods = $_carrier->getAllowedMethods())
                {
                    if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                        $_title = $_ccode;

                    foreach($_methods as $_mcode => $_method)
                    {
                        if ($_mcode == "m2eproshipping") continue;
                        $_allShippingMethodDescription[] = $_title." - ".$_method;
                    }
                }
            }

            foreach ($shipping_address_background as $key => $item){
                if (trim($item['pattern'])=="") continue;
                if (!in_array($item['pattern'] , $_allShippingMethodDescription)){
                    unset($shipping_address_background[$key]);
                }
            }
        }
        return $shipping_address_background;
    }

    protected function getNameDefaultStore($item)
    {
        $product_id      = $item->getProductId();
        $default_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $_newProduct     = Mage::helper('pickpack')->getProductForStore($product_id, $default_storeId);
        $name            = trim($_newProduct->getName());
        return $name;
    }
    protected function getNameShippingLabel($order){
		$name_ship_label = "";
		$store_id = Mage::app()->getStore()->getId();
		$shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $store_id);
        try {
            $shipping_address_background = unserialize($shipping_address_background);
        }
        catch (Exception $e) {
        }
		$print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
		if($print_row != -1)
			$name_ship_label = $shipping_address_background[$print_row]['name'];
		return $name_ship_label;
	}
    protected function getProductAttributeValue($product, $attribute_code, $preprocess = true)
    {
        $return_value ='';
        try{
            if (is_object($product) && !is_null($product) && $attributeValue = $product->getData($attribute_code)) {

                $attribute = $product->getResource()->getAttribute($attribute_code);
                if ($attribute->usesSource()) {
                    $return_value = $product->getAttributeText($attribute_code, $attributeValue);
                } else {
                    $return_value = $attributeValue;
                }
                if ($preprocess) {
                    $return_value = preg_replace('/[^a-zA-Z0-9\s\.\-\/\=\?\'\"\<\>\;\:\{\}\(\)]/', '', $return_value);
                }
            }

            return $return_value;
        }
        catch(Exception $e)
        {
            Mage::logException($e);
            return '';
        }

    }

    protected function getProductAttributeValue2($product,$attribute_code,$store_id,$product_id)
    {
    	$return_value ='';
    	try{
			if (is_object($product)) {
				if ($shelving_var = $product->getData($shelving_attribute)) {
				} elseif ($shelving_var = $product->getAttributeText($attribute_code)) {
				} elseif ($shelving_var = $product[$shelving_attribute]) {
				}
			}

			if (is_object($product)) {
				if ($product->setStoreId($store_id)->load($product_id))
					if ($product) {
						$product = $product->setStoreId($store_id)->load($product_id);
						if ($product->getData($shelving_attribute))
							$return_value= $product->getData($shelving_attribute);
					} elseif ($product->getData($shelving_attribute)) {
						$return_value= $product->getData($shelving_attribute);
					}
				if ($product->setStoreId($store_id)->load($product_id)->getAttributeText($shelving_attribute))
					if ($product->getAttributeText($shelving_attribute)) {
						$return_value= $product->setStoreId($store_id)->load($product_id)->getAttributeText($shelving_attribute);
						$return_value= $product->getAttributeText($shelving_attribute);
					} elseif ($product[$shelving_attribute])
						$return_value= $product[$shelving_attribute];
			}

			if (is_array($return_value)) {
				$return_value= implode(',', $return_value);
				$return_value= preg_replace('~^,~', '', $return_value);
			}
			return $return_value;
		}
		catch(Exception $e)
		{
			Mage::logException($e);
			return '';
		}
    }
	protected function getShippingAddressFull($order, $font_size_label)
    {
        $address_full = '';
        $i            = 0;
        while ($i < 10) {
            if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
                $value             = trim($order->getShippingAddress()->getStreet($i));
                $max_chars         = 20;
                $font_temp         = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $font_size_compare = ($font_size_label * 0.8);
                $line_width        = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                $char_width        = $line_width / 10;
                $max_chars         = 200;
                $token             = strtok($value, "\n");
                while ($token !== false) {
                    if (trim(str_replace(',', '', $token)) != '') {
                        $address_full .= trim($token) . ", ";
                    }
                    $token = strtok("\n");
                }
            }
            $i++;
        }

        $address_full = trim($address_full, ',');
        return $address_full;
    }
	protected function getShippingAddressOrder($order){
		$shippingAddressFlat = '';
		$shippingAddressFlat = implode(',', $this->_formatAddress($order->getShippingAddress()->format('pdf')));
		$shipping_address = array();
		$shipping_address['company'] = $order->getShippingAddress()->getCompany();
		$shipping_address['name'] = $order->getShippingAddress()->getName();
		$shipping_address['firstname'] = $order->getShippingAddress()->getFirstname();
		$shipping_address['lastname'] = $order->getShippingAddress()->getLastname();
		$shipping_address['telephone'] = $order->getShippingAddress()->getTelephone();
		// $shipping_address['email'] = $order->getBillingAddress()->getEmail();
		$i = 0;
		while ($i < 10) {
			if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
				if (isset($shipping_address['street'])) $shipping_address['street'] .= ", \n";
				else $shipping_address['street'] = '';
				$shipping_address['street'] .= $order->getShippingAddress()->getStreet($i);
				$street_key = 'street'.$i;
				$shipping_address[$street_key] = $order->getShippingAddress()->getStreet($i);
			}
			$i++;
		}
		$shipping_address['city'] = $order->getShippingAddress()->getCity();
		$shipping_address['postcode'] = $order->getShippingAddress()->getPostcode();
		$shipping_address['region'] = $order->getShippingAddress()->getRegion();
		$shipping_address['prefix'] = $order->getShippingAddress()->getPrefix();
		$shipping_address['suffix'] = $order->getShippingAddress()->getSuffix();
		$shipping_address['country'] = Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId());
		return $shipping_address;
	}
	protected function getBillingAddressOrder($order){
		$billingAddressFlat = '';
		$billingAddressFlat = implode(',', $this->_formatAddress($order->getBillingAddress()->format('pdf')));
		$billing_address = array();
		$billing_address['company'] = $order->getBillingAddress()->getCompany();
		$billing_address['name'] = $order->getBillingAddress()->getName();
		$billing_address['firstname'] = $order->getBillingAddress()->getFirstname();
		$billing_address['lastname'] = $order->getBillingAddress()->getLastname();
		$billing_address['telephone'] = $order->getBillingAddress()->getTelephone();
		// $shipping_address['email'] = $order->getBillingAddress()->getEmail();
		$i = 0;
		while ($i < 10) {
			if ($order->getBillingAddress()->getStreet($i) && !is_array($order->getBillingAddress()->getStreet($i))) {
				if (isset($billing_address['street'])) $billing_address['street'] .= ", \n";
				else $billing_address['street'] = '';
				$billing_address['street'] .= $order->getBillingAddress()->getStreet($i);
			}
			$i++;
		}
		$billing_address['city'] = $order->getBillingAddress()->getCity();
		$billing_address['postcode'] = $order->getBillingAddress()->getPostcode();
		$billing_address['region'] = $order->getBillingAddress()->getRegion();
		$billing_address['prefix'] = $order->getBillingAddress()->getPrefix();
		$billing_address['suffix'] = $order->getBillingAddress()->getSuffix();
		$billing_address['country'] = Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId());
		return $billing_address;
	}
	protected function getAddressFormatByValue($key, $value, $address_format_set){
		$value = trim($value);
		$if_contents = array();
		$value = preg_replace('~,$~', '', $value);
		$value = str_replace(',,', ',', $value);
		//check key in format address string
		$string_key_check = '{if '.$key.'}';
		$key_flag = strpos($address_format_set,$string_key_check);
		$search  = array($string_key_check,'{/if}');
		$replace = array('','');
		if($key_flag !== FALSE)
		{
			$address_format_set = str_replace($search, $replace, $address_format_set);
		}
		// end check key in format address string
		if ($value != '' && !is_array($value)) {
			$pre_value = '';
			preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);
			if (isset($if_contents[1])) $if_contents[1] = str_replace('{' . $key . '}', $value, $if_contents[1]);
			else $if_contents[1] = '';
			$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $pre_value . $if_contents[1], $address_format_set);
			$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
			$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
		} else {
			$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
			$address_format_set = str_ireplace('{' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
		}
		return $address_format_set;
	}
	protected function addressPrintLine($shippingAddressArray, $black_color, $page, $sku_shipping_address_temp){
		$i = 0;
		$stop_address = FALSE;
		$skip_entry = FALSE;

		foreach ($shippingAddressArray as $i => $value) {
			$value = trim($value);

			$skip_entry = FALSE;
			if (isset($value) && $value != '~') {
				// remove fax
				$value = preg_replace('!<(.*)$!', '', $value);
				if (preg_match('~T:~', $value)) {
					// if($show_phone_yn == 1)
					//                 {
					$value = str_replace('~', '', $value);
					$value = '[ ' . $value . ' ]';
				} elseif ($stop_address === FALSE) {
					if (!isset($shippingAddressArray[($i + 1)]) || preg_match('~T:~', $shippingAddressArray[($i + 1)])) {
						// last line, lets bold it and make it a bit bigger
						$value = str_replace('~', '', $value);
					} else {
						if ((!isset($shippingAddressArray[($i + 2)]) || preg_match('~T:~', $shippingAddressArray[($i + 2)]))) {
							$value = str_replace('~', '', $value);
						} else $value = str_replace('~', ',', $value);
					}
					$page->setFillColor($black_color);
				}
				if ($stop_address === FALSE && $skip_entry === FALSE) $sku_shipping_address_temp .= ',' . $value;
			}
			$i++;
		}
		$sku_shipping_address_temp = str_replace(
			array('  ', ',,', '<br />', '<br/>', "\n", "\p", ',,', ',,', ',', '-'),
			array(' ', ',', '', '', '', '', ',', ',', ', ', ''), $sku_shipping_address_temp);
		$sku_shipping_address_temp = preg_replace('~, $~', '', $sku_shipping_address_temp);
		$sku_shipping_address = preg_replace('~^\s?,\s?~', '', $sku_shipping_address_temp);
		return $sku_shipping_address;
	}
	protected function getMaxCharMessage2($message, $padded_right, $font_size_options, $font_temp, $padded_left){
		$maxWidthPage_message = $padded_right - $padded_left - 10;
		$font_temp_message = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$font_size_compare_message = $font_size_options;
		$line_width_message = $this->parseString($message, $font_temp, $font_size_compare_message);
		$char_width_message = $line_width_message / strlen($message);
		$max_chars_message = round($maxWidthPage_message / $char_width_message);
		return $max_chars_message;
	}
	protected function getItemGiftMessage($item,$max_chars_message){
		$item_message_array = array();
		$_giftMessage = Mage::helper('giftmessage/message')->getGiftMessageForEntity($item);
		if(isset($_giftMessage)){
			$item_message_from = 'From : ' . $_giftMessage->getRecipient();
			$item_message_from = wordwrap($item_message_from, $max_chars_message, "\n");

			$item_message_to = 'Message to : ' . $_giftMessage->getSender();
			$item_message_to = wordwrap($item_message_to, $max_chars_message, "\n");
			$item_message = $_giftMessage->getMessage();
			$item_message = wordwrap($item_message, $max_chars_message, "\n");
			$token = strtok($item_message, "\n");
			$msg_line_count = 2.5;
			if ($token != false) {
				while ($token != false) {
					$gift_msg_array[] = $token;
					$msg_line_count++;
					$token = strtok("\n");
				}
			} else
				$gift_msg_array[] = $item_message;
			$item_message_array[0] = $item_message_from;
			$item_message_array[1] = $item_message_to;
			$item_message_array[2] = $gift_msg_array;
		}
		return $item_message_array;
	}
	protected function getWidthString($message, $font_size){
		$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$line_width_message = $this->parseString($message, $font_temp, $font_size);
		return $line_width_message;
	}
	protected function getItemGiftMessageSeprated($item,$max_chars_message, $message_title_tofrom_yn){
		$item_message_array = array();
		$_giftMessage = Mage::helper('giftmessage/message')->getGiftMessageForEntity($item);
		if(isset($_giftMessage)){
			$item_message_from = 'From : ' . $_giftMessage->getRecipient();
			//$item_message_from = wordwrap($item_message_from, $max_chars_message, "\n");

			$item_message_to = 'To : ' . $_giftMessage->getSender();
			//$item_message_to = wordwrap($item_message_to, $max_chars_message, "\n");
			$item_message = $_giftMessage->getMessage();
			if($message_title_tofrom_yn == 1)
				$item_message = $item_message_to . ' ' . $item_message_from . ' ' . "Message : " . $item_message;
			$item_message = wordwrap($item_message, $max_chars_message, "\n");
			$token = strtok($item_message, "\n");
			$msg_line_count = 2.5;
			if ($token != false) {
				while ($token != false) {
					$gift_msg_array[] = $token;
					$msg_line_count++;
					$token = strtok("\n");
				}
			} else
				$gift_msg_array[] = $item_message;
			//$item_message_array[0] = $item_message_from;
			//$item_message_array[1] = $item_message_to;
			$item_message_array = $gift_msg_array;
		}
		return $item_message_array;
	}
	protected function getMaxCharMessage($padded_right, $font_size_options, $font_temp, $padded_left=30){
		$maxWidthPage_message = $padded_right - $padded_left;
		$font_temp_message = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$font_size_compare_message = $font_size_options;
		$line_width_message = $this->parseString('12345abcde', $font_temp, $font_size_compare_message);
		$char_width_message = $line_width_message / 10;
		$max_chars_message = round($maxWidthPage_message / $char_width_message);
		return $max_chars_message;
	}
	protected function showToFrom($message_title_tofrom_yn, $to_from, $msgX, $y, $to_from_from, $font_size_gift_message, $page){
		if($message_title_tofrom_yn ==1)
			{
				$page->drawText(Mage::helper('sales')->__($to_from), ($msgX), $y, 'UTF-8');
				$y -= ($font_size_gift_message + 3);
				if (isset($to_from_from) && ($to_from_from != '')) {

					$page->drawText(Mage::helper('sales')->__($to_from_from), ($msgX), $y, 'UTF-8');
					$y -= ($font_size_gift_message + 3);
				}


			}
		return $y;
	}
		/*Funtion for show qty*/
	protected function getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array){
		// Add order gift message with gift wrap info
		$gift_message_info = array();
		$gift_message = '';
		$gift_sender = '';
		$gift_recipient = '';
		if ($gift_message_yn != 'no' && !is_null($gift_message_id)) {
			// normal gift message
			$gift_message_item->load((int)$gift_message_id);
			$gift_sender = $gift_message_item->getData('sender');
			$gift_recipient = $gift_message_item->getData('recipient');
			$gift_message = $gift_message_item->getData('message');
		}

		if (isset($giftWrap_info['message']) && $giftWrap_info['message'] != NULL) {
			if ($gift_message != '') $gift_message .= "\n";
			$gift_message .= $giftWrap_info['message'];
		}

		// add product gift message and history ebay note to order message

		$gift_message_combined = '';
		if(isset($gift_message_array['notes']))
			foreach ($gift_message_array['notes'] as $k => $v)
			{
				$gift_message.='\n'.$v;
			}

		if(isset($gift_message_array['items']))
			foreach ($gift_message_array['items'] as $item_key => $item_message)
			{
				if(isset($item_message['printed'])){
					if($item_message['printed'] == 0)
					{   if(is_array($item_message['message-content'])){
							foreach($item_message['message-content'] as $k2=>$v2)
								$gift_message.="\n".$v2;
						}
					}
				}
			}
		$gift_message_info[0] = $gift_message;
		$gift_message_info[1] = $gift_sender;
		$gift_message_info[2] = $gift_recipient;
		return $gift_message_info;
	}
	protected function createMsgArray($gift_message){
		$character_message_breakpoint = 96;
		$gift_message = wordwrap($gift_message, 96, "\n", false);
		$gift_msg_array = array();
		// wordwrap characters
		$token = strtok($gift_message, "\n");
		// $y = 740;
		$msg_line_count = 2.5;
		while ($token != false) {
			$gift_msg_array[] = $token;
			$msg_line_count++;
			$token = strtok("\n");
		}
		return $gift_msg_array;
	}
	protected function drawOrderGiftMessage($gift_msg_array, $msgX, $font_size_gift_message, $y, $page){
		foreach ($gift_msg_array as $gift_msg_line) {
			$page->drawText(trim($gift_msg_line), $msgX, $y, 'UTF-8');
			$y -= ($font_size_gift_message + 3);
		}
		return $y;
	}
	protected function formatPriceTxt($order, $price){
		if (!is_numeric($price)) {
            $price = Mage::app()->getLocale()->getNumber($price);
        }
		$price = $order->formatPriceTxt($price);
		return $price;
	}
	protected function createArraySort($sort_packing,$product_build, $sku,$product_id, $trim_names_yn){
		if ($sort_packing != 'none' && $sort_packing != '') {
			$product_build[$sku][$sort_packing] = '';
			$attributeName = $sort_packing;

			if ($attributeName == 'Mcategory') {
				$product_build[$sku][$sort_packing] = $product_build[$sku]['%category%']; //$category_label;
			} elseif ($sort_packing == 'sku') {
				$product_build[$sku][$sort_packing] = $sku;
			} else {
                $product = Mage::helper('pickpack')->getProduct($product_id);
				if ($product->getData($attributeName)) {

                    $attributeValue = $product->getData($attributeName);
                    $attribute = $product->getResource()->getAttribute($attributeName);
                    if ($attribute->usesSource()) {
                        $return_value = $product->getAttributeText($attributeName, $attributeValue);
                    } else {
                        $return_value = $attributeValue;
                    }

                    $product_build[$sku][$sort_packing] = $return_value;
				}
			}
			unset($attributeName);
			unset($attribute);
			unset($attributeOptions);
			unset($result);
			return $product_build[$sku][$sort_packing];
		}
	}
	protected function sortMultiDimensional(&$array, $subkey, $subkey2, $sortorder_packing_bool=false, $sortorder_packing_secondary_bool=false){
		foreach ($array as $key => $row) {
			$array1[$key]  = $row[$subkey];
			$array2[$key] = $row[$subkey2];
		}
		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		if($sortorder_packing_bool) $sortorder_packing_bool = SORT_ASC;
		else $sortorder_packing_bool = SORT_DESC;

		if($sortorder_packing_secondary_bool) $sortorder_packing_secondary_bool = SORT_ASC;
		else $sortorder_packing_secondary_bool = SORT_DESC;
		array_multisort($array1,$sortorder_packing_bool ,$array2, $sortorder_packing_secondary_bool , $array);
	}
	protected function _getTruncatedComment($comment)
    {
		$comment = str_replace('<br />','~',nl2br(trim($comment)));
		// Strip HTML Tags
		$comment = strip_tags($comment);
		// Clean up things like &amp;
		$comment = html_entity_decode($comment);
		// Strip out any url-encoded stuff
		$comment = urldecode($comment);
		// Replace non-AlNum characters with space
		$comment = preg_replace('/[^@A-Za-z0-9\.\,~:\-]/', ' ', $comment);

		$comment = str_ireplace(array('M2E Pro Notes:','','Checkout Message From '),'',$comment);
		$comment = preg_replace('/Because the Order currency is different (.*)$/i','',$comment);

		// uncomment for rates comments
			// $comment = str_ireplace(array('M2E Pro Notes:','Because the Order currency is different from the Store currency','the conversion from ','as a rate','Checkout Message From '),'',$comment);
			// $comment = str_replace(' was performed~  using','@',$comment);

		// Replace Multiple spaces with single space
		$comment = preg_replace('/ +/', ' ', $comment);
		// Trim the string of leading/trailing space
		$comment = trim($comment);
		$comment = preg_replace('/[ \,@\;~]$/', '', $comment);
		$comment = preg_replace('/ \.$/', '', $comment);
		$comment = preg_replace('/^[~\s\,\.\;~]+/', '', $comment);
		$comment = str_replace(array('~~~','~~','~~','~'),'~',$comment);

		// if($length == 'trim')
		// {
	        // $truncate_at = Mage::getStoreConfig(self::XML_PATH_TRUNCATE);
			// if($truncate_at < 5) $truncate_at = 5;
			// if ($truncate_at < strlen($comment)) {

	                // $comment = trim(substr($comment, 0, $truncate_at)). '&hellip;';
					// $comment = str_replace('~','<br />',$comment);
					// return $comment;
	            // }
		// }
		$comment = str_replace('~','&#10;',$comment); //&#13;
		$comment = preg_replace('/Buyer:\s?$/i','',$comment); //&#13;
		$comment = preg_replace('/&#10;\s?$/i','',$comment); //&#13;

		return trim($comment);
    }
    protected function getOptionProductByStore($store_view, $helper, $product_id, $storeId, $specific_store_id, $options, $i){
        $config    = Mage::getModel('eav/config');
        $options_store = array();
        if ($store_view == "storeview") {
            $_newProduct = $helper->getProductForStore($product_id, $storeId, $specific_store_id);
            $_newOption = $_newProduct->getOptionById($options['options'][$i]['option_id']);
            if(is_object($_newOption)){
                $options['options'][$i]['label']  = $_newOption->getTitle();
                /*if($options['options'][$i]['option_type'] != "field" && $options['options'][$i]['option_type'] != "area")
                    $options['options'][$i]['value'] = $options['options'][$i]['option_value'];*/
            }else{
                $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $options['options'][$i]['label']);

                if($attribute->getStoreLabels()){
                    $label_ar = $attribute->getStoreLabels();
                    $options['options'][$i]['label'] = $label_ar[$storeId];
                }
                else{
                    $label_ar = $attribute->getData('attribute_code');
                    $options['options'][$i]['label'] = $label_ar;
                }

                $option_id = $attribute->getSource()->getOptionId($options['options'][$i]['value']);
                $value_ar = $attribute->setStoreId($storeId)->getSource()->getAllOptions();

                foreach ($value_ar as $key => $value) {
                    if($value["value"] == $option_id && $option_id != "")
                        $options['options'][$i]['value'] = $value["label"];
                }
            }

        }
        if($store_view == "specificstore" && $specific_store_id != "") {
            $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
            $_newOption = $_newProduct->getOptionById($options['options'][$i]['option_id']);
            if(is_object($_newOption)){
                $options['options'][$i]['label']  = $_newOption->getTitle();
                if($options['options'][$i]['option_type'] != "field" && $options['options'][$i]['option_type'] != "area")
                    $options['options'][$i]['value']  = $_newOption->getValueById($options['options'][$i]['option_value'])->getTitle();
            }else{

                $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $options['options'][$i]['label']);

                if($attribute->getStoreLabels()){
                    $label_ar = $attribute->getStoreLabels();
                    $options['options'][$i]['label'] = $label_ar[$specific_store_id];
                }
                else{
                    $label_ar = $attribute->getData('attribute_code');
                    $options['options'][$i]['label'] = $label_ar;
                }

                $option_id = $attribute->getSource()->getOptionId($options['options'][$i]['value']);
                $value_ar = $attribute->setStoreId($specific_store_id)->getSource()->getAllOptions();

                foreach ($value_ar as $key => $value) {
                    if($value["value"] == $option_id && $option_id != "")
                        $options['options'][$i]['value'] = $value["label"];
                }
            }
        }

        $options_store["label"] = $options['options'][$i]['label'];
        $options_store["value"] = $options['options'][$i]['value'];
        return $options_store;
    }
    protected function _getProductFromItem($item) {
        $helper = Mage::helper('pickpack');
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $item->getHasChildren()) {
            $children = $item->getChildrenItems();
            $child = $children[0];
            if ($child) {
                $product = $helper->getProduct($child->getProductId());
            } else {
                $product = $helper->getProduct($item->getProductId());
            }
        } else {
            $product = $helper->getProduct($item->getProductId());
        }

        return $product;
    }
    protected function printBackGroundImage($page, $store_id, $page_background_image_yn, $page_top, $full_page_width, $page_background_position, $sub_folder, $option_group, $suffix_group, $x1, $y2, $page_background_nudge,$page_background_resize){
        // require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Zebra_Image.php';
        // $image_zebra = new Zebra_Image();


        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
        $image_simple = new SimpleImage();

        if ($page_background_image_yn == 1) {
            $filename = Mage::getStoreConfig('pickpack_options/' . $option_group . '/' . $suffix_group, $store_id);
            $helper = Mage::helper('pickpack');
            if ($filename) {
                $image_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $filename;
                if (is_file($image_path)) {
                    $image_file_name = $image_path;
                    $imageObj = $helper->getImageObj($image_path);
                    $orig_img_width = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_width = $orig_img_width;
                    $img_height = $orig_img_height;
                    /*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/
                    if ($orig_img_width > $full_page_width) {
                        $img_height = ceil(($full_page_width / $orig_img_width) * $orig_img_height);
                        $img_width = $full_page_width;
                    }
                    else
                        if ($orig_img_height > $page_top) {
                            $temp_var = $page_top / $orig_img_height;
                            $img_height = $page_top;
                            $img_width = $temp_var * $orig_img_width;
                        }
                    if($page_background_resize == 'low'){
                        $img_width = $img_width * 72/300;
                        $img_height = $img_height * 72/300;
                    }
                    if($page_background_position == 'topleft'){
                        $y2 += 10;
                    }elseif($page_background_position == 'center_page'){
                        $x1 = ($full_page_width - $img_width) / 2;
                        $y2 = ($page_top + 10 - $img_height) / 2 + $img_height;
                    }else{
                        $x1 = ($full_page_width - $img_width) / 2;
                        if($page_background_resize == 'high')
                            $y2 = ($page_top - 200);
                        else
                            $y2 = ($page_top - 350);
                    }
                    $x1 = $x1 + $page_background_nudge[0];
                    $y2 = $y2 + $page_background_nudge[1];
                    $y1 = ($y2 - $img_height);
                    $x2 = ($x1 + $img_width);
                    $image_ext = '';
                    $temp_array_image = explode('.', $image_path);
                    $option_group_folder = str_replace('/','',$option_group);
                    $suffix_group_folder = str_replace('/','',$suffix_group);

                    $image_ext = array_pop($temp_array_image);
                    if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
                        if(isset($image_simple))
                        {
                            //Create new temp image
                            $final_image_path2 = $image_file_name;//$media_path . '/' . $image_url_after_media_path;
                             $image_source = $final_image_path2;
                            $io = new Varien_Io_File();
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage'.DS.$option_group_folder.DS.$suffix_group_folder.DS.'default');
                            $ext = substr($image_source, strrpos($image_source, '.') + 1);
                            $filename = str_replace($ext,'jpeg', $filename);
                            $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$filename;
                            if(($orig_img_width > $img_width*300/72) || ($orig_img_height > $img_height*300/72))
                            {
                                if(!(file_exists($image_target)))
                                {
                                    $size_1 = $img_width*300/72;
                                    $size_2 = $img_height*300/72;
                                    $image_simple->load($image_source);
                                    $image_simple->resize($size_1,$size_2);
                                    // if($image_ext == 'png')
                                    //     $image_type = IMAGETYPE_PNG;
                                    // else
                                    //     $image_type=IMAGETYPE_JPEG;
                                    $image_simple->save($image_target);
                                }

                                $image_path = $image_target;
                            }

                        }

                        $background = Zend_Pdf_Image::imageWithPath($image_path);
                        $page->drawImage($background, $x1, $y1, $x2, $y2);
                        unset($background);
                        unset($filename);
                        unset($image_path);
                    }
                }
            }
        }
        unset($image_zebra);
    }
	protected function printHeaderLogo($page,$store_id, $show_top_logo_yn, $page_top, $_logo_maxdimensions, $sub_folder, $option_group, $suffix_group, $x1 = 27, &$y2){
		// require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Zebra_Image.php';
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
		// $image_zebra = new Zebra_Image();
        $image_simple = new SimpleImage();


		/***************************PRINTING 1 HEADER LOGO *******************************/
		$minY_logo = $page_top; //$this->_printing_format['page_top'];
		if ($show_top_logo_yn == 1) {
			//$sub_folder = 'logo_product_separated';
			//$option_group = 'product_separated';
			/*************************** PRINT HEADER LOGO *******************************/

			$packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . $suffix_group, $store_id);
			$helper = Mage::helper('pickpack');
			if ($packlogo_filename) {

				$packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
				if (is_file($packlogo_path)) {
					$img_width = $_logo_maxdimensions[0];
					$img_height = $_logo_maxdimensions[1];

					$imageObj = $helper->getImageObj($packlogo_path);
					$orig_img_width = $imageObj->getOriginalWidth();
					$orig_img_height = $imageObj->getOriginalHeight();

					$img_width = $orig_img_width;
					$img_height = $orig_img_height;

					/*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/
					if ($orig_img_width > $_logo_maxdimensions[0]) {
						$img_height = ceil(($_logo_maxdimensions[0] / $orig_img_width) * $orig_img_height);
						$img_width = $_logo_maxdimensions[0];
					} //Fix for auto height --> Need it?
					else
						if($_logo_maxdimensions[2] && $_logo_maxdimensions[2] != 'fullwidth'){
                            if ($orig_img_height > $_logo_maxdimensions[1]) {
                                $temp_var = $_logo_maxdimensions[1] / $orig_img_height;
                                $img_height = $_logo_maxdimensions[1];
                                $img_width = $temp_var * $orig_img_width;
                            }
                        }

                    $y2 += 10;
					$y1 = ($y2 - $img_height);

					$x2 = ($x1 + $img_width);
					$image_ext = '';
					$temp_array_image = explode('.', $packlogo_path);
					$option_group_folder = str_replace('/','',$option_group);
					$suffix_group_folder = str_replace('/','',$suffix_group);

					$image_ext = array_pop($temp_array_image);
					if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
						if(isset($image_simple))
						{
							$final_image_path2 = $packlogo_path;
							// $image_zebra->source_path = $final_image_path2;
                            $image_source = $final_image_path2;
							$io = new Varien_Io_File();
							$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
							$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage'.DS.$option_group_folder.DS.$suffix_group_folder.DS.'default');
							$ext = substr($image_source, strrpos($image_source, '.') + 1);
							// $image_zebra->target_path = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$packlogo_filename;
                            $image_source = $final_image_path2;
                            $packlogo_filename = str_replace($ext,'jpeg', $packlogo_filename);
                            $image_target= Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$packlogo_filename;
							if (!file_exists(dirname($image_target))) {
                                mkdir(dirname($image_target), 0777, true);
                            }
                            if(($orig_img_width > $img_width*300/72) || ($orig_img_height > $img_height*300/72))
							{
                                if(!(file_exists($image_target)))
                                {
								$size_1 = $img_width*360/72;
								$size_2 = $img_height*360/72;
                                    $image_simple->load($image_source);
                                    $image_simple->resize($size_1,$size_2);
                                    // if($image_ext == 'png')
                                    //     $image_type = IMAGETYPE_PNG;
                                    // else
                                    //     $image_type=IMAGETYPE_JPEG;
                                    $image_simple->save($image_target);
                                }
								$packlogo_path = $image_target;

								}

							}

						$packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
						$page->drawImage($packlogo, $x1, $y1, $x2, $y2);
						unset($packlogo);
						unset($packlogo_filename);
						unset($packlogo_path);
					}
					$minY_logo = $y1 - 20;
				}
			}

			/*************************** END PRINT HEADER LOGO ***************************/
		}
		unset($image_zebra);
		return $minY_logo;
		//return $this->y;
	}

	protected function getEbayOption($order, $sku , $productId){
		$ebay_option_item = array();
		$collection_order = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
		$collection_order->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
		$order_id = '';
		foreach($collection_order as $ebay_order){
			$order_id = $ebay_order->getData("order_id");
		}
		$collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $order_id)
			->addFieldToFilter('sku', $sku);
		$items = $collection->getData();
		foreach($items as $item){
            if($productId != $item['product_id'])
                continue;
			$variation_details = json_decode($item['variation_details'], true);
            if(isset($variation_details["options"])){
    			$options = $variation_details["options"];
    			foreach($options as $key=>$value){
    				$option_array['label'] = $key;
    				$option_array['value'] = $value;
    			}
    			$ebay_option_item[] = $option_array;
    			unset($options);
    			unset($variation_details);
    			unset($option_array);
            }
		}
		return $ebay_option_item;
	}

    protected function getArrayShippingAddress($shipping_address, $capitalize_label_yn, $address_format_set){
		$if_contents = array();
		foreach ($shipping_address as $key => $value) {
			$value = trim($value);
			if (($capitalize_label_yn == 1) && ($key != 'postcode') && ($key != 'region_code') && ($key != 'region')) {
                // $value = strtoupper($value);
                $value = ucfirst($value);
                $value = Mage::helper('pickpack/functions')->ucwords_specific( mb_strtolower($value, 'UTF-8'), "-'");
			} else
				if ($capitalize_label_yn == 2) {
					$value = strtoupper($value);
                    $value = $this->capitalAddress($value);
				}
			$value = str_replace(array(',,', ', ,', ', ,'), ',', $value);
            $value = str_replace(array('N/a', 'n/a', 'N/A'), '', $value);
			$value = trim(preg_replace('~\-$~', '', $value));
			//check key in format address string
			$string_key_check = '{if ' . $key . '}';
			$key_flag = strpos($address_format_set, $string_key_check);
			$search = array($string_key_check, '{/if}');
			$replace = array('', '');
			if ($key_flag !== FALSE) {
				$address_format_set = str_replace($search, $replace, $address_format_set);
			}


			// end check key in format address string

			if ($value != '' && !is_array($value)) {
				$pre_value = '';
				preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);

				if (isset($if_contents[1])) {
					$if_contents[1] = str_replace('{' . $key . '}', $value, $if_contents[1]);
				} else $if_contents[1] = '';

				$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $if_contents[1], $address_format_set);
				$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
				$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
			} else {
				$pre_value = '';
				$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
				$address_format_set = str_replace('{' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
				$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
                //$address_format_set = str_ireplace(', ', '', $address_format_set);
			}

			$from_date = "{if telephone}";
			$end_date = "{telephone}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}

			$from_date = "{if fax}";
			$end_date = "{fax}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}

			$from_date = "{if vat_id}";
			$end_date = "{vat_id}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}
		}
		$address_format_set = trim(str_replace(array('||', '|'), "\n", trim($address_format_set)));
        $address_format_set = str_replace("\n\n", "\n", $address_format_set);
        $address_format_set = str_replace("  ", " ", $address_format_set);
        $address_format_set = trim(ltrim($address_format_set,','));
		return $address_format_set;
	}
	protected function getAddressLines($shippingAddressArray, $show_this_shipping_line){
		$ship_i = 0;
		foreach ($shippingAddressArray as $key => $value) {

			$value = trim($value);
			$value = preg_replace('~^,$~', '', $value);
			$value = str_replace(',,', ',', $value);
			$value = str_ireplace(array('{if street}', '{street}', '{/if street}', '{if street1}', '{street1}', '{/if street1}', '{if street2}', '{street2}', '{/if street2}', '{if street3}', '{street3}', '{/if street3}', '{if street4}', '{street4}', '{/if street4}', '{if street5}', '{street5}', '{/if street5}', '{if street6}', '{street6}', '{/if street6}', '{if street7}', '{street7}', '{/if street7}', '{if street8}', '{street8}', '{/if street8}', '{if city}', '{city}', '{/if city}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $value);
			if ($value != '') {
				$show_this_shipping_line[$ship_i] = $value;
				$ship_i++;
			}
		}
		return $show_this_shipping_line;
	}

    protected function printHeader(&$page, $store_id)
    {
     if($this->_printing_format['page_logo'] == 1){
         //$minY_logo = $this->printHeaderLogo($page, $store_id, $this->_printing_format['page_logo']);
         $sub_folder = 'logo_product_separated';
         $option_group = 'product_separated';
         $suffix_group = '/product_separated_logo';
         $minY_logo = $this->printHeaderLogo($page, $store_id, $this->_printing_format['page_logo'], $this->_printing_format['page_top'], $this->_logo_maxdimensions, $sub_folder, $option_group, $suffix_group);
         $this->_setFont($page, $this->_printing_format['font_style_header'], $this->_printing_format['font_size_header'], $this->_printing_format['font_family_header'], $this->_printing_format['non_standard_characters'], $this->_printing_format['font_color_header']);
         $page->drawText($this->_helper->__($this->_printing_format['page_title']), 325, $this->y, 'UTF-8');
         $this->y -= $this->_printing_format['font_size_body'];
         $page->setFillColor($this->_printing_format['font_color_header_zend']);
         $page->setLineColor($this->_printing_format['font_color_header_zend']);
         $page->setLineWidth(0.5);
         $page->drawRectangle(325, $this->y, $this->_printing_format['padded_right'], ($this->y - 1));
         $this->y -= 20;
         //Print printed date
         if($this->_getConfig('pickpack_pickprint',1, false, 'product_separated', $store_id) == 1){
             $this->_setFont($page, 'regular', $this->_printing_format['font_size_body'] + 2, $this->_printing_format['font_family_body'], $this->_printing_format['non_standard_characters'], $this->_printing_format['font_color_subtitles']);
             $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
             $printed_date = date($this->_printing_format['date_format'], $currentTimestamp);
             $page->drawText('Date:    '.$printed_date, 325, $this->y, 'UTF-8');
             $this->y -= 20;
         }
         if($minY_logo < $this->y) $this->y = $minY_logo;
     }
     else{
            $this->_setFont($page, $this->_printing_format['font_style_header'], $this->_printing_format['font_size_header'], $this->_printing_format['font_family_header'], $this->_printing_format['non_standard_characters'], $this->_printing_format['font_color_header']);
            $page->drawText($this->_helper->__($this->_printing_format['page_title']), 20, $this->y, 'UTF-8');
            $this->y -= $this->_printing_format['font_size_body'];
            $page->setFillColor($this->_printing_format['font_color_header_zend']);
            $page->setLineColor($this->_printing_format['font_color_header_zend']);
            $page->setLineWidth(0.5);
            $page->drawRectangle(17, $this->y, $this->_printing_format['padded_right'], ($this->y - 1));
            $this->y -= 20;
            //Print printed date
            if($this->_getConfig('pickpack_pickprint',1, false, 'product_separated', $store_id) == 1){
                $this->_setFont($page, 'regular', $this->_printing_format['font_size_body'] + 2, $this->_printing_format['font_family_body'], $this->_printing_format['non_standard_characters'], $this->_printing_format['font_color_subtitles']);
                $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
                $printed_date = date($this->_printing_format['date_format'], $currentTimestamp);
                $page->drawText('Date:    '.$printed_date, 20, $this->y, 'UTF-8');
                $this->y -= 20;
            }
     }
    }


    protected function groupOptionProduct($options_splits){
        $group_options = array();
        $group_options_temp = array();
        $group_options_f = array();
        foreach ($options_splits as $key => $options_split) {
            $temp_str1 = substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split));
            $temp_str1 = str_replace('qty_ordered', '', $temp_str1);
            $temp_str2 = substr($options_split, 0, strpos($options_split, 'qty_ordered'));
            if(isset($group_options[$temp_str2])){
                $group_options[$temp_str2] = ($temp_str1 + $group_options[$temp_str2]) . ' x';

            }else{
                    $group_options[$temp_str2] = $temp_str1;
                    $group_options_temp[] = $temp_str2;
                }
        }
        $group_options_f = $this->naturalSort($group_options, $group_options_temp);
        return $group_options_f;
    }
    protected function naturalSort($group_options, $group_options_temp){
        $group_options_f = array();
        natcasesort($group_options_temp);
        foreach ($group_options_temp as $key => $value) {
            $group_options_f[$value] = $group_options[$value];
        }
        return $group_options_f;
    }
    protected function printRepeatGiftMessage($page,$order, $gift_message_array, $background_color_comments,$font_style_comments, $font_family_comments, $font_size_comments, $font_color_comments, $positional_remessage_box_fixed_position, $positional_message_box_fixed_position_demension_x, $giftWrap_info, $gift_message_item, $background_color_gift_message, $gift_message_id){
        $gift_msg_pro_array = array();
        $gift_msg_array = array();
        if(isset($gift_message_array['items']) && ($gift_message_combined = $this->getProductGiftMessage($gift_message_array))){

            $gift_msg_pro_array = $this->createMsgArray2($gift_message_combined, $positional_message_box_fixed_position_demension_x, $font_size_comments, $font_family_comments);
        }
        $gift_message = '';
        if((!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL)){
            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $giftWrap_info, $gift_msg_array);
            $gift_sender = $gift_msg_array[1];
            $gift_recipient = $gift_msg_array[2];
            $gift_message = $gift_msg_array[0];
        }
        //TODO gift registry
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
            $message_character_breakpoint = $positional_message_box_fixed_position_demension_x;
            $gift_message = wordwrap($gift_message, $message_character_breakpoint, "\n", false);
            $gift_msg_array = $this->createMsgArray2($gift_message, $positional_message_box_fixed_position_demension_x, $font_size_comments, $font_family_comments);
        }
        $gift_msg_combined = array_merge($gift_msg_pro_array, $gift_msg_array);

        if($gift_msg_combined != null){
            $y_repeat = $positional_remessage_box_fixed_position[1];
            $left_bg_gift_msg = $msgX_repeat = $positional_remessage_box_fixed_position[0];
            $background_color_temp = $background_color_comments;
            $font_style_temp = $font_style_comments;
            $font_family_temp = $font_family_comments;
            $font_size_temp = $font_size_comments;
            $font_color_temp = $font_color_comments;
            $right_bg_gift_msg = $positional_remessage_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
            $top_bg_gift_msg = $positional_remessage_box_fixed_position[1] + $font_size_temp;
            $msg_line_count = count($gift_msg_combined);
            $bottom_bg_gift_msg = $top_bg_gift_msg - $msg_line_count * ($font_size_temp + 1) - $font_size_temp * 0.5;

            $this->drawBackgroundGiftMessage($background_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
            $this->_setFont($page, $font_style_temp, ($font_size_temp - 1), $font_family_temp, $this->_general['non_standard_characters'], $font_color_temp);
            $this->drawOrderGiftMessage($gift_msg_combined, $msgX_repeat + $font_size_temp / 3, $font_size_temp, $positional_remessage_box_fixed_position[1], $page);
        }
    }
    
   // define('LATIN1_UC_CHARS', 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ');
//    define('LATIN1_LC_CHARS', 'àáâãäåæçèéêëìíîïðñòóôõöøùúûüý');
//    function mb_strtoupper($str) {
// 	   if (is_array($str)) $str = $str[0];
// 	   return strtoupper(strtr($str, LATIN1_LC_CHARS, LATIN1_UC_CHARS));
//    }
//    function mb_strtolower($str) {
// 	   if (is_array($str)) $str = $str[0];
// 	   return strtolower(strtr($str, LATIN1_UC_CHARS, LATIN1_LC_CHARS));
//    }
//    define('MB_CASE_LOWER', 1);
//    define('MB_CASE_UPPER', 2);
//    define('MB_CASE_TITLE', 3);
//    function mb_convert_case($str, $mode) {
// 	   // XXX: Techincally the calls to strto...() will fail if the
// 	   //      char is not a single-byte char
// 	   switch ($mode) {
// 	   case MB_CASE_LOWER:
// 		   return preg_replace_callback('/\p{Lu}+/u', 'mb_strtolower', $str);
// 	   case MB_CASE_UPPER:
// 		   return preg_replace_callback('/\p{Ll}+/u', 'mb_strtoupper', $str);
// 	   case MB_CASE_TITLE:
// 		   return preg_replace_callback('/\b\p{Ll}/u', 'mb_strtoupper', $str);
// 	   }
//    }
    protected function capitalAddress($str){
       
        $str = strtoupper(strtr($str, LATIN1_LC_CHARS, LATIN1_UC_CHARS));
        return strtr($str, array("ß" => "SS"));
    }
    
    public function convertCurrency($price, $from, $to)
    {
        if ($from == $to) {
            return $price;
        }

        $from = Mage::getModel('directory/currency')->load($from);
        $to = Mage::getModel('directory/currency')->load($to);

        if ($rate = $from->getRate($to)) {
            return $price*$rate;
        } else if ($rate = $to->getRate($from)) {
            return $price / $rate;
        } else {
            throw new Exception(Mage::helper('directory')->__('Undefined rate from "%s-%s".', $from->getCode(), $to->getCode()));
        }
    }

    protected function getSkuBarcode($sku, $product_id, $store_id)
    {
        $barcode_array = array();
        $config_group = 'messages';
        $new_product_barcode = '';
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_1', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_2', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_3', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_4', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_5', '', false, $config_group, $store_id);
        $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_spacer', '', false, $config_group, $store_id);
        if ($product_sku_barcode_spacer != '') {
            $barcode_array['spacer'] = $product_sku_barcode_spacer;
        } else
            $barcode_array['spacer'] = '';
        foreach ($product_sku_barcode_attributes as $product_sku_barcode_attribute)
            $new_product_barcode = $this->getSkuBarcodeByAttribute($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $sku, $product_id);
        return $new_product_barcode;
    }
    
    protected function getSkuBarcodeByAttribute($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $sku, $product_id)
    {
        if ($product_sku_barcode_attribute != '') {
            switch ($product_sku_barcode_attribute) {
                case 'sku':
                    $barcode_array[$product_sku_barcode_attribute] = $sku;
                    break;
                case 'product_id':
                    $barcode_array[$product_sku_barcode_attribute] = $product_id;
                    break;
                default:
                    $attributeName = $product_sku_barcode_attribute;
                    $product = Mage::helper('pickpack')->getProduct($product_id);
                    if ($product->getData($attributeName)) {
                        $barcode_array[$product_sku_barcode_attribute] = $this->getProductAttributeValue($product, $attributeName);
                    } else {
                        $barcode_array[$product_sku_barcode_attribute] = '';
                    }
                    break;
            }
            $new_product_barcode = $new_product_barcode . $barcode_array[$product_sku_barcode_attribute] . $barcode_array['spacer'];
        }
        return $new_product_barcode;
    }
    
     public function getOrderDescription($order,$description_code)
    {
		// Description:%description%;
		// ^ same as %description_products%
		// List product names, separated by |
		// Include each once only.
		// //
		// Description:%description_category%;
		// //^ same as %description_categories%
		// List product category names, separated by |
		// Include each once only.
		// //
		// Description:%description_qty%;
		// //^ same as %description_products_qty%
		// List product names, separated by |
		// Include each once only, with qty prefix *eg. 2 x White shirt
		// 
		// Description:%description_category_qty%;
		// ^ same as %description_categories_qty%
		// List product category names, separated by |
		// Include each once only, with qty prefix *eg. 2 x Shirt
		$description_detail = array();
		$itemsCollection = $order->getAllVisibleItems();
			$store_id = $order->getStore()->getId();
			foreach($itemsCollection as $item)
			{
				if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
					$configurable_id = $item->getProductId();
					$sku = $item->getProductOptionByCode('simple_sku');
					$product_id = Mage::getModel('catalog/product')->setStoreId($store_id)->getIdBySku($sku);
				} else {
					$sku = $item->getSku();
					$product_id = $item->getProductId(); // get it's ID

				}
				$product_name = $item->getName();
				$product_sku = $sku;
				$product_qty = round($item->getQtyOrdered(),2);
				if($description_code=='description')
					$description_detail[] = $product_name;
				else
					if($description_code=='description_qty')
						$description_detail[] = $product_qty.' x '.$product_name;
					else
						if($description_code=='description_categories')
						{
							$product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id);
							$product_categories = $this->getProductCategories($product);
							$description_detail[] =$product_categories;
						}

			}

		return implode('|',$description_detail);

    }

    public function getProductCategories($product)
    {
        $catCollection = $product->getCategoryCollection();
        $categs = $catCollection->exportToArray();
        $categsToLinks = array();
        foreach ($categs as $cat) {
            $categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
        }
        $category_label = '';
        foreach ($categsToLinks as $ind => $cat) {
            if (isset($category_map[strtolower($cat)])) $cat = $category_map[strtolower($cat)];
            if (!empty($category_label)) $category_label = $category_label . ', ' . $cat;
            else $category_label = $cat;
        }
        return $category_label;
    }


    public function getProductWebsites($product)
    {
        $websiteIds = $product->getWebsiteIds();
        $website_name = "";
        foreach ($websiteIds as $key => $websiteId) {
            $website_name = $website_name . Mage::app()->getWebsite($websiteId)->getName() . ",";
        }
        $website_name = trim($website_name, ',');
        return $website_name;
    }

    public function getProductStores($product)
    {
        $storeIds = $product->getStoreIds();
        $store_name = '';
        foreach ($storeIds as $key => $storeId) {
            $store_name = $store_name . Mage::app()->getStore($storeId)->getName() . ',';
        }
        $store_name = trim($store_name, ',');
        return $store_name;
    }

    public function printBottomOrderId($order,$page,$page_top,$padded_right,$font_style_body,$font_size_body,$font_family_body,$font_color_body,$font_size_shipaddress,$store_id){
        if (($this->_bottom_shipping_address_yn == 1)&&($this->_bottom_shipping_address_id_yn == 1)) {
            if($this->_case_rotate > 0)
                $this->rotateLabel($this->_case_rotate,$page,$page_top,$padded_right,$this->_nudge_rotate_address_label);
            $this->_setFont($page, $font_style_body, ($font_size_body + 0.5), $font_family_body, $this->_non_standard_characters, $font_color_body);
            $bottom_order_id_nudge = explode(",", $this->_getConfig('pickpack_nudge_id_bottom_shipping_address', '0, 0', true, $this->_wonder, $store_id));
            if (!isset($bottom_order_id_nudge[1]))
                $bottom_order_id_nudge[1] = 0;
            $page->drawText('#' . $order->getRealOrderId(), $this->_addressFooterXY[0] + $bottom_order_id_nudge[0], $bottom_order_id_nudge[1] + $this->_addressFooterXY[1], 'UTF-8');
            $minY[] = ($this->_addressFooterXY[1] + ($font_size_shipaddress)) - 7;
            unset($bottom_order_id_nudge);
            if($this->_case_rotate > 0)
                $this->reRotateLabel($this->_case_rotate,$page,$page_top,$padded_right,$this->_nudge_rotate_address_label);
        }
    }

    public function setGlobalPageConfig($store_id){
        $this->_bottom_shipping_address_id_yn = $this->_getConfig('pickpack_bottom_shipping_address_id_yn', 0, false, $this->_wonder, $store_id);
        $this->_case_rotate = $this->_getConfig('case_rotate_address_label',0, false, $this->_wonder, $store_id);
        $this->_non_standard_characters = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);
        $this->_nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $this->_wonder, $store_id));
        $this->_addressFooterXY = explode(",", $this->_getConfig('pickpack_shipaddress', $this->_addressFooterXYDefault, true, $this->_wonder, $store_id));
        $this->_bottom_shipping_address_yn = $this->_getConfig('pickpack_bottom_shipping_address_yn', 0, false, $this->_wonder, $store_id);
    }

    public function setGeneralConfig($store_id = null){
        if ($store_id === null){
            $store_id = Mage::app()->getStore()->getStoreId();
        }
        $this->_general['csv_strip_linebreaks_yn'] = $this->_getConfig('csv_strip_linebreaks_yn', 1, false, 'general', $store_id);
        $this->_general['font_style_subtitles'] = $this->_getConfig('font_style_subtitles', 'regular', false, 'general', $store_id);
        $this->_general['font_size_subtitles'] = $this->_getConfig('font_size_subtitles', 15, false, 'general', $store_id);
        $this->_general['font_family_subtitles'] = $this->_getConfig('font_family_subtitles', 'helvetica', false, 'general', $store_id);
        $this->_general['non_standard_characters'] = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);
        $this->_general['font_color_subtitles'] = trim($this->_getConfig('font_color_subtitles', '#222222', false, 'general', $store_id));
        $this->_general['font_style_body'] = $this->_getConfig('font_style_body', 'regular', false, 'general', $store_id);
        $this->_general['font_family_body'] = $this->_getConfig('font_family_body', 'helvetica', false, 'general', $store_id);
        $this->_general['font_color_body'] = trim($this->_getConfig('font_color_body', 'Black', false, 'general', $store_id));
        $this->_general['font_size_body'] = $this->_getConfig('font_size_body', 10, false, 'general', $store_id);
        $this->_general['second_page_start'] = $this->_getConfig('second_page_start', 'top', false, 'general', $store_id); // top or asfirst
    }

    public function setPickPackInvoiceConfig($store_id){
        if ($store_id === null){
            $store_id = Mage::app()->getStore()->getStoreId();
        }
        $this->_packingsheet['pickpack_return_address_yn'] = $this->_getConfig('pickpack_return_address_yn', 0, false, $this->_wonder, $store_id);
        $this->_packingsheet['page_size'] = $this->_getConfig('page_size', 'a4', false, $this->_wonder, $store_id);
    }

    private function reRotateLabel($case_rotate,&$page,$page_top,$padded_right,$nudge_rotate_address_label)
    {
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
                $rotate = 3.14 / 2;
                break;
            case 2:
                $rotate = -3.14 / 2;
                break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], 0-$rotate);
    }

    /*
     * This function prints the bundle product shelving
     *
     */
    protected function printBundleShelving($page,$shelving_yn,$shelving_attribute,$product,$child,$columns_xpos_array,$padded_right,$shelfX,$addon_shift_x,$store_id,$page_count,$items_header_top_firstpage,$page_top,$order_number_display)
    {
        $helper = Mage::helper('pickpack');

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $this->_wonder, $store_id);
        $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $this->_wonder, $store_id);
        $shelving_real = '';
        $flag_newpage_shelving_real = 0;
        $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $this->_wonder, $store_id);
        if ( $shelving_real_yn == 1 && $shelving_yn == 1 && $product->offsetExists($shelving_attribute)) {
            $option = '';
            switch (trim($shelfX)){
                case 'shelfX':
                    $option = 'shelving_real_trim_content_yn';
                    break;
                case 'shelf2X';
                    $option = 'shelving_trim_content_yn';
                    break;
                case 'shelf3X';
                    $option = 'shelving_2_trim_content_yn';
                    break;
                case 'shelf4X';
                    $option = 'shelving_3_trim_content_yn';
                    break;
            }
            if ($product->getData($shelving_attribute)) {
                $shelving_real = $product->getData($shelving_attribute);
            } elseif ($helper->getProductForStore($child->getProductId(), $store_id)->getAttributeText($shelving_attribute)) {
                $shelving_real = $helper->getProductForStore($child->getProductId(), $store_id)->getAttributeText($shelving_attribute);
            } elseif ($product[$shelving_attribute]) $shelving_real = $product[$shelving_attribute];
            if (is_array($shelving_real)) $shelving_real = implode(',', $shelving_real);
            $shelving_real = trim($shelving_real);
            $custom_round_yn = $this->_getConfig('custom_round_yn', 0, false, $this->_wonder, $store_id);
            if($custom_round_yn != 0)
            {
                $shelving_real = $this->_roundNumber($shelving_real,$custom_round_yn);
            }
            $next_col_to_shelving_real = getPrevNext2($columns_xpos_array,$shelfX, 'next', $padded_right);
            $shelfX = $this->_getConfig('pricesN_'.$shelfX, 0, false, $this->_wonder, $store_id);
            $max_shelving_real_length = ($next_col_to_shelving_real - $shelfX);
            $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $line_width_shelving_real = $this->parseString('1234567890', $font_temp_shelf2, ($this->_general['font_size_body'] - 2));
            $char_width_shelving_real = $line_width_shelving_real / 11;
            $max_chars_shelving_real = round($max_shelving_real_length / $char_width_shelving_real);

            $shelving_real = wordwrap($shelving_real, $max_chars_shelving_real, "\n");
            if($combine_custom_attribute_yn == 1){
                $shelfX = $combine_custom_attribute_Xpos;
            }
            $shelving_real_trim = str_trim($shelving_real, 'WORDS', $max_chars_shelving_real - 3, '...');
            $token = strtok($shelving_real, "\n");

            $msg_line_count = 2;
            if ($token != false) {
                while ($token != false) {
                    $shelving_real_array[] = $token;
                    $msg_line_count++;
                    $token = strtok("\n");
                }
            } else
                $shelving_real_array[] = $shelving_real & nbsp;
            //End
            if ($this->_getConfig($option, 0, false, $this->_wonder, $store_id)) {
                $page->drawText($shelving_real_trim, $shelfX +$addon_shift_x, $this->y, 'UTF-8');
                //$this->y -= $line_height;
            } else {
                $count_shelving_row = count($shelving_real_array);

                foreach ($shelving_real_array as $shelving_real_line) {
                    $page->drawText($shelving_real_line, $shelfX +$addon_shift_x, $this->y, 'UTF-8');
                    if($count_shelving_row >0)
                        $this->y -= $this->_general['font_size_body']*0.8;

                    if ($this->y < 20) {
                        if ($page_count == 1 && $this->_packingsheet['pickpack_return_address_yn'] == 0) {
                            $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->_general['font_size_subtitles'] * 2), 'UTF-8');
                        }
                        $page = $this->nooPage($this->_packingsheet['page_size']);
                        $page_count++;
                        $flag_newpage_shelving_real++;
                        $this->_setFont($page, $this->_general['font_style_subtitles'], ($this->_general['font_size_subtitles'] - 2), $this->_general['font_family_subtitles'], $this->_general['non_standard_characters'], $this->_general['font_color_subtitles']);
                        if ($this->_general['second_page_start'] == 'asfirst') $this->y = $items_header_top_firstpage;
                        else $this->y = $page_top;

                        $paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                        $paging_text_width = widthForStringUsingFontSize($paging_text, $this->_general['font_family_subtitles'], ($this->_general['font_size_subtitles'] - 2));
                        $paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

                        $page->drawText($paging_text, $paging_text_x +$addon_shift_x, ($this->y), 'UTF-8');
                        $this->y = ($this->y - ($this->_general['font_size_subtitles'] * 2));

                        $items_y_start = $this->y;
                        $this->_setFont($page, $this->_general['font_style_body'], $this->_general['font_size_body'], $this->_general['font_family_body'], $this->_general['non_standard_characters'], $this->_general['font_color_body']);
                        $this->y -= $this->_general['font_size_body'] * 0.8;
                    }
                }
            }
            unset($shelving_real_array);
            unset($shelving_real);
        }
        return $page;
    }
}