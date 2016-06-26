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
 * File        Functions.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_Shipeasy_Helper_Functions extends Mage_Core_Helper_Abstract
{
    private function clean_object($dirty)
    {
        if (is_object($dirty)) {
            $dirty = strval($dirty);
        }

        return $dirty;
    }

    private function to_utf8($in)
    {
        if (is_array($in)) {
            $out = array();
            foreach ($in as $key => $value) {
                $out[ $this->to_utf8($key) ] = $this->to_utf8($value);
            }
            return $out;
        } elseif (is_string($in)) {
            if (mb_detect_encoding($in) != "UTF-8") {
                return utf8_encode($in);
            } else {
                return $in;
            }
        } else {
            return $in;
        }
    }

    public function clean($dirty)
    {
        $dirty = $this->clean_object($dirty);
        $clean = str_replace(array("\n", "\r", '<br/>', '<br>', '<br />'), '~', $dirty);//,'\n','\r'
        $clean = strip_tags($clean);
        $clean = $this->to_utf8($clean);
        $clean = str_replace('~', "\n", $clean);

        return $clean;
    }

    public function getCustomPreset($att_number = 1)
    {

        if ($att_number == 1) {
            $configSuffix = 'szy_custom_attribute_preset';
        } else if ($att_number == 2) {
            $configSuffix = 'szy_custom_attribute2_preset';
        } else {
            $configSuffix = 'szy_custom_attribute3_preset';
        }
        $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $configSuffix);
        $configPresets = explode("\n", $configPresets);

        $presets = array();
        foreach ($configPresets as $preset) {
            $preset = trim($preset);
            if (empty($preset)) {
                continue;
            }
            if (strpos($preset, '|') !== false) {
                list($label, $color) = explode('|', $preset);
                $presets[ $preset ] = $label;

            } else {
                $presets[ $preset ] = $preset;
            }
        }
        $presets['custom'] = 'New Value';

        return $presets;
    }

    public function renderCustom($column_index, $value)
    {
        $configValues      = Mage::getStoreConfig('moogento_shipeasy/grid/' . $column_index . '_preset');
        $return_data       = Array();
        $configValuesLines = explode("\n", $configValues);
        foreach ($configValuesLines as $line) {
            try {
                $line = trim($line);
                if (!$line) {
                    continue;
                }
                @list($label, $check_value) = explode('|', $line);
                if (strtolower($label) == strtolower($value)) {
                    if ((preg_match('/png/', $check_value) != 0) || (preg_match('/gif/', $check_value) != 0)
                        || (preg_match('/jp*g/', $check_value) != 0)
                    ) {
                        $return_data['flag'] = $check_value;

                        return $return_data;
                    } else {
                        if (preg_match('/^#[a-f0-9]{6}$/i', $check_value)) {
                            $return_data['color'] = $check_value;

                            return $return_data;
                        }
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return false;
    }

    public function getValueSetStockOption($configSuffix)
    {
        $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $configSuffix);
        $configPresets = explode("\n", $configPresets);
        $image_url     = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)
                         . 'adminhtml/default/default/moogento/shipeasy/images/stock_images/';

        $presets = array();
        for ($i = 0; $i < (count($configPresets)); ++$i) {
            $preset = trim($configPresets[ $i ]);
            if (empty($preset)) {
                continue;
            }
            if (strpos($preset, '|') !== false) {
                list($label, $img) = explode('|', $preset);
                $presets[ $i ] = array(
                    "img"   => $image_url . str_replace("{{", "", str_replace("}}", "", $img)),
                    "label" => $label
                );
            } else {
                $presets[ $i ] = array("label" => $preset);
            }

        }

        return $presets;
    }
}
