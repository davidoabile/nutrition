<?php


class Moogento_RetailExpress_Block_Adminhtml_Column_Renderer_Id extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $retailId = $row->getData($this->getColumn()->getIndex());
        $html = '';
        if ($retailId) {
            $url = Mage::helper('moogento_retailexpress')->getRetailViewUrl($row);
            if ($url) {
                $html .= '<a href="' . $url . '" target="_blank">';
            }
            $html .= $row->getData($this->getColumn()->getIndex());
            if ($url) {
                $html .= '</a>';
            }
        }

        return $html;
    }
} 