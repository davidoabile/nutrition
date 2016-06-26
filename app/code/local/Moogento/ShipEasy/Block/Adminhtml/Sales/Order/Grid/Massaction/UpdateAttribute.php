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
 * File        UpdateAttribute.php
 *
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
 * @license    http://moogento.com/License.html
 */
class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Massaction_UpdateAttribute
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_preset = null;

    protected function _getPreset($id)
    {
        if (is_null($this->_preset)) {
            $this->_preset = array();

            for ($i = 1; $i <= 3; $i++) {
                if ($i == 1) {
                    $configSuffix = 'szy_custom_attribute_preset';
                } else if ($i == 2) {
                    $configSuffix = 'szy_custom_attribute2_preset';
                } else {
                    $configSuffix = 'szy_custom_attribute3_preset';
                }
                $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $configSuffix);
                $configPresets = explode("\n", $configPresets);
                $presets       = array();
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

                $this->_preset[ $i ] = $presets;
            }
        }

        return $this->_preset[ $id ];
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addType('szypreset', 'Moogento_ShipEasy_Block_Adminhtml_Fieldset_Form_Element_Select');

        $_attributes = array();
        for ($i = 1; $i <= 3; $i++) {
            if ($i == 1) {
                $_attributes[ $i ] = Mage::getStoreConfig("moogento_shipeasy/grid/szy_custom_attribute_header");
            } else if ($i == 2) {
                $_attributes[ $i ] = Mage::getStoreConfig("moogento_shipeasy/grid/szy_custom_attribute2_header");
            } else {
                $_attributes[ $i ] = Mage::getStoreConfig("moogento_shipeasy/grid/szy_custom_attribute3_header");
            }
        }

        $form->addField('szy_attr_no', 'select', array(
            'label'   => Mage::helper('catalogrule')->__('Attr'),
            'title'   => Mage::helper('catalogrule')->__('Attr'),
            'name'    => 'attr',
            'options' => $_attributes,
        ));

        $form->addField('szy_attr_preset_1', 'szypreset', array(
            'label'   => Mage::helper('catalogrule')->__('Preset'),
            'title'   => Mage::helper('catalogrule')->__('Preset'),
            'name'    => 'preset1',
            'options' => $this->_getPreset(1),
            'class'   => 'heszy_attr_preset',
        ));

        $form->addField('szy_attr_preset_2', 'szypreset', array(
            'label'   => Mage::helper('catalogrule')->__('Preset'),
            'title'   => Mage::helper('catalogrule')->__('Preset'),
            'name'    => 'preset2',
            'options' => $this->_getPreset(2),
            'class'   => 'heszy_attr_preset',
        ));
        $form->addField('szy_attr_preset_3', 'szypreset', array(
            'label'   => Mage::helper('catalogrule')->__('Preset'),
            'title'   => Mage::helper('catalogrule')->__('Preset'),
            'name'    => 'preset3',
            'options' => $this->_getPreset(3),
            'class'   => 'heszy_attr_preset',
        ));

        $form->addField('szy_attr_custom_text', 'text', array(
            'name'  => 'custom_text',
            'label' => null,
            'title' => '',
            'class' => 'szy_attr_custom_text',
        ));

        $this->setForm($form);

        return $this;
    }
}
