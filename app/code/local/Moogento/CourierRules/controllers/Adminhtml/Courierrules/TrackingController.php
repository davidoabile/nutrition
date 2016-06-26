<?php


class Moogento_CourierRules_Adminhtml_Courierrules_TrackingController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function exportCsvAction()
    {
        $fileName   = 'cr_tracking.csv';
        $grid       = $this->getLayout()->createBlock('moogento_courierrules/adminhtml_tracking_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
} 