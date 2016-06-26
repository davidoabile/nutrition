<?php

class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Renderer_Ebayuserid
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        if (Mage::helper('moogento_core')->isInstalled('Ess_M2ePro')) {
            $return = '';
            $customer_email = $row->getData('szy_customer_email');
            if (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_ebay_customer_id_show_email')) {
                $return .= $customer_email . '<br/>';
            }
            $return .= trim($row->getData('szy_ebay_customer_id'));
            return $return;
        }
    }

    public function renderExport(Varien_Object $row)
    {
        return strip_tags($this->render($row));
    }
}
