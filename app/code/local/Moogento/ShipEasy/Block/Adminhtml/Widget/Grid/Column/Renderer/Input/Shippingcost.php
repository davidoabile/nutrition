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
* File        Shippingcost.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Input_Shippingcost
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{
    protected function _getCurrencySelectBoxHtml($row)
    {
        $html = '';
        if ($row->getStoreId()) {
            try {
                $codes = Mage::app()->getStore($row->getStoreId())->getAvailableCurrencyCodes(true);
            } catch (Exception $e) {
                $codes = array();
            }
            if (count($codes)) {
                $html .= '<select class="shipping_cost_currency" name="' . $this->getColumn()->getId() . '_currency'
                         . '">';
                foreach ($codes as $code) {
                    $selected = ($code == $row->getOrderCurrencyCode());
                    if ($selected) {
                        $html .= '<option selected="selected" value="' . $code . '">' . $code . '</option>';
                    } else {
                        $html .= '<option value="' . $code . '">' . $code . '</option>';
                    }
                }
                $html .= '</select>';
            }
        }
        return $html;
    }

    public function render(Varien_Object $row)
    {
        if (!is_null($row->getData($this->getColumn()->getIndex()))) {
            return parent::render($row);
        } else {
            $html = '<input type="text" ';
            $html .= 'name="' . $this->getColumn()->getId() . '" ';
            $html .= 'value="' . $row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'class="input-text ' . $this->getColumn()->getInlineCss() . '"/>';
            return $html;
        }
    }

    public function renderExport(Varien_Object $row)
    {
        if (!is_null($row->getData($this->getColumn()->getIndex()))) {
            return $this->render($row);
        } else {
            return '';
        }
    }
}
