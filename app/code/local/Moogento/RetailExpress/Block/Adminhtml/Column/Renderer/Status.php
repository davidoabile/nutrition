<?php


class Moogento_RetailExpress_Block_Adminhtml_Column_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    public function render(Varien_Object $row)
    {
        $status = $row->getData($this->getColumn()->getIndex());

        $image = 'status_pending.png';
        $text = 'Pending';
        switch ($status) {
            case Moogento_RetailExpress_Model_Retailexpress_Status::SUCCESS:
                $image = 'status_success.png';
                $text = $this->__('Success');
                break;
            case Moogento_RetailExpress_Model_Retailexpress_Status::SUCCESS_MANUAL:
                $image = 'status_success_manual.png';
                $text = $this->__('Success (Manual)');
                break;
            case Moogento_RetailExpress_Model_Retailexpress_Status::PENDING_RETRY:
                $image = 'status_pending_retry.png';
                $text = $this->__('Rending retry: ') . $row->getRetailExpressMessage();
                break;
            case Moogento_RetailExpress_Model_Retailexpress_Status::ERROR:
                $image = 'status_error.png';
                $text = $this->__('Error: ') . $row->getRetailExpressMessage();
                break;
        }

        return '<img src="' . $this->getSkinUrl('moogento/retailexpress/' . $image) . '" title="' . $text . '" style="width: 16px;" />';
    }
} 