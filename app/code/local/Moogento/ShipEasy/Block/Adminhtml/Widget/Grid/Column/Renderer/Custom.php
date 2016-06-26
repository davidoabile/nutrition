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
 * File        Custom.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Custom
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    protected $preset_id = 1;

    protected function _getCurrencySelectBoxHtml($row)
    {
        $codes = Mage::helper('moogento_shipeasy/functions')->getCustomPreset($this->preset_id);
        array_unshift($codes, '');
        $value = trim($row->getData($this->getColumn()->getIndex()));
        $html
               =
            '<select class="chosen-select" name="custom' . $this->preset_id . '_' . $row->getData('entity_id') . '">';
        foreach ($codes as $code) {
            $selected = ($code == $value);
            if ($selected) {
                $html .= '<option selected="selected" value="' . $code . '">' . $code . '</option>';
            } else {
                $html .= '<option value="' . $code . '">' . $code . '</option>';
            }
        }
        $html .= '</select>';

        return $html;
    }

    public function render(Varien_Object $row)
    {
        $value     = trim($row->getData($this->getColumn()->getIndex()));

        if ($value == "{{date}}") {
            $value = date("m-d-Y", Mage::getModel('core/date')->timestamp(time()));
        } else {
            $render_data = Mage::helper('moogento_shipeasy/functions')->renderCustom($this->getColumn()->getIndex(),
                $value);
        }
        $input_data = "";
        
        try {
            if (isset($render_data['flag']) && (strlen($render_data['flag']) > 0)) {
                $flag      = $render_data['flag'];
                $flag      = trim($flag);
                $flag      = str_replace('{{', '', $flag);
                $flag      = str_replace('}}', '', $flag);
                $flag      = str_replace('{', '', $flag);
                $flag      = str_replace('}', '', $flag);
                $image_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)
                             . 'adminhtml/default/default/moogento/shipeasy/images/flag_images/' . $flag;
                $html      = '<div class="custom_color"><img style="height: 25px !important;" class="custom_flag szy_grid_image" src="' . $image_url . '" /></div>';
            } else if (strtotime($value) !== false) {
                $html = '<div class="custom_color date_value">' . $value . '</div>';
            } else if (isset($render_data['color']) && (strlen($render_data['color']) > 0)) {
                $color = $render_data['color'];
                $color = ' background-color:' . $color;
                $html  = '<div class="custom_color" style="padding: 2px;' . $color . '">' . $value . '</div>';
            } else {
                $input_data = $value;
                $html = '<div class="custom_color">' . $value . '</div>';
            }

            $extra_str = '<div><input type="text" class="input_custom_value" name="custom' . $this->preset_id . '_'
                         . $row->getData('entity_id') . '" id="custom_value_' . $this->preset_id . '_'
                         . $row->getData('entity_id') . '" style="display:none;"'
                         . '></div>';

            $html .= $this->_getCurrencySelectBoxHtml($row);
            $html .= $extra_str;
            $html = '<div class="szy_custom_col_data">'.$html.'</div>';
            
            return $html;
        } catch (Exception $e) {
            return '&nbsp;';
        }
    }

    public function renderExport(Varien_Object $row)
    {
        $value = trim($row->getData($this->getColumn()->getIndex()));
        if ($value == "{{date}}") {
            $value = date("m-d-Y", Mage::getModel('core/date')->timestamp(time()));
        }

        return $value;
    }

}
