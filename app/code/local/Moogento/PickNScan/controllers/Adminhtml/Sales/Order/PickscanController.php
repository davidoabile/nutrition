<?php

class Moogento_PickNScan_Adminhtml_Sales_Order_PickscanController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction()
    {
//        if (!Mage::helper('moogento_pickscan/moo')->vl()) {
//            $this->_getSession()->addError('PickNScan isn\'t registered yet. Please add a valid key or buy the latest version');
//            $this->_redirectReferer('*/sales_order');
//            return;
//        }

        $adminSession = Mage::getSingleton('admin/session');
        if (!$adminSession->isAllowed('moogento/pickscan/pick') && !$adminSession->isAllowed('moogento/pickscan/quickpick')) {
            $this->_forward('denied');
        }

        $ids = Mage::app()->getRequest()->getParam('ids');
        if ($ids) {
            $ids = explode(',', $ids);
        }
        $custom1Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_1');
        $custom2Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_2');
        $custom3Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_3');
        $custom4Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_4');
        $titleCode = Mage::getStoreConfig('moogento_pickscan/settings/show_in_title');
        $sortCode = Mage::getStoreConfig('moogento_pickscan/settings/sort_by');
        if ($titleCode == 'custom') {
            $titleCode = Mage::getStoreConfig('moogento_pickscan/settings/show_in_title_custom');
        }

        $pickScanData = array(
            'settings' => array(
                'allowTrolley' => is_array($ids) && count($ids) > 1,
                'customSortName' => $this->_getAttributeLabel(Mage::getStoreConfig('moogento_pickscan/settings/sort_by')),
                'customFirstShow' => !!$custom1Code,
                'customFirstName' => $this->_getAttributeLabel($custom1Code),
                'customSecondShow' => !!$custom2Code,
                'customSecondName' => $this->_getAttributeLabel($custom2Code),
                'customThirdShow' => !!$custom3Code,
                'customThirdName' => $this->_getAttributeLabel($custom3Code),
                'customForthShow' => !!$custom4Code,
                'customForthName' => $this->_getAttributeLabel($custom4Code),
                'custom1Code' => $custom1Code,
                'custom2Code' => $custom2Code,
                'custom3Code' => $custom3Code,
                'custom4Code' => $custom4Code,
                'titleCode' => $titleCode,
                'sortCode' => $sortCode,
                'autoreturn' => Mage::getStoreConfigFlag('moogento_pickscan/settings/autoreturn'),

                'barcodeAttribute' => Mage::getStoreConfig('moogento_pickscan/settings/barcode'),
                'showConfigurableOptions' => Mage::getStoreConfigFlag('moogento_pickscan/settings/show_configurable_options'),
                'show_in_title' => Mage::getStoreConfigFlag('moogento_pickscan/settings/show_in_title'),

                'allowSubstitution' => Mage::getStoreConfigFlag('moogento_pickscan/manual_substitution/enable'),
                'substitution_status' => Mage::getStoreConfig('moogento_pickscan/manual_substitution/set_order_status'),
                'substitution_flag' => Mage::helper('moogento_pickscan')->getSubstitutionFlag(),

                'allowIgnore' => Mage::getStoreConfigFlag('moogento_pickscan/ignore_error/enable'),
                'ignore_status' => Mage::getStoreConfig('moogento_pickscan/ignore_error/set_order_status'),
                'ignore_flag' => Mage::helper('moogento_pickscan')->getIgnoreFlag(),

                'order_progress' => Mage::getStoreConfigFlag('moogento_pickscan/settings/order_progress'),
                'trolley_progress' => Mage::getStoreConfigFlag('moogento_pickscan/settings/trolley_progress'),
                'total_progress' => Mage::getStoreConfigFlag('moogento_pickscan/settings/total_progress'),
                'enable_commenting' => Mage::getStoreConfigFlag('moogento_pickscan/settings/enable_commenting'),
                'enable_correct_sound' => Mage::getStoreConfigFlag('moogento_pickscan/settings/enable_correct_sound'),

                'assign_tracking' => Mage::getStoreConfigFlag('moogento_pickscan/settings/assign_tracking'),

                'allowance' => Mage::getStoreConfigFlag('moogento_pickscan/manual_substitution/allowance_enable'),

                'barcodeUpdateAuth' => Mage::getStoreConfigFlag('moogento_pickscan/settings/barcode_update_auth'),
            ),
            'orders' => array(),
        );

        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        if (count($ids)) {
            $hasFinishedPicking = false;
            foreach ($ids as $id) {
                $order = Mage::getModel('sales/order')->load($id);
                if ($order->getId() && !$order->getIsVirtual() && $picking = Mage::helper('moogento_pickscan')->assignOrder($order, $userId)) {
                    if ($picking->getFinished()) {
                        $hasFinishedPicking = true;
                    } else {
                        $picking->start();
                        $orderData = Mage::helper('moogento_pickscan')->getOrderJsonData($order);
                        if ($orderData) {
                            $pickScanData['orders'][] = $orderData;
                        }
                    }
                }
            }
            if (!count($pickScanData['orders'])) {
                if ($hasFinishedPicking) {
                    $this->_getSession()->addError(Mage::helper('moogento_pickscan')->__('The order' . (count($ids) > 1 ? 's have' :' has') .' already been picked' ));
                } else {
                    $this->_getSession()->addError(Mage::helper('moogento_pickscan')->__('Could not assign order' . (count($ids) > 1 ? 's' :'') .'. Check order assignments and/or order items (orders with only virtual/downloadable orders are not assigned)' ));
                }
                $this->_redirect('*/sales_order');
                return;
            }
        } else if (Mage::app()->getRequest()->getParam('quickpick') && $adminSession->isAllowed('moogento/pickscan/quickpick')) {
            try {
                $orders = Mage::helper('moogento_pickscan')->getAssignedOrders();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/sales_order');
                return;
            }
            foreach ($orders as $order) {
                if (count($order->getOrder()->getAllVisibleItems()) && !$order->getIsVisible()) {
                    $data = Mage::helper('moogento_pickscan')->getOrderJsonData($order->getOrder());
                    if ($data) {
                        $pickScanData['orders'][] = $data;
                    }
                }
            }
            if (count($orders) > 1) {
                $pickScanData['settings']['allowTrolley'] = true;
            }
        }

        Mage::register('pickScanData', Mage::helper('core')->jsonEncode($pickScanData));

        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _getAttributeLabel($code)
    {
        $attribute = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $code);
        return $attribute->getFrontendLabel();
    }

    public function loadOrderAction()
    {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $result = array(
            'success' => false,
        );

        if (!$order_id) {
            $result['message'] = $this->__('Please scan order ID');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);

        if (!$order->getId()) {
            $result['message'] = $this->__('Wrong order ID');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();

        $picking = Mage::helper('moogento_pickscan')->assignOrder($order, $userId);

        if (!$picking) {
            $result['message'] = $this->__('Order is assigned to other user');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        if ($picking->getFinished()) {
            $result['message'] = $this->__('Order has already been picked');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $picking->start();

        $result['success'] = true;
        $result['order'] = Mage::helper('moogento_pickscan')->getOrderJsonData($order);
        if (!$result['order']) {
            $result['success'] = false;
            $result['message'] = $this->__('Nothing to pick in order');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function assignAction() {
        $ids = Mage::app()->getRequest()->getPost('order_ids');

        $userId = Mage::app()->getRequest()->getPost('user_id');
        $counter = 0;
        foreach ($ids as $id) {
            $order = Mage::getModel('sales/order')->load($id);
            if ($order->getId() && $picking = Mage::helper('moogento_pickscan')->assignOrder($order, $userId)) {
                $counter++;
            }
        }

        if ($counter > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%d orders assigned', $counter));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('All orders are already assigned'));
        }
        $this->_redirectReferer();
    }

    public function unassignAction()
    {
        $ids = Mage::app()->getRequest()->getPost('order_ids');

        if (count($ids)) {
            $collection = Mage::getModel('moogento_pickscan/picking')->getCollection()->addFieldToFilter('entity_id', array('in' => $ids))->load();
            foreach ($collection as $one) {
                $order = Mage::getModel('sales/order')->load($one->getId());
                $order->addStatusHistoryComment("Pick'N'Scan: <br/>Picking status was reset", false);
                $order->save();
                $one->delete();
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Pick Checkout Status was cleared for %d orders', count($ids)));
        $this->_redirectReferer();
    }

    public function finishAction()
    {
        $orders = Mage::app()->getRequest()->getPost('orders');
        $orders = Mage::helper('core')->jsonDecode($orders);
        $result = array();
        foreach ($orders as $orderData) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderData['id']);
            $picking = Mage::getModel('moogento_pickscan/picking')->load($order->getEntityId());
            if ($picking) {
                $pickResult = $picking->finish($orderData);
                if (isset($pickResult['errors'])) {
                    if (!isset($result['errors'])) {
                        $result['errors'] = array();
                    }
                    $result['errors'] = array_merge($result['errors'], $pickResult['errors']);
                }
            }
        }
        if (!isset($result['errors'])) {
            $result['success'] = true;
        } else {
            $result['errors'] = implode('<br/>', $result['errors']);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveTrackingAction()
    {
        $orderId = $this->getRequest()->getPost('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $result = array();
        try {
            if ($order->getId()) {
                $trackingNumber = $this->getRequest()->getPost('tracking');
                $carrier  = $this->getRequest()->getPost('carrier', false);

                if ($trackingNumber) {
                    if (Mage::getStoreConfigFlag('moogento_carriers/general/warn_no_matching') && !$carrier) {
                        $carrierInfo = Mage::helper('moogento_core/carriers')->getCarrierForTrackingNumber($trackingNumber);
                        if (!$carrierInfo) {
                            $result['errors'] = Mage::helper('moogento_core')->__('No matching carrier found');
                        }
                    }
                    if (!isset($result['errors'])) {
                        if (count($order->getShipmentsCollection())) {
                            foreach ($order->getShipmentsCollection() as $shipment) {
                                Mage::helper('moogento_core/carriers')
                                    ->addTrackingToShipment($shipment, $trackingNumber, $carrier);
                                $shipment->save();
                            }
                        } else {
                            if (Mage::helper('moogento_core')->isInstalled('Moogento_ShipEasy')) {
                                $order->setData('preshipment_tracking',
                                    $trackingNumber . ($carrier ? '||' . $carrier : ''));
                            } else if (Mage::helper('moogento_core')->isInstalled('Moogento_CourierRules')) {
                                $order->setCourierrulesTracking($trackingNumber . ($carrier ? '||' . $carrier : ''));
                            }
                            $order->save();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $result['errors'] = $e->getMessage();
        }
        if (!isset($result['errors'])) {
            $result['success'] = true;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function abortAction()
    {
        $orders = Mage::app()->getRequest()->getPost('ids');
        foreach ($orders as $orderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $picking = Mage::getModel('moogento_pickscan/picking')->load($order->getEntityId());
            if ($picking) {
                $picking->abort();
            }
        }

        $result['success'] = true;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveConditionAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        $config = new Mage_Core_Model_Config();
        $config->saveConfig('moogento_pickscan/condition/sort', $session->getData('sales_order_gridsort'));
        $config->saveConfig('moogento_pickscan/condition/dir', $session->getData('sales_order_griddir'));
        $config->saveConfig('moogento_pickscan/condition/filter', $session->getData('sales_order_gridfilter'));
        Mage::getConfig()->reinit();
        Mage::app()->getResponse()->setBody(1);
    }

    public function generateTrolleyBarcodesAction()
    {
        require_once 'mpdf/mpdf.php';

        $pageSize = Mage::getStoreConfig('moogento_pickscan/trolley/page_size') ? Mage::getStoreConfig('moogento_pickscan/trolley/page_size') : 'A4';
        if ($pageSize == 'custom') {
            $pageSize = array(Mage::getStoreConfig('moogento_pickscan/trolley/page_width'), Mage::getStoreConfig('moogento_pickscan/trolley/page_height'));
            $mpdf=new mPDF('', $pageSize, 12, 'dejavusans', 5, 5, 5, 5);
        } else {
            $mpdf=new mPDF('', $pageSize, 12, 'dejavusans', 15, 15, 5, 5);
        }


        $mpdf->AddPage('P');

        $block = $this->getLayout()->createBlock('adminhtml/template')->setTemplate('moogento/pickscan/pdf/trolley.phtml');

        $mpdf->WriteHTML($block->toHtml());

        return $this->_prepareDownloadResponse('trolley_barcodes.pdf', $mpdf->Output('', 'S'), 'application/pdf');
    }

    public function packAction()
    {
        $adminSession = Mage::getSingleton('admin/session');
        if (!$adminSession->isAllowed('moogento/pickscan/pack')) {
            $this->_forward('denied');
        }

        $custom1Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_1');
        $custom2Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_2');
        $custom3Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_3');
        $custom4Code = Mage::getStoreConfig('moogento_pickscan/settings/custom_4');
        $settings = array(
            'customSortName' => $this->_getAttributeLabel(Mage::getStoreConfig('moogento_pickscan/settings/sort_by')),
            'customFirstShow' => !!$custom1Code,
            'customFirstName' => $this->_getAttributeLabel($custom1Code),
            'customSecondShow' => !!$custom2Code,
            'customSecondName' => $this->_getAttributeLabel($custom2Code),
            'customThirdShow' => !!$custom3Code,
            'customThirdName' => $this->_getAttributeLabel($custom3Code),
            'customForthShow' => !!$custom4Code,
            'customForthName' => $this->_getAttributeLabel($custom4Code),
            'showConfigurableOptions' => Mage::getStoreConfigFlag('moogento_pickscan/settings/show_configurable_options'),
            'show_in_title' => Mage::getStoreConfigFlag('moogento_pickscan/settings/show_in_title'),
            'enable_correct_sound' => Mage::getStoreConfigFlag('moogento_pickscan/settings/enable_correct_sound'),
            'assign_tracking' => Mage::getStoreConfigFlag('moogento_pickscan/settings/assign_tracking'),
        );

        Mage::register('settings', $settings);

        $this->loadLayout()
            ->renderLayout();
    }

    public function loadBoxDataAction()
    {
        $helper = Mage::helper('moogento_pickscan');
        $boxBarcode = $this->getRequest()->getPost('box');
        $result = array();

        if (strpos($boxBarcode, 'PKNSCN') === false) {
            $result['error'] = $helper->__('Wrong box ID format');
        } else {
            @list($trolley, $box) = explode('-', str_replace('PKNSCN', '', $boxBarcode));
            if (!$trolley || !$box) {
                $result['error'] = $helper->__('Wrong box ID format');
            } else {
                $pickingCollection = Mage::getModel('moogento_pickscan/picking')->getCollection();
                $pickingCollection->addFieldToFilter('trolley_id', $trolley);
                $pickingCollection->addFieldToFilter('box', $box);
                $pickingCollection->getSelect()->order('finished DESC');
                $picking = $pickingCollection->getFirstItem();
                if ($picking->getId()) {
                    $packstation = $this->getRequest()->getPost('packstation');
                    $order = Mage::getModel('sales/order')->load($picking->getId());
                    $this->_startPack($picking, $order, $packstation);
                    $data = $helper->getOrderJsonData($order);
                    $data['trolley'] = $trolley;
                    $data['box'] = $box;
                    $data['pick_results'] = $picking->getResults();
                    $result['data'] = $data;
                } else {
                    $result['error'] = $helper->__('Missing data about this box');
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _startPack($picking, $order, $packstation)
    {
        $picking->setPackstation($packstation);
        $picking->save();
        switch (Mage::getStoreConfig('moogento_pickscan/pack/print')) {
            case 'email':
                $this->_sendPrintEmail($order);
                break;
            case 'ftp':
                $this->_printFtpUpload($order);
                break;
        }

        if (Mage::getStoreConfigFlag('moogento_pickscan/pack/api_enable')
            && Mage::getStoreConfig('moogento_pickscan/pack/api_url')
            && Mage::getStoreConfig('moogento_pickscan/pack/api_start')) {
            $uri = Mage::getStoreConfig('moogento_pickscan/pack/api_url');
            $client = new Zend_XmlRpc_Client($uri);
            $method = Mage::getStoreConfig('moogento_pickscan/pack/api_start');
            $paramsList = explode(',', Mage::getStoreConfig('moogento_pickscan/pack/api_start_params'));
            $params = array();
            foreach ($paramsList as $param) {
                $params[] = $this->_getParamValue($param, $picking, $order, $packstation);
            }

            try {
                $client->call($method, $params);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    protected function _sendPrintEmail($order)
    {
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra(array($order->getId()));

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */

        $template = Mage::getStoreConfig('moogento_pickscan/pack/print_template');

        $reportFileName = 'zebra_label_' . $order->getIncrementId() . '.pdf';
        $mailTemplate
            ->getMail()
            ->createAttachment( $pdf->render(), 'application/pdf' )
            ->filename = $reportFileName;

        $mailTemplate
            ->sendTransactional(
                $template,
                'general',
                Mage::getStoreConfig('moogento_pickscan/pack/print_email'),
                Mage::getStoreConfig('moogento_pickscan/pack/print_email'),
                array()
            );

        $translate->setTranslateInline(true);
    }

    protected function _printFtpUpload($order)
    {
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_labelzebra')->getLabelzebra(array($order->getId()));

        $ftp = new Varien_Io_Ftp();
        $ftp->open(
            array(
                'host' => Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_host'),
                'port' => Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_post'),
                'user'  => Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_username'),
                'password'  => Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_password'),
            )
        );

        if (Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_path')) {
            $ftp->cd(Mage::getStoreConfig('moogento_pickscan/pack/print_ftp_path'));
        }
        $ftp->write('zebra_label_' . $order->getIncrementId() . '.pdf', $pdf->render());
        $ftp->close();
    }

    public function finishPackAction()
    {
        $orderId = $this->getRequest()->getPost('order_id');
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                if (Mage::getStoreConfigFlag('moogento_pickscan/pack/mark_complete')) {
                    Mage::helper('moogento_core')->changeOrderStatus($order->getId(), Mage_Sales_Model_Order::STATE_COMPLETE, Mage::helper('moogento_pickscan')->__('Packing finished'), Mage::getStoreConfigFlag('moogento_pickscan/settings/notify_status'));
                }

                if (Mage::getStoreConfigFlag('moogento_pickscan/pack/api_enable')
                    && Mage::getStoreConfig('moogento_pickscan/pack/api_url')
                    && Mage::getStoreConfig('moogento_pickscan/pack/api_stop')) {
                    $picking = Mage::getModel('moogento_pickscan/picking')->load($orderId);
                    $uri = Mage::getStoreConfig('moogento_pickscan/pack/api_url');
                    $client = new Zend_XmlRpc_Client($uri);
                    $method = Mage::getStoreConfig('moogento_pickscan/pack/api_stop');
                    $paramsList = explode(',', Mage::getStoreConfig('moogento_pickscan/pack/api_stop_params'));
                    $params = array();
                    foreach ($paramsList as $param) {
                        $params[] = $this->_getParamValue($param, $picking, $order, $picking->getPackstation());
                    }

                    try {
                        $client->call($method, $params);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    public function printPackingAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault(array($orderId), 'order', 'pack');
            Mage::helper('pickpack')->updatePrintedTime(array($orderId), 'packingsheet_pdf');
            Mage::dispatchEvent(
                'moo_pp_pack_pdf_generate_after',
                array('order_ids' => array($orderId))
            );

            //Default store config
            if(Mage::getStoreConfig("pickpack_options/wonder/additional_action_change_order_status_yn") == 1)
            {
                Mage::dispatchEvent(
                    'moo_pp_pack_pdf_manual_generate_after',
                    array('order_ids' => array($orderId))
                );
            }

            return $this->_prepareDownloadResponse('packing-sheet_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function printCombinedAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined(array($orderId), 'order_combined');
            Mage::helper('pickpack')->updatePrintedTime(array($orderId), 'ordercombined_pdf');
            return $this->_prepareDownloadResponse('pick-list-combined_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    public function printSeparateAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_separated')->getPickSeparated(array($orderId));
            Mage::helper('pickpack')->updatePrintedTime(array($orderId),'orderseparated_pdf');

            return $this->_prepareDownloadResponse('pick-list-separated_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

    protected function _getParamValue($paramName, $picking, $order, $packstation)
    {
        switch ($paramName) {
            case 'account_id':
                return Mage::getStoreConfig('moogento_pickscan/pack/api_account_id');
            case 'user_id':
                return Mage::getSingleton('admin/session')->getUser()->getUsername();
            case 'packstation_id':
                return $packstation;
            case 'overlay_a':
                $text = Mage::getStoreConfig('moogento_pickscan/pack/overlay_text_a');
                return $this->_prepareOverlayText($text, $picking, $order, $packstation);
            case 'overlay_b':
                $text = Mage::getStoreConfig('moogento_pickscan/pack/overlay_text_b');
                return $this->_prepareOverlayText($text, $picking, $order, $packstation);
        }

        return '';
    }

    protected function _prepareOverlayText($text, $picking, $order, $packstation)
    {
        $user = $this->_getSession()->getUser();
        return str_replace(array(
            '{{order_id}}',
            '{{name}}'
        ), array(
            $order->getIncrementId(),
            $user->getFirstname() . ' ' . $user->getLastname(),
        ), $text);
    }
}