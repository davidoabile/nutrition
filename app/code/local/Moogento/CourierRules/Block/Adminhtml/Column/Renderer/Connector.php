<?php

class Moogento_CourierRules_Block_Adminhtml_Column_Renderer_Connector extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $collection = Mage::getModel('moogento_courierrules/connector')->getCollection();
        $collection->getSelect()->join(
            array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
            'main_table.shipment_id = shipment.entity_id',
            array('shipment.increment_id')
        );
        $collection->getSelect()->where('shipment.order_id = ?', $row->getId());

        $item = $collection->getFirstItem();
        if ($item->getId()) {
            if ($item->getStatus() == 'DELETED') {
                return '<img style="width:16px" src="' . $this->getSkinUrl('moogento/courierrules/images/label_deleted.png') . '" title="' . $item->getStatusMessage() . '"/>';
            } else if ($item->getLabel()) {
                return '<img style="width:16px" src="' . $this->getSkinUrl('moogento/courierrules/images/label_ready.png') . '" title="' . $item->getStatusMessage() . '"/>';
            } else {
                return '<img style="width:16px" src="' . $this->getSkinUrl('moogento/courierrules/images/label_problem.png') . '" title="' . $item->getStatusMessage() . '"/>';
            }
        } else {
            return '<span style="color:grey;font-style:italic;" title="">' . Mage::helper('moogento_courierrules')->__('Pending Sync') . '</span>';
        }
    }
} 