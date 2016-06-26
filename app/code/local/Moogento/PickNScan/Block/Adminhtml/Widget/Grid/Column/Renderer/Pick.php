<?php

class Moogento_PickNScan_Block_Adminhtml_Widget_Grid_Column_Renderer_Pick
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $image = '';

        /** @var Moogento_PickNScan_Model_Picking $picking */
        $picking = $row->getPicking();
        if (!$picking) {
            $picking = Mage::getModel('moogento_pickscan/picking')->load($row->getEntityId());
        }

        switch ($picking->getStatus()) {
            case Moogento_PickNScan_Model_Picking::STATUS_ASSIGNED:
                $image = '<img style="width:16px;height:16px;" src="' . $this->getSkinUrl('moogento/pickscan/images/pick_notstarted.png') . '" alt="" title="' . $this->__('Assigned') . '"/>';
                break;
            case Moogento_PickNScan_Model_Picking::STATUS_STARTED:
                $image = '<img style="width:16px;height:16px;" src="' . $this->getSkinUrl('moogento/pickscan/images/pick_incomplete.png') . '" alt="" title="' . $this->__('Started') . '"/>';
                break;
            case Moogento_PickNScan_Model_Picking::STATUS_COMPLETE:
                $image = '<img style="width:16px;height:16px;" src="' . $this->getSkinUrl('moogento/pickscan/images/pick_complete.png') . '" alt="" title="' . $this->__('Complete') . '"/>';
                break;
            case Moogento_PickNScan_Model_Picking::STATUS_COMPLETE_ANOMALIES:
                $image = '<img style="width:16px;height:16px;" src="' . $this->getSkinUrl('moogento/pickscan/images/pick_anomalies.png') . '" alt="" title="' . $this->__('Complete with a sub or a skip') . '"/>';
                break;
        }

        return $image ? $image : '';
    }

    public function renderExport(Varien_Object $row)
    {
        /** @var Moogento_PickNScan_Model_Picking $picking */
        $picking = $row->getPicking();
        if (!$picking) {
            $picking = Mage::getModel('moogento_pickscan/picking')->load($row->getEntityId());
        }

        switch ($picking->getStatus()) {
            case Moogento_PickNScan_Model_Picking::STATUS_ASSIGNED:
                return $this->__('Assigned');
            case Moogento_PickNScan_Model_Picking::STATUS_STARTED:
                return $this->__('Started');
            case Moogento_PickNScan_Model_Picking::STATUS_COMPLETE:
                return $this->__('Complete');
            case Moogento_PickNScan_Model_Picking::STATUS_COMPLETE_ANOMALIES:
                return $this->__('Complete with a sub or a skip');
        }

        return '';
    }
}