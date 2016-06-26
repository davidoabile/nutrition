<?php 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Gift
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $gift_message_id = $row->getGiftMessageId();
        if(!empty($gift_message_id)) {
            return '<img src="' . $this->getSkinUrl('moogento/shipeasy/images/szy-gift.png') . '" title="Gift" width="16" height="16" />';
        }
        return '';
    }
}