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
* File        Names.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 



class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Names
    extends Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Skus
{
    protected $_xmlPathFillColor = 'moogento_shipeasy/grid/szy_product_names_fill_color';
    protected $_xmlPathCriteria = 'moogento_shipeasy/grid/szy_product_names_product_availability';
    protected $_xmlPathCriteriaCustomQty = 'moogento_shipeasy/grid/szy_product_names_custom_qty';
    
    protected $_xmlPathColorUnavailable = 'moogento_shipeasy/grid/szy_product_names_fully_unavailable';
    protected $_xmlPathColorFullyAvailable = 'moogento_shipeasy/grid/szy_product_names_fully_available';
    protected $_xmlPathColorPartiallyAvailable = 'moogento_shipeasy/grid/szy_product_names_partially_available';

    protected $_xmlPathTruncateText = 'moogento_shipeasy/grid/szy_product_names_truncate';
    protected $_xmlPathTruncateLength = 'moogento_shipeasy/grid/szy_product_names_x_truncate';
    protected $_xmlPathTransparentStatus = 'moogento_shipeasy/grid/szy_product_names_transparent_status';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moogento/shipeasy/sales/order/grid/names.phtml');
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
                $result .= '<div class="nowrap szy_grid_name" title="'.Mage::helper('moogento_shipeasy/functions')->clean($_item->getName()).'">';
                if($itemUrl) {$result .= '<a href="'.$itemUrl.'" target="_blank">';}
                    if((int)$_item->getQtyOrdered() == (float)$_item->getQtyOrdered()){
                        $qty = (int)$_item->getQtyOrdered();
                    } else {
                        $qty = round($_item->getQtyOrdered(), 2);
                    }
                    $strQty = '';
                    if (($qty != 1) || ($displaySingle)) {
                        $result .= '<b>' . $qty . '</b> x ';
                    }
                    if(Mage::getStoreConfig('moogento_shipeasy/grid/szy_product_names_cut_name')){
                        $result .= $this->_truncate(Mage::helper('moogento_shipeasy/functions')->clean($_item->getName()), strlen($strQty));
                    } else {
                        $result .= $_item->getName();
                    }
                    if($itemUrl) {$result .= '</a>';}
                $result .= '</div>';
            $result .= '</div>';
        $result .= '</div>';
        return $result;
    }
    
    protected function _truncate($sku, $additionalSymbolCounts = 0)
    {
        if (!Mage::getStoreConfigFlag($this->_xmlPathTruncateText)) {
            return $sku;
        }
        
        $truncatePosition = (int)Mage::getStoreConfig('moogento_shipeasy/grid/szy_product_names_cut_name_length') - $additionalSymbolCounts;
        if ($truncatePosition < strlen($sku)) {
            return trim(substr($sku, 0, $truncatePosition)). '&hellip;';
        } else {
            return $sku;
        }
    }
}
