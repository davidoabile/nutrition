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
 * File        Date.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Date
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    public function render(Varien_Object $row)
    {

        return $this->_getValue($row);
    }

    public function _getValue(Varien_Object $row)
    {
        $val = $row->getData('created_at');
        if (is_null($val)) {
            return '';
        }

        $format_date = Mage::helper('moogento_shipeasy/format')->formatDate($val, 'medium', true, 'date');
        $date_format = Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_format');
        $dateType = Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_type');

        $val = $format_date;
        switch ($date_format) {
            case 2:
				// Mar 14, 2011 7:24:37 PM
                $date_formatted = Mage::helper('moogento_shipeasy/date')->format($val, 'M j, Y g:i A', false, $dateType);
                break;
            case 3:
				// Change to PHP format
                $date_formatted = Mage::helper('moogento_shipeasy/date')->format($val, Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_custom_format'), false, $dateType);
                break;
			case 0:
            default:
				// 14.03.11 19:24
                $date_formatted = Mage::helper('moogento_shipeasy/date')->format($val, 'd.m.y H:i', false, $dateType);
                break;
        }
        if ($date_formatted) {
            if ($dateType == Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN) {
                return '<span style="direction: rtl; display: inline-block; width: 100%;">' . $date_formatted . '</span>';
            }
            return $date_formatted;
        }

        if ($dateType == Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN) {
            return '<span style="direction: rtl; display: inline-block; width: 100%;">' . $val . '</span>';
        }

        return $val;

    }

    public function renderExport(Varien_Object $row)
    {
        return strip_tags($this->render($row));
    }

}
