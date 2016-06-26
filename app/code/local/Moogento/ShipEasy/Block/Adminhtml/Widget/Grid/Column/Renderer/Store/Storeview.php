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
 * File        Storeview.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Store_Storeview
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store
{

    public function render(Varien_Object $row)
    {
        //0: Standard -- 1: Simplified

        $out                  = '';
        $out_full             = '';
        $skipAllStoresLabel   = $this->_getShowAllStoresLabelFlag();
        $skipEmptyStoresLabel = $this->_getShowEmptyStoresLabelFlag();
        $origStores           = $row->getData($this->getColumn()->getIndex());

        if (is_null($origStores) && $row->getStoreName()) {
            $scopes = array();
            foreach (explode("\n", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat('&nbsp;', $k * 3) . $label;
            }
            $out .= implode('<br/>', $scopes) . $this->__(' [deleted]');
            $out_full .= implode('<br/>', $scopes) . $this->__(' [deleted]');
            // return $out;
        } else {
            if (!is_array($origStores)) {
                $origStores = array($origStores);
            }
        }

        if (empty($origStores)) {
//                 return '';
        } else {
            if (in_array(0, $origStores) && count($origStores) == 1 && !$skipAllStoresLabel) {
//                 return Mage::helper('adminhtml')->__('All Store Views');
            } else {
                $data = $this->_getStoreModel()->getStoresStructure(false, $origStores);

                foreach ($data as $website) {
                    $out_full .= $website['label'] . '&nbsp;&mdash;&nbsp;';
                    foreach ($website['children'] as $group) {
                        $out_full .= $group['label'] . '&nbsp;&mdash;&nbsp; ';
                        foreach ($group['children'] as $store) {
                            $out_full .= $store['label'];
                            $out = $store['label'];
                        }
                    }
                }
            }
        }

        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_store_id_format_store_view') == 0) {
            if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_store_id_format') == 0) {
                return $out_full;
            } else {
                return $out;
            }            
        } else {
            $orderStoreId = $row->getData($this->getColumn()->getIndex());
            $label = '';
            if ($orderStoreId) {
                try {
                    $store_view = Mage::app()->getStore($orderStoreId);
                } catch (Exception $e) {
                    $store_view = false;
                }

                if ($store_view) {
                    $label = $store_view->getName();
                    if ($image = Mage::getStoreConfig(
                        'moogento_shipeasy/grid/szy_store_id_store_view_' . $store_view->getCode() . '_logo'
                    )
                    ) {
                        $label
                            = '<img style="height: 25px !important;" title="' . $store_view->getName() . '" src="'
                              . $image
                              . '" class="szy_grid_image" />';
                    } else {
                        $label = $out;
                    }
                }
            }
            return $label;
        }


    }

    public function renderExport(Varien_Object $row)
    {
        //0: Standard -- 1: Simplified

        $out                  = '';
        $out_full             = '';
        $skipAllStoresLabel   = $this->_getShowAllStoresLabelFlag();
        $skipEmptyStoresLabel = $this->_getShowEmptyStoresLabelFlag();
        $origStores           = $row->getData($this->getColumn()->getIndex());

        if (is_null($origStores) && $row->getStoreName()) {
            $scopes = array();
            foreach (explode("\n", $row->getStoreName()) as $k => $label) {
                $scopes[] = str_repeat(' ', $k * 3) . $label;
            }
            $out .= implode("\n", $scopes) . $this->__(' [deleted]');
            $out_full .= implode("\n", $scopes) . $this->__(' [deleted]');
        } else {
            if (!is_array($origStores)) {
                $origStores = array($origStores);
            }
        }

        if (!empty($origStores)) {
            if (in_array(0, $origStores) && count($origStores) == 1 && !$skipAllStoresLabel) {

            } else {
                $data = $this->_getStoreModel()->getStoresStructure(false, $origStores);

                foreach ($data as $website) {
                    $out_full .= $website['label'] . ' - ';
                    foreach ($website['children'] as $group) {
                        $out_full .= $group['label'] . ' - ';
                        foreach ($group['children'] as $store) {
                            $out_full .= $store['label'];
                            $out = $store['label'];
                        }
                    }
                }
            }
        }

        if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_store_id_format') == 0) {
            return $out_full;
        } else {
            return $out;
        }
    }
}
