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
 * File        Label.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Input_Label
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{

    public function render(Varien_Object $row)
    {
        $html = '';
        $links = $this->_getLinks($row);

        if (count($links)) {
            $linksHtml = array();
            foreach ($links as $linkData) {
                $carrierNumber = $linkData['number'];

                $link = str_replace('#tracking#', $linkData['number'], $linkData['url']);
                $link = str_replace('#zipcode#', $row->getShippingAddress()->getPostcode(), $link);
                $link = str_replace('#postcode#', $row->getShippingAddress()->getPostcode(), $link);
                $linkHtml = '<span class="tracking_link" data-id="' . $linkData['id'] .'" data-number="' . $carrierNumber . '" data-carrier="' . $linkData['code'] .  '">'
                        . '<a target="_blank" href="' . $link . '">' . (isset($linkData['image']) ? $linkData['image'] : $linkData['title']) . '</a> '
                        . (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/moo_shipeasy_grid_edit_tracking') ? '<a class="fa fa-pencil" style="display:none; color: green;"></a> ' : '')
                        . (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/moo_shipeasy_grid_add_tracking') ? '<a class="fa fa-plus" style="color: blue; display:none"></a>' : '')
                        . ' <a class="fa fa-minus" style="color: red; display:none" data-url="' . $this->getUrl('*/sales_order_process/deleteTracking') . '"></a>'
                    .'</span>';
                $linksHtml[] = $linkHtml;
            }
            
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/moo_shipeasy_grid_add_tracking')) {
                $html = '';

                @list($tracking, $carrier) = explode('||', $row->getData('preshipment_tracking') ? $row->getData('preshipment_tracking')
                    : $row->getData('courierrules_tracking'));
                $input_show = !empty($tracking);
                $span_show = !$input_show;

                $html .= '<span class="track_link_not_fixed" data-number="" style="display:none;">';
                $html .= '<i class="fa fa-clock-o"></i> ';
                $html .= ' <a class="fa fa-pencil" style="display:none; color: green;"></a> ';
                $html .= ' <a class="fa fa-minus" style="color: red; display:none" data-url="' . $this->getUrl('*/sales_order_process/deleteTracking') . '"></a>';
                $html .= '</span> ';

                $html .= '<input type="text" ';
                $html .= 'value="" ';
                $html .= 'name="szy_tracking_number" ';
                $html .= 'data-id="' . $row->getId() . '" ';
                $html .= 'data-url="' . $this->getUrl('*/sales_order_process/saveTracking') . '" ';
                $html .= 'class="tracking_number" style="display:none;"/>';
            }
            $html .= '<div class="tracking_wrapper" data-id="' . $row->getId() . '" data-url-add="' . Mage::getUrl('*/sales_order_process/addTracking')
                . '" data-url-edit="' . Mage::getUrl('*/sales_order_process/editTracking') .'">';
            $html .= implode('<br/>', $linksHtml);
            $html .= '</div>';
        } else {
            
            $html = $this->_renderInput($row);
        }

        return $html;
    }

    protected function _getLinks($order)
    {
        $tracks = $order->getTracksCollection();
        $links  = array();
        if (count($tracks)) {
            foreach ($tracks as $track) {
                $links[] = Mage::helper('moogento_core/carriers')->getTrackLinkData($track);
            }
        }

        return $links;
    }

    protected function _renderInput($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/moo_shipeasy_grid_add_tracking')) {
            $html = '';

            @list($tracking, $carrier) = explode('||', $row->getData('preshipment_tracking') ? $row->getData('preshipment_tracking')
                : $row->getData('courierrules_tracking'));
            $input_show = !empty($tracking);
            $span_show = !$input_show;

            if (Mage::getStoreConfig('moogento_shipeasy/grid/szy_tracking_number_show_carriers')) {
                $html .= $this->_renderDropdown($carrier, $span_show);
            }

            $html .= '<span class="track_link_not_fixed" data-number="' . $tracking . '" '.$this->_displayElem($span_show).'>';
            $html .= '<i class="fa fa-clock-o"></i> ';

            $html .= $tracking;
            $html .= ' <a class="fa fa-pencil" style="display:none; color: green;"></a> ';
            $html .= ' <a class="fa fa-minus" style="color: red; display:none" data-url="' . $this->getUrl('*/sales_order_process/deleteTracking') . '"></a>';
            $html .= '</span> ';

            $html .= '<input type="text" ';
            $html .= 'value="' . ($tracking) . '" ';
            $html .= 'name="szy_tracking_number" ';
            $html .= 'data-id="' . $row->getId() . '" ';
            $html .= 'data-url="' . $this->getUrl('*/sales_order_process/saveTracking') . '" ';
            $html .= 'class="tracking_number" '.$this->_displayElem($input_show).'/>';
            
            return $html;
        }

        return '';
    }

    protected function _displayElem($checker)
    {
        return ($checker) ? 'style="display: none;"' : "";
    }

    protected function _renderDropdown($selected = false, $display = true)
    {
        $carrierConfig = Mage::helper('moogento_core/carriers')->getCarriersConfig();
        $html = '';
        if (count($carrierConfig)) {
            $html .= '<span ' . (!$display ? 'style="display:none"' : '') . '>';
            $html .= '<select class="tracking_carrier" name="tracking_carrier">';
            foreach ($carrierConfig as $index => $one) {
                if (trim($one['title'])) {
                    $html .= '<option value="' . $index . '" ' . ($selected && $selected == $index
                            ? 'selected="selected"' : '') . '>' . $one['title'] . '</option>';
                }
            }
            $html .= '</select><br/>';
            $html .= '</span>';
        }
        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        $html = '';
        $carriersConfig = Mage::helper('moogento_core/carriers')->getCarriersConfig();
        $links = $this->_getLinks($row);
        if (count($links)) {
            foreach ($links as $link) {
                $carrierNumber = $link['number'];
                $title         = Mage::helper('moogento_core/carriers')->getDefaultTitle();
                if ($carriersConfig) {
                    foreach ($carriersConfig as $carrierCode => $carrierInfo) {
                        $lowerCode = strtolower($carrierCode);
                        if (strpos(strtolower($carrierNumber), $lowerCode) === 0) {
                            $title = (!empty($carrierInfo['title'])) ? $carrierInfo['title'] : $title;
                            break;
                        }
                    }

                }
                $html .= $title . ",";
            }
        } else {
            $html .= '';
        }

        return trim($html, ',');

    }

    public function renderHeader()
    {
        $header = parent::renderHeader();
        $header .= '<div id="tracking_form" style="display: none;">';
        if (Mage::getStoreConfig('moogento_shipeasy/grid/szy_tracking_number_show_carriers')) {
            $header .= $this->_renderDropdown();
        }
        $header .= '<input class="tracking_number" type="text" /> <br/><input type="checkbox" value="1" checked class="notify_customer"/> Notify Cust? <button class="button"><span>Edit</span></button>';
        $header .= '</div>';

        return $header;
    }
}
