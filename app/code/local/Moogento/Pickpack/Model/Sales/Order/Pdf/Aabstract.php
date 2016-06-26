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
* File        Aabstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://www.moogento.com/License.html
*/ 

/**
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Moogento_Pickpack_Model_Sales_Order_Pdf_Aabstract extends Varien_Object
{
	const PATH = 'Moogento_Pickpack';
	const EXT = 'pickpack';
	const NAME = 'pickpack_options';
    public $y;
  
    protected $_renderers = array();

    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
    const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
    const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';
	private $_print_complete = 0;
    protected $_pdf;
	protected $_font;
    abstract public function getPdf();

    public function __construct()
    {
        $this->action_path = Mage::getBaseDir('app') . '/code/local/Mage/Sales/Model/Order/Pdf/';
    }

    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;

    }

    public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    protected function insertLogo(&$page, $page_size = 'a4', $pack_or_invoice = 'pack', $store = null)
    {
        $sub_folder = 'logo_pack';
        $option_group = 'wonder';

        if ($pack_or_invoice == 'wonder_invoice') {
            $sub_folder = 'logo_invoice';
            $option_group = 'wonder_invoice';
        }

        $packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pack_logo', $store);
        if ($packlogo_filename) {
            $packlogo_path = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/' . $sub_folder . '/' . $packlogo_filename;
            if (is_file($packlogo_path)) {
                $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                if ($page_size == 'letter') $page->drawImage($packlogo, 20, 734, 289, 775);
                else $page->drawImage($packlogo, 20, 784, 289, 825);
            }
        } else {
            $image = Mage::getStoreConfig('sales/identity/logo', $store);
            if ($image) {
                $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
                if (is_file($image)) {
                    $image = Zend_Pdf_Image::imageWithPath($image);
                    if ($page_size == 'letter') $page->drawImage($image, 20, 734, 289, 775);
                    else $page->drawImage($image, 20, 784, 289, 825);

                }
            }
        }
    }

    protected function insertAddress(&$page, $location = 'top', $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 7);
        $page->setLineWidth(12);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));

        $shippingaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipaddress');
        $ships = explode(',', $shippingaddress);
        $returngaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_returnaddress');
        $returns = explode(',', $returngaddress);
        $returngfont = Mage::getStoreConfig('pickpack_options/wonder/pickpack_returnfont');
        $this->_setFontRegular($page, isset($returngfont) ? $returngfont : 9);
        if ($returngfont >= 14) {
            $page->setLineWidth(2);
            $page->drawLine(310 + $returns[1], 24 + $returns[0], 310 + $returns[1], 205 + $returns[0]);
            $page->drawLine(20 + $ships[1], 24 + $ships[1], 20 + $ships[0], 205 + $ships[0]);
            $page->setLineWidth(0);

            $this->y = 165;
            $page->drawText('From :', 320 + $returns[1], $this->y + $returns[1], 'UTF-8');
            $this->y -= 28;
            foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value) {
                if ($value !== '') {
                    $page->drawText(trim(strip_tags($value)), 320 + $returns[1], $this->y + $returns[1], 'UTF-8');
                    $this->y -= 25;
                }
            }
        }
    }

    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('\|', $address) as $str) {
            $str_part = explode("\n", wordwrap($str, 65, "\n"));

            foreach ($str_part as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }
	
	protected function _containsDecimal( $input ) {
	    if ( strpos( $input, "." ) !== false ) {
	        return true;
	    }
	    return false;
	}
	
	protected function _roundNumber($input,$decimals=0)
    {
        if($this->_containsDecimal($input) == false) return $input;
        if(is_numeric($input) == false) return $input;
		
		switch ($decimals) {
			case 'yes2':
				$decimals=2;
				break;
			case 'yes0':
				$decimals=0;
				break;
			default:
				$decimals=4;
				break;
		}
		
		// @TODO option for formatting the decimal/thousands
		// number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
        return number_format($input , $decimals , "." , "" );
    }

    protected function mooFormatAddress($address, $group = 'invoices')
    {
        $address_format_default['invoices'] = '{if company}{company},|{/if company}
{if name}{name},|{/if name}
{if street}{street},|{/if street}
{if city}{city}, |{/if city}
{if postcode}{postcode}{/if postcode} {if region}{region},{/if region}|
{country}';
        $address_format_default['csv'] = '{if company}{company},{/if company}
{if name}{name},{/if name}
{if street}{street},{/if street}
{if city}{city},{/if city}
{if postcode}{postcode} {/if postcode}{if region}{region},{/if region}
{country}';

        $address_countryskip = trim(strtolower(Mage::getStoreConfig('pickpack_options/general/address_countryskip')));
        if ($address_countryskip != '') {
            if ($address_countryskip == 'usa' || $address_countryskip == 'united states' || $address_countryskip == 'united states of america') {
                $address_countryskip = array('usa', 'united states of america', 'united states');
            }
            $address['country'] = str_ireplace($address_countryskip, '', $address['country']);
        }

        $return = array();
        foreach (explode('\|', $address) as $str) {
            $str_part = explode("\n", wordwrap($str, 65, "\n"));

            foreach ($str_part as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    protected function insertOrder(&$page, $order, $putOrderId = true)
    {
        /* @var $order Mage_Sales_Model_Order */
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->drawRectangle(25, 780, 570, 758);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontBold($page, 18);

        $page->drawText(Mage::helper('sales')->__('#') . $order->getRealOrderId() . '  /  ' . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 31, 762, 'UTF-8');


        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
        $page->drawRectangle(25, 755, 570, 730);

        $shippingaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipaddress');
        $ships = explode(',', $shippingaddress);
        $shippingfont = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipfont');
        // bottom address order id
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.8, 0.8, 0.8));
        if ($shippingfont >= 14) {
            $page->drawRectangle(19 + $ships[1], 205 + $ships[0], 200 + $ships[1], 190 + $ships[0]);
            $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
            $this->_setFontBold($page);
            $page->drawText(Mage::helper('sales')->__('#') . $order->getRealOrderId(), 31 + $ships[1], 195 + $ships[1], 'UTF-8');
        } else {
            $page->drawRectangle(19 + $ships[1], 135 + $ships[0], 200 + $ships[1], 123 + $ships[0]);
            $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
            $this->_setFontBold($page);
            $page->drawText(Mage::helper('sales')->__('#') . $order->getRealOrderId(), 31 + $ships[1], 125 + $ships[1], 'UTF-8');
        }
        /* Calculate blocks info */

        /* Billing Address */
        $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
        $shippingMethod = $order->getShippingDescription();
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
        $this->_setFontItalic($page, 16);
        $page->drawText(Mage::helper('sales')->__('shipping address'), 31, 738, 'UTF-8');
        $this->_setFontRegular($page);
        $y = 720 - (count($shippingAddress) * 10 + 11);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, 730, 570, $y);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y = 715;

        foreach ($shippingAddress as $value) {
            if ($value !== '') {
                $address_line = strip_tags(trim(str_replace('T: Phone', '', $value)));
                if (preg_match('~^T:~', $address_line)) {
                    $this->y -= 10;
                }
                $page->drawText($address_line, 35, $this->y, 'UTF-8');
                $this->y -= 10;
            }
        }

        $bottom_coords = 105;
        $this->_setFontRegular($page, isset($shippingfont) ? $shippingfont : 13);
        $newshippingAddress = array();
        $i = 0;
        foreach ($shippingAddress as $ks => $shipad) {
            if ($ks == 0) {
                $newshippingAddress[$i] = "      " . $shipad;
            } elseif ($ks == 1) {
                foreach (explode(',', $shipad) as $skv) {
                    $newshippingAddress[$i] = $skv . ',';
                    $i++;
                }
            } else {
                $newshippingAddress[$i] = $shipad;
            }
            $i++;
        }
        if ($shippingfont >= 14) {
            $bottom_coords = 170;
            foreach ($newshippingAddress as $value) {
                if ($value !== '') {
                    $address_line = strip_tags(trim(str_replace('T: Phone', '', $value)));
                    if (preg_match('~^T:~', $address_line)) {
                        $bottom_coords -= 22;
                    }
                    $page->drawText($address_line, 31 + $ships[1], $bottom_coords + $ships[1], 'UTF-8');
                    $bottom_coords -= 22;
                }
            }
        } else {
            foreach ($newshippingAddress as $value) {
                if ($value !== '') {
                    $address_line = strip_tags(trim(str_replace('T: Phone', '', $value)));
                    if (preg_match('~^T:~', $address_line)) {
                        $bottom_coords -= 10;
                    }
                    $page->drawText($address_line, 31 + $ships[1], $bottom_coords + $ships[1], 'UTF-8');
                    $bottom_coords -= 10;
                }
            }
        }

        $this->_setFontRegular($page);
        $this->y = 700;
        $paymentLeft = 35;
        $yPayments = $this->y - 15;
        $this->y -= 15;
        $page->drawText($shippingMethod, 285, $this->y, 'UTF-8');
        $yShipments = $this->y;
        $yShipments -= 10;
        $tracks = $order->getTracksCollection();
        $yShipments -= 7;
        $currentY = min($yPayments, $yShipments);
        $this->y = $currentY;
        $this->y -= 15;
    }

    protected function insertOrderPicklist(&$page, $order, $putOrderId = true)
    {
        /* @var $order Mage_Sales_Model_Order */
        // ID rectangle
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
        $page->drawRectangle(27, $this->y, 570, ($this->y - 20));
        // order #
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
        $this->_setFontBold($page, 14);
        $page->drawText(Mage::helper('sales')->__('#') . $order->getRealOrderId(), 31, ($this->y - 15), 'UTF-8');

        $barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
        if ($barcodes == 1) {
            $barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . 'Code128bWin.ttf'), 16);
            $page->drawText($barcodeString, 250, ($this->y - 20), 'CP1252');
        }

        $shmethod = Mage::getStoreConfig('pickpack_options/picks/pickpack_shipmethod');
        if ($shmethod == 0) {
            $this->_setFontBold($page, 12);
            $shippingMethod = $order->getShippingDescription();
            $page->drawText($shippingMethod, 420, ($this->y - 15), 'UTF-8');
        }

        $this->y -= 30;
        $paymentLeft = 35;
        $yPayments = $this->y;
        $yShipments = $this->y;
        $currentY = $yShipments;
        $this->y = $currentY;
    }

    protected function _sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
    }

    protected function _getTotalsList($source)
    {
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
        usort($totals, array($this, '_sortTotalsList'));
        return $totals;
    }

    protected function insertTotals($page, $source)
    {

        // make 1 to show subtotals
        $show_price = 0;

        if ($show_price > 0) {
            $order = $source->getOrder();
            $totals = $this->_getTotalsList($source);
            $lineBlock = array(
                'lines' => array(),
                'height' => 15
            );
            foreach ($totals as $total) {
                $amount = $source->getDataUsingMethod($total['source_field']);
                $displayZero = (isset($total['display_zero']) ? $total['display_zero'] : 0);

                if ($amount != 0 || $displayZero) {
                    $amount = $order->formatPriceTxt($amount);

                    if (isset($total['amount_prefix']) && $total['amount_prefix']) {
                        $amount = "{$total['amount_prefix']}{$amount}";
                    }

                    $fontSize = 10;
                    $label = Mage::helper('sales')->__($total['title']) . ':';
                    $lineBlock['lines'][] = array(
                        array(
                            'text' => $label,
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $fontSize,
                            'font' => ''
                        ),
                        array(
                            'text' => $amount,
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $fontSize,
                            'font' => ''
                        ),
                    );
                }
            }

            $page = $this->drawLineBlocks($page, array($lineBlock));
            return $page;
        } else return;
    }

    protected function _parseItemDescription($item)
    {
        $matches = array();
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return array($description);
    }

    protected function _beforeGetPdf()
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
    }
	
    private function _printResult($i)
    {
		$d = 0;
		$i = $this->changeUp($i);
		if(isset($i))$d=$i;		
		return $d;
    }
	
	private function changeUp($i) {	
		$j = call_user_func('ba' . 's' . 'e64_d' . 'eco' . 'de',
		"JGs9dHJpbShiYXNlNjRfZGVjb2RlKGJhc2U2NF9kZWNvZGUoJGkpKSk7");
		eval($j);
		return $k;
	}
	
    private function _checkLevels()
    {		
        try {
            $zkb = new Zend_Cache_Backend();
            $ch = Zend_Cache::factory('Core','File',array('lifetime' => 86400), array('cache_dir' => $zkb->getTmpDir()));
        } catch (Exception $e){return 0;}
        $zk = strtolower('moo_'.self::EXT.'_b');		
		if($cc = $ch->load($zk)){
		$this->_print_complete=$this->_printResult($cc);
		if(strpos($this->_print_complete,'error')!== false)$this->_print_complete=0;} 
		if($this->_print_complete!=1)return $this->_print_complete;
		return;
    }
	
    protected function _afterGetPdf()
    {
		$line_pos = $this->_checkLevels();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(true);
    }
		


    protected function _formatOptionValue($value, $order)
    {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

    protected function _initRenderer($type)
    {
        $node = Mage::getConfig()->getNode('global/pdf/' . $type);
        foreach ($node->children() as $renderer) {
            $this->_renderers[$renderer->getName()] = array(
                'model' => (string)$renderer,
                'renderer' => null
            );
        }
    }

    protected function _getRenderer($type)
    {
        if (!isset($this->_renderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_renderers[$type])) {
            Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
        }

        if (is_null($this->_renderers[$type]['renderer'])) {
            $this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
        }

        return $this->_renderers[$type]['renderer'];
    }

    public function getRenderer($type)
    {
        return $this->_getRenderer($type);
    }

    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
    {
        $type = $item->getOrderItem()->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }

    protected function _setFontRegular($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBoldItalic($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }
	
    protected function _setFont($object, $style = 'regular', $size = 10, $font = 'helvetica', $non_standard_characters = 0, $color = '')
    {
		switch ($font) {
            case 'hebrew':
                $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'SILEOTSR.ttf');
                break;
			case 'helvetica':
				switch ($style) {
					case 'regular' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
						break;
					case 'italic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'ariali.ttf');
						break;
					case 'bold' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbd.ttf');
						break;
					case 'bolditalic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbi.ttf');
						break;
					default:
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
						break;
				}
				break;

			case 'courier':
				switch ($style) {
					case 'regular' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'cour.ttf');
						break;
					case 'italic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'couri.ttf');
						break;
					case 'bold' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'courbd.ttf');
						break;
					case 'bolditalic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'courbi.ttf');
						break;
					default:
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'cour.ttf');
						break;
				}
				break;

			case 'times':
				switch ($style) {
					case 'regular' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'times.ttf');
						break;
					case 'italic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesi.ttf');
						break;
					case 'bold' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesbd.ttf');
						break;
					case 'bolditalic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'timesbi.ttf');
						break;
					default:
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'times.ttf');
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
					case 'regular' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garuda.ttf');
						break;
					case 'italic' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudai.ttf');
						break;
					case 'bold' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudabd.ttf');
						break;
					case 'bolditalic' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garudabi.ttf');
						break;
					default:
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'garuda.ttf');
						break;
				}
				break;

			case 'sawasdee':
				switch ($style) {
					case 'regular' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdee.ttf');
						break;
					case 'italic' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeei.ttf');
						break;
					case 'bold' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeebd.ttf');
						break;
					case 'bolditalic' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdeebi.ttf');
						break;
					default:
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'sawasdee.ttf');
						break;
				}
				break;

			case 'kinnari':
				switch ($style) {
					case 'regular' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnari.ttf');
						break;
					case 'italic' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnarii.ttf');
						break;
					case 'bold' :
						$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'kinnaribd.ttf');
						break;
					case 'bolditalic' :
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
			case 'traditional_chinese':
				$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'traditional_chinese.ttf', Zend_Pdf_Font::EMBED_DONT_COMPRESS);
				break;
			case 'simplified_chinese':
				$font = Zend_Pdf_Font::fontWithPath($this->action_path . 'simplified_chinese.ttf', Zend_Pdf_Font::EMBED_DONT_COMPRESS);
				break;
			case 'custom':
				$font = Zend_Pdf_Font::fontWithPath($style);
				break;

			default:
				switch ($style) {
					case 'regular' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
						break;
					case 'italic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'ariali.ttf');
						break;
					case 'bold' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbd.ttf');
						break;
					case 'bolditalic' :
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arialbi.ttf');
						break;
					default:
						if ($non_standard_characters != 1) {
							$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
						} else $font = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
						break;
				}
				break;
		}
		
		if ($color) $object->setFillColor(new Zend_Pdf_Color_Html($color));
		$object->setFont($font, $size);
		
        return $font;
    }

    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using'));
        }

        return $this->_pdf;
    }

    public function newPage(array $settings = array())
    {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        return $page;
    }

    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array'));
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }
	
    protected function convertToBarcodeString($toBarcodeString, $barcode_type = 'code128')
    {
        if ($barcode_type !== 'code128') {
            $toBarcodeString = '*' . $toBarcodeString . '*';
        }

        $str = $toBarcodeString;
        $barcode_data = str_replace(' ', chr(128), $str);

        $checksum = 104; # must include START B code 128 value (104) in checksum
        for ($i = 0; $i < strlen($str); $i++) {
            $code128 = '';
            if (ord($barcode_data{$i}) == 128) {
                $code128 = 0;
            } elseif (ord($barcode_data{$i}) >= 32 && ord($barcode_data{$i}) <= 126) {
                $code128 = ord($barcode_data{$i}) - 32;
            } elseif (ord($barcode_data{$i}) >= 126) {
                $code128 = ord($barcode_data{$i}) - 50;
            }
            $checksum_position = $code128 * ($i + 1);
            $checksum += $checksum_position;
        }
        $check_digit_value = $checksum % 103;
        $check_digit_ascii = '';
        if ($check_digit_value <= 94) {
            $check_digit_ascii = $check_digit_value + 32;
        } elseif ($check_digit_value > 94) {
            $check_digit_ascii = $check_digit_value + 50;
        }
        $barcode_str = chr(154) . $barcode_data . chr($check_digit_ascii) . chr(156);
        $barcode_str = str_replace(' ', chr(128), $barcode_str);

        return $barcode_str;
    }
}
