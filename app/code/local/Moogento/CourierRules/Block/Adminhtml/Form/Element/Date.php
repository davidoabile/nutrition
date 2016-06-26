<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 21.06.14
 * Time: 22:58
 */

class Moogento_CourierRules_Block_Adminhtml_Form_Element_Date extends Varien_Data_Form_Element_Date
{
    public function getHtmlAttributes()
    {
        $attr = parent::getHtmlAttributes();
        $attr[] = 'data-bind';
        return $attr;
    }

    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s />'
            .' <img src="%s" alt="" class="v-middle" id="%s_trig" title="%s" style="%s" />',
            $this->getName(), $this->getHtmlId(), $this->_escape($this->getValue()), $this->serialize($this->getHtmlAttributes()),
            $this->getImage(), $this->getHtmlId(), 'Select Date', ($this->getDisabled() ? 'display:none;' : '')
        );
        $html .= $this->getAfterElementHtml();

        return $html;
    }
} 