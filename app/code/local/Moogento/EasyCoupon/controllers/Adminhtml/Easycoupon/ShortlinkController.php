<?php

class Moogento_EasyCoupon_Adminhtml_Easycoupon_ShortlinkController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout()
             ->renderLayout();
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('moogento_easycoupon/shortlink')->load($id);
        if ($model->getId()) {
            $model->delete();
        }

        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        foreach ($ids as $id) {
            $model = Mage::getModel('moogento_easycoupon/shortlink')->load($id);
            if ($model->getId()) {
                $model->delete();
            }
        }

        $this->_redirectReferer();
    }
} 