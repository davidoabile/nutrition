<?php


class Moogento_Shipeasy_Block_Adminhtml_System_Config_Fieldset_Key
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if (Mage::getStoreConfig('moogento_shipeasy/moodetails/license')) {
            $html =<<<HTML
<span id="licence_mark"></span>
<script>
    Event.observe(window, 'load', function() {
        $('licence_mark').update('<a href="//moogento.com" target="_blank"><img width="121" src="{$this->getMark()}" alt="" border="0" style="margin: -4px 0 -12px -14px;"/></a>');
    });
</script>
HTML;
            $element->setComment($html);
        }
        return parent::render($element);
    }

    public function getMark()
    {
        return Mage::getStoreConfig('moogento/general/url') . 'media/moo_mark/' . Mage::helper('moogento_shipeasy/moo')->m() . '/moogento_shipeasy.png';
    }
} 