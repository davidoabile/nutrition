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
 * File        Color.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_System_Config_General extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getFieldsContainerHeaderWithClassAndStatus($title, $class, $status)
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;


        if ($status == 1) {
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        } else {
            $html = '<tr class="column_config ' . $class . '"><td colspan="' . $colspan . '">';
        }

        $html .=
            '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;'
            . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    protected function _getFieldsContainerFooter()
    {
        $html = '</tbody></table></fieldset></td></tr>';

        return $html;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {
            if ($field->getId() == 'moogento_shipeasy_general_override_bl_grid') {
                if (!Mage::helper('moogento_core')->isInstalled('BL_CustomGrid')) {
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('BL grid', 'bl_grid', 1);
                }
            }

            $html .= $field->toHtml();

            if ($field->getId() == 'moogento_shipeasy_general_override_bl_grid') {
                if (!(Mage::helper('moogento_core')->isInstalled('BL_CustomGrid'))) {
                    $html .= $this->_getFieldsContainerFooter();
                }
            }
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}
