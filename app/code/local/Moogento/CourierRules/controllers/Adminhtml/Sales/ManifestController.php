<?php


class Moogento_CourierRules_Adminhtml_Sales_ManifestController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function listAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function toggleReturnedAction()
    {
        $id = $this->getRequest()->getPost('id');
        $manifest = Mage::getModel('moogento_courierrules/connector_manifest')->load($id);
        if ($manifest->getId()) {
            $manifest->setReturned((int)$this->getRequest()->getPost('value'));
            $manifest->save();
        }
    }

    public function togglePrintedAction()
    {
        $id = $this->getRequest()->getPost('id');
        $manifest = Mage::getModel('moogento_courierrules/connector_manifest')->load($id);
        if ($manifest->getId()) {
            $manifest->setPrinted((int)$this->getRequest()->getPost('value'));
            $manifest->save();
        }
    }

} 