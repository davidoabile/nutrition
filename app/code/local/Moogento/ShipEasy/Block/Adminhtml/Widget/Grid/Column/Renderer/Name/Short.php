<?php 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Name_Short
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $resulting_string  =  '<div class="bill_ship_for_form" data-url="'.$this->getUrl('*/sales_grid/showAddressForm', array('order_id' => $row->getId(), 'type' => $this->getColumn()->getIndex())).'">';
        $resulting_string .= ($this->getColumn()->getIndex() == 'billing_name') ? $row->getBillingName() : $row->getShippingName() ;
        $resulting_string .= '</div><span class="edit-icon"></span>';
        return $resulting_string;
    }

    public function renderExport(Varien_Object $row)
    {
        return ($this->getColumn()->getIndex() == 'billing_name') ? $row->getBillingName() : $row->getShippingName();
    }
}
