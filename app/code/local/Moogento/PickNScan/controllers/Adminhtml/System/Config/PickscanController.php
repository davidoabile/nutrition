<?php


class Moogento_PickNScan_Adminhtml_System_Config_PickscanController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function printAuthCardAction()
    {
        $id = $this->getRequest()->getParam('id');
        $user = Mage::getModel('admin/user')->load($id);

        if ($user->getId()) {
            require_once 'mpdf/mpdf.php';

            $mpdf=new mPDF('', array(120, 60), 20, 'dejavusans', 5, 5, 5, 5);
            $mpdf->AddPage('P');

            $block = $this->getLayout()->createBlock('adminhtml/template')->setTemplate('moogento/pickscan/pdf/auth.phtml');
            $block->setName($user->getFirstname() . ' ' . $user->getLastname());
            $block->setCode(substr(md5($user->getUsername()) ,0, 6));

            $mpdf->WriteHTML($block->toHtml());

            return $this->_prepareDownloadResponse('authcard.pdf', $mpdf->Output('', 'S'), 'application/pdf');
        } else {
            $this->_redirectReferer();
        }
    }
} 