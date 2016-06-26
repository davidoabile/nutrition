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
* File        Simple.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Store_Simple
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store
{
    public function render(Varien_Object $row)
    {
        $orderStoreId = $row->getData($this->getColumn()->getIndex());
        $label = '';
        if ($orderStoreId) {
            try {
                $websiteModel = Mage::app()->getStore($orderStoreId)->getWebsite();
            } catch (Exception $e) {
                $websiteModel = false;
            }

            if ($websiteModel) {
                $label = $websiteModel->getName();

                $show_type = Mage::getStoreConfig('moogento_shipeasy/grid/szy_website_id_format_website');
                if ($show_type == 1) {
                    if ($image = Mage::getStoreConfig('moogento_shipeasy/grid/szy_website_id_'
                                                      . $websiteModel->getCode()
                                                      . '_logo')
                    ) {
                        $label
                            = '<img style="height: 25px !important;" title="' . $websiteModel->getName() . '" src="'
                              . $image
                              . '" class="szy_grid_image" />';
                    }
                }
            }
        }
        return $label;
    }


    public function renderExport(Varien_Object $row)
    {
        $out = '';
        $skipAllStoresLabel = $this->_getShowAllStoresLabelFlag();
        $origStores = $row->getData($this->getColumn()->getIndex());

        if (is_null($origStores) && $row->getStoreName()) {
            $scopes = array();
            foreach (explode("\n", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat(' ', $k * 3) . $label;
            }
            $out .= implode("\r\n", $scopes) . $this->__(' [deleted]');
            return $out;
        }

        if (!is_array($origStores)) {
            $origStores = array($origStores);
        }

        if (in_array(0, $origStores) && !$skipAllStoresLabel) {
            return Mage::helper('adminhtml')->__('All Store Views');
        }

        $data = $this->_getStoreModel()->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $out .= $website['label'] . "\r\n";
        }

        return $out;
    }
}
