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
* File        Skus.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Skus extends Mage_Adminhtml_Block_Template
{
    protected $_xmlPathFillColor = 'moogento_shipeasy/grid/szy_product_skus_fill_color';
    protected $_xmlPathCriteria = 'moogento_shipeasy/grid/szy_product_skus_product_availability';
	protected $_xmlPathCriteriaCustomQty = 'moogento_shipeasy/grid/szy_product_skus_custom_qty';

    protected $_xmlPathColorUnavailable = 'moogento_shipeasy/grid/szy_product_skus_fully_unavailable';
    protected $_xmlPathColorFullyAvailable = 'moogento_shipeasy/grid/szy_product_skus_fully_available';
    protected $_xmlPathColorPartiallyAvailable = 'moogento_shipeasy/grid/szy_product_skus_partially_available';

    protected $_xmlPathTruncateText = 'moogento_shipeasy/grid/szy_product_skus_truncate';
    protected $_xmlPathTruncateLength = 'moogento_shipeasy/grid/szy_product_skus_x_truncate';
	protected $_xmlPathTransparentStatus = 'moogento_shipeasy/grid/szy_product_skus_transparent_status';

	protected function _stockWarningQty(){
		$stock_warning_qty = Mage::getStoreConfig($this->_xmlPathCriteriaCustomQty);
		return $stock_warning_qty;
	}
	
    protected $_simpleProductTypes = array(
        'simple',
        'virtual',
        'downloadable'
    );

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/skus.phtml');
    }


    protected function _getItemAvailableColor($item)
    {
	    if (!Mage::getStoreConfigFlag($this->_xmlPathFillColor)) {
		    return 'transparent';
	    }
	    $transparent_arr = Array();
	    if (($transparent_statuses = Mage::getStoreConfig($this->_xmlPathTransparentStatus))) {
		    $transparent_arr = explode(',',$transparent_statuses);

	    }
	    foreach($transparent_arr as $stran_status)
	    {
		    if($item->getOrder()->getStatus() == $stran_status)
			    return 'transparent';
	    }

        $colorType = Mage::getStoreConfig($this->_xmlPathCriteriaCustomQty);
        $class = '';
        if ($colorType == Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Product_Availability::ISTOCK) {
            switch ($item->getInStockAtCreateMoment()) {
                case 0:
                    $class = "szy_stock_unavailable";
                    break;
                case 1:
                    $class = "szy_stock_partially_available";
                    break;
                case 2:
                    $class = "szy_stock_fully_available";
                    break;
            }
        } else {
            $result = 1;
            if (in_array($item->getProductType(), $this->_simpleProductTypes)) {
                $productId = $item->getProductId();
                $qty = $item->getQtyOrdered();

                if ($item->getParentItem() && ($item->getParentItem()->getProductType() == 'bundle')) {
                    $qty *= $item->getParentItem()->getQtyOrdered();
                }
                $result = Mage::helper('moogento_shipeasy/inventory')->checkAvailability(
                    $productId,
                    $qty,
                    Mage::getStoreConfig($this->_xmlPathCriteria),
                    $this->_stockWarningQty()
                );
            } else if ($item->getParentItem() == 'bundle'){
                $_childItem = $item->getChildrenItems()->getFirstItem();
                $productId = $_childItem->getProductId();
                $qty = $_childItem->getQtyOrdered() * $item->getQtyOrdered();
                $result = Mage::helper('moogento_shipeasy/inventory')->checkAvailability(
                    $productId,
                    $qty,
                    Mage::getStoreConfig($this->_xmlPathCriteria),
                    $this->_stockWarningQty()
            );
        }

            switch ($result) {
                case -1:
                    $class = "szy_stock_unavailable";
                    break;
                case 1:
                    $class = "szy_stock_fully_available";
                    break;
                case 0:
                    $class = "szy_stock_partially_available";
                    break;
            }
        }

        return $class;
    }

    public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection();
    }

    protected function _truncate($sku, $additionalSymbolCounts = 0)
    {
        if (!Mage::getStoreConfigFlag($this->_xmlPathTruncateText)) {
            return $sku;
        }

        $truncatePosition = (int)Mage::getStoreConfig($this->_xmlPathTruncateLength) - $additionalSymbolCounts;
        if ($truncatePosition < strlen($sku)) {
            return trim(substr($sku, 0, $truncatePosition)). '&hellip;';
        } else {
            return $sku;
        }
    }

    protected function _isBundleProduct($item)
    {
        $result = false;

        if ($item->getProductType() == 'bundle') {
            $result = true;
        }
        return $result;
    }

    protected function _isGroupedProduct($item)
    {
        $result = false;

        if ($item->getProductType() == 'grouped') {
            $result = true;
        }
        return $result;
    }

    protected function _isSimpleWeDisplay($item)
    {
        $result = false;

        if (
            in_array($item->getProductType(), $this->_simpleProductTypes) &&
            (
                !$item->getParentItem() ||
                ($item->getParentItem()->getProductType() == 'configurable')
            )
        ) {
            $result = true;
        }

        return $result;
    }
    
    public function showResult($classColor, $_item, $displaySingle, $displayLink)
    {
        $itemUrl = '';
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro') && $displayLink) {
            $itemUrl = Mage::helper('moogento_shipeasy/grid')->getMktLink($_item);
        }

        $result = "";
        $result .= '<div class="'.$classColor.'">';
            $result .= '<div>';
                $result .= '<div class="szy_grid_sku" title="' . $_item->getSku() .'">';
                    if($itemUrl) {$result .= '<a href="'.$itemUrl.'" target="_blank">';}
                        $qty = (int)$_item->getQtyOrdered();
                        $result .= '<span>';
						if (($qty != 1) || ($displaySingle)) {
                            $result .= '<b>' . $qty . '</b> x ';
                        }     
                        $result .= Mage::helper('moogento_shipeasy/functions')->clean($_item->getSku());
						$result .= '</span>';
                    if($itemUrl)  {$result .= '</a>';}
                    if(Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_product_skus_go-to-product-page'))  {
                        $result .= '  <a href="';
                            $result .= Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $_item->getProductId()));
                        $result .= '" class="linkout_sku tooltipster" style="display:none;" title="Go to this product detail page"></a>';
                    }
                $result .= '</div>';
            $result .= '</div>';
        $result .= '</div>';
        $result .= '<div class="clear"></div>';
        return $result;
    }
}
