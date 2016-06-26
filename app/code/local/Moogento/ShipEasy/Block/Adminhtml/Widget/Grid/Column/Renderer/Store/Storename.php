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
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Store_Storename
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store
{
    public function render(Varien_Object $row)
    {
        $orderStoreId = $row->getData($this->getColumn()->getIndex());
        if ($orderStoreId) {
            try {
                $group = Mage::app()->getStore($orderStoreId)->getGroup();
            } catch (Exception $e) {
                $group = false;
            }
            if ($group) {
                if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_store_name_format_store_view')) {
                    if ($image = Mage::getStoreConfig('moogento_shipeasy/grid/szy_store_name_store_view_'
                                                      . $group->getId()
                                                      . '_logo')
                    ) {
                        return '<img style="height:25px !important;" title="' . $group->getName() . '" src="' . $image
                               . '" class="szy_grid_image" />';
                    } else {
                        if (Mage::getStoreConfig('moogento_shipeasy/grid/szy_store_name_format')) {
                            return $group->getName();
                        } else {
                            return $group->getWebsite()->getName() . ' - '
                                   . $group->getGroup()->getName();
                        }
                    }
                } else {
                    if (Mage::getStoreConfig('moogento_shipeasy/grid/szy_store_name_format')) {
                        return $group->getName();
                    } else {
                        return $group->getWebsite()->getName() . ' - '
                               . $group->getName();
                    }
                }
            }
        }
        return '';
    }
}
