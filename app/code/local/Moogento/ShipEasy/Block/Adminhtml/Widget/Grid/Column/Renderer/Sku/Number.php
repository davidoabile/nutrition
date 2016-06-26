<?php

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Sku_Number
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    public function render(Varien_Object $row)
    {
        if (is_null($row->getData('szy_sku_number'))) {
            return '<p style="color:grey;font-style:italic;" title="">'.Mage::helper('moogento_shipeasy')->__("Pending Sync") . '</p>';
        } else {
            $img = '<img style="height: 16px;" src="';
            $img .= $row->getData('szy_sku_number') ? $this->getSkinUrl('moogento/shipeasy/images/szy_sku_multiple.png')
                : $this->getSkinUrl('moogento/shipeasy/images/szy_sku_single.png');
            $img .= '" alt="' . ($row->getData('szy_sku_number') ? $this->__('Multi-SKU Order')
                    : $this->__('Single-SKU Order')) . '" />';

            return $img;
        }
    }

    public function renderExport(Varien_Object $row)
    {
        return $row->getData('szy_sku_number') ? $this->__('Multi-SKU Order') : $this->__('Single-SKU Order');
    }
} 