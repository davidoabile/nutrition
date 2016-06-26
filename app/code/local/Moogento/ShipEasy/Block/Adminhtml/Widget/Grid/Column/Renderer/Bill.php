<?php 

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Bill
    extends Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer
{
    protected $_block_name = 'moogento_shipeasy/adminhtml_sales_order_grid_billship';
    
    public function render(Varien_Object $row)
    {
        return $this->getBlock()
                ->setOrder($row)
                ->setType('bill')
                ->toHtml();
    }
}
