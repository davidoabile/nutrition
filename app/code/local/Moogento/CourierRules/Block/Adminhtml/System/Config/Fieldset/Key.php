<?php


class Moogento_CourierRules_Block_Adminhtml_System_Config_Fieldset_Key
    extends Mage_Core_Block_Abstract
{
    public function _toHtml()
    {
        $html = '';
        if (Mage::getStoreConfig('courierrules_rules/moodetails/license')) {
            $html =<<<HTML
<span id="licence_mark"></span>
<script>
    Event.observe(window, 'load', function() {
        $('licence_mark').update('<a href="//moogento.com" target="_blank"><img width="121" src="{$this->getMark()}" alt="" border="0" style="margin: -4px 0 -12px -14px;"/></a>');
    });
</script>
HTML;
        } else {
            $html = '<p class="note">' . $this->__('Enter your license key here') . '</p>';
        }
        return $html;
    }

    public function getMark()
    {
        return Mage::getStoreConfig('moogento/general/url') . 'media/moo_mark/' . Mage::helper('moogento_courierrules/moo')->m() . '/moogento_courierrules.png';
    }
} 