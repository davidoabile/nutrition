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
 * File        Dateformat.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */


class Moogento_ShipEasy_Model_Adminhtml_System_Config_Source_Grid_Datetype
{
    public function toOptionArray()
    {
        return array(
            array('value' => Moogento_ShipEasy_Helper_Date::TYPE_GREGORIAN, 'label'=>Mage::helper('moogento_shipeasy')->__('Gregorian')),
            array('value' => Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN, 'label'=>Mage::helper('moogento_shipeasy')->__('Persian')),
            array('value' => Moogento_ShipEasy_Helper_Date::TYPE_THAI, 'label'=>Mage::helper('moogento_shipeasy')->__('Thai')),
        );
    }

}
