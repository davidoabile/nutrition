<?php
/**
 * Created by PhpStorm.
 * User: werewolf
 * Date: 21.06.14
 * Time: 22:47
 */

class Moogento_CourierRules_Block_Adminhtml_Form_Element_Text extends Varien_Data_Form_Element_Text
{
    public function getHtmlAttributes()
    {
        $attr = parent::getHtmlAttributes();
        $attr[] = 'data-bind';
        return $attr;
    }
} 