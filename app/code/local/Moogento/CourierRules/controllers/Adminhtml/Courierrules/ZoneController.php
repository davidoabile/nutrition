<?php


class Moogento_CourierRules_Adminhtml_Courierrules_ZoneController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function exportCsvAction()
    {
        $fileName   = 'cr_zones.csv';
        $grid       = $this->getLayout()->createBlock('moogento_courierrules/adminhtml_zone_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
} 