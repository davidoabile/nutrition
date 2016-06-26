<?php

class Wyomind_Ordersexporttool_Adminhtml_ProfilesController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('sales/ordersexporttool')
                ->_addBreadcrumb($this->__('Orders Export Tool'), ('Orders Export Tool'));

        return $this;
    }

    public function indexAction() {


        $this->_initAction()
                ->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/ordersexporttool/ordersexporttoolprofiles');
    }

    public function editAction() {


        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ordersexporttool/profiles')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('ordersexporttool_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('sales/ordersexporttool')->_addBreadcrumb(Mage::helper('ordersexporttool')->__('Orders Export Tool'), ('Orders Export Tool'));
            $this->_addBreadcrumb(Mage::helper('ordersexporttool')->__('Orders Export Tool'), ('Orders Export Tool'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()
                            ->createBlock('ordersexporttool/adminhtml_profiles_edit'))
                    ->_addLeft($this->getLayout()
                            ->createBlock('ordersexporttool/adminhtml_profiles_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ordersexporttool')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {

        $this->_forward('edit');
    }

    public function saveAction() {

        // check if data sent
        if ($data = $this->getRequest()->getPost()) {

            $data['file_store_id'] = implode(',', $data['file_store_id']);

            if (isset($data['file_product_type']))
                $data['file_product_type'] = implode(',', $data['file_product_type']);
            else
                $data['file_product_type'] = "";


            // init model and set data
            $model = Mage::getModel('ordersexporttool/profiles');

            if ($this->getRequest()->getParam('file_id')) {
                $model->load($this->getRequest()->getParam('file_id'));
            }


            $model->setData($data);

            // try to save it
            try {

                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ordersexporttool')->__('The profile has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('continue')) {
                    $this->getRequest()->setParam('id', $model->getFileId());
                    $this->_forward('edit');
                    return;
                }


                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('file_id', $model->getFileId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {

                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction() {

        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('ordersexporttool/profiles');
                $model->setId($id);
                // init and load ordersexporttool model


                $model->load($id);
                // delete file
                if ($model->getFileName() && file_exists($model->getPreparedFilename())) {
                    unlink($model->getPreparedFilename());
                }
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ordersexporttool')->__('The export file configuration has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/');
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ordersexporttool')->__('Unable to find the export file configuration to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    public function sampleAction() {

        // init and load ordersexporttool model
        $id = $this->getRequest()->getParam('file_id');


        $ordersexporttool = Mage::getModel('ordersexporttool/profiles');
        $ordersexporttool->setId($id);
        $ordersexporttool->_limit = Mage::getStoreConfig("ordersexporttool/system/preview");

        $ordersexporttool->_display = true;


        $ordersexporttool->load($id);
        try {
            $content = $ordersexporttool->generateFile();

            print($content);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        } catch (Exception $e) {

            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->addException($e, Mage::helper('ordersexporttool')->__('Unable to generate the export file.'));
            $this->_redirect('*/*/');
        }
    }

    public function updateAction() {
        try {
            Mage::getModel("sales/order_item")->load($this->getRequest()->getPost('item_id'))->setExportTo($this->getRequest()->getPost('value'))->save();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('ordersexporttool')->__('Error : ', $e->getMessage()));
        }
    }

    public function exportAction() {

        $profile_ids = unserialize($this->getRequest()->getParam('profile_ids'));
        $order_ids = unserialize($this->getRequest()->getParam('order_ids'));

        if ($order_ids === FALSE) {
            $order_ids = $this->getRequest()->getPost('order_ids');
        }


        if ($profile_ids === FALSE) {
            $profile_ids = array();
            foreach ($order_ids as $order_id) {

                $order = Mage::getModel('sales/order')->load($order_id);
                foreach ($order->getAllItems() as $item) {
                    if ($item->getExportTo() > 0) {
                        $profile_ids[] = $item->getExportTo();
                    }
                }
            }
        }


        try {

            foreach (array_unique($profile_ids) as $profile_id) {
                $this->getRequest()->setParam('file_id', $profile_id);
                $this->getRequest()->setParam('file_attributes', '[{"line":"11","checked":true,"code":"order_item.export_to","condition":"eq","value":"' . $profile_id . '"},{"line":"12","checked":true,"code":"order.entity_id","condition":"in","value":"' . implode(',', $order_ids) . '"}]');
                //   echo($profile_id."->".'[{"line":"11","checked":true,"code":"order_item.export_to","condition":"eq","value":"' . $profile_id . '"},{"line":"12","checked":true,"code":"order.entity_id","condition":"in","value":"' . implode(',', $order_ids) . '"}]<br>');
                $this->generateAction();
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('ordersexporttool')->__('Error : ', $e->getMessage()));
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    public function generateAction() {

        // init and load ordersexporttool model
        $id = $this->getRequest()->getParam('file_id');

        $ordersexporttool = Mage::getModel('ordersexporttool/profiles');
        $ordersexporttool->setId($id);
        $limit = $this->getRequest()->getParam('limit');
        $ordersexporttool->_limit = $limit;


        // if ordersexporttool record exists
        if ($ordersexporttool->load($id)) {


            try {

                $time_start = time(true);
                $ordersexporttool->generateFile();
                $time_end = time(true);

                $time = $time_end - $time_start;
                if ($time < 60)
                    $time = ceil($time) . ' sec. ';
                else
                    $time = floor($time / 60) . ' min. ' . ($time % 60) . ' sec.';

                $unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
                $memory = @round(memory_get_usage() / pow(1024, ($i = floor(log(memory_get_usage(), 1024)))), 2) . ' ' . $unit[$i];

                $this->_getSession()->addSuccess(Mage::helper('ordersexporttool')->__('The profile "%s" has been executed. (%s - %s)', $ordersexporttool->getFileName(), $time, $memory));
                $this->_getSession()->addSuccess("--------------------------------------------------------------");
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addException($e, Mage::helper('ordersexporttool')->__('Unable to execute the profile.'));
            }
        } else {
            $this->_getSession()->addError(Mage::helper('ordersexporttool')->__('Unable to find a profile to execute.'));
        }

        // go to grid
        if ($this->getRequest()->getParam('generate'))
            $this->_redirect('*/*/edit', array("id" => $id));
        else
            $this->_redirect('*/*');
    }

    function libraryAction() {



        $types = array(
            array("code" => "order", "label" => "Order", "syntax" => "", "table" => "sales_flat_order"),
            array("code" => "order_item", "label" => "Product", "syntax" => " product", "table" => "sales_flat_order_item"),
            array("code" => "order_address", "label" => "Shipping address", "syntax" => " shipping", "table" => "sales_flat_order_address"),
            array("code" => "order_address", "label" => "Billing address", "syntax" => " billing", "table" => "sales_flat_order_address"),
            array("code" => "order_payment", "label" => "Payment", "syntax" => " payment", "table" => "sales_flat_order_payment"),
            array("code" => "invoice", "label" => "Invoice", "syntax" => " invoice", "table" => "sales_flat_invoice"),
            array("code" => "shipment", "label" => "Shipment", "syntax" => " shipment", "table" => "sales_flat_shipment"),
            array("code" => "creditmemo", "label" => "Creditmemo", "syntax" => " creditmemo", "table" => "sales_flat_creditmemo"),
        );

        function cmp($a, $b) {

            return ($a['attribute_code'] < $b['attribute_code']) ? -1 : 1;
        }

        $tabOutput = '<div id="dfm-library"><ul><h3>Attribute groups</h3> ';
        $contentOutput = '<table >';
        foreach ($types as $type) {
            if (version_compare(Mage::getVersion(), '1.5.0', '<')) {

                $resource = Mage::getSingleton('core/resource');
                $read = $resource->getConnection('core_read');
                $tableEet = $resource->getTableName('eav_entity_type');
                $select = $read->select()->from($tableEet)->where('entity_type_code IN ("' . $type['code'] . '")');

                $data = $read->fetchAll($select);
                $typeId = $data[0]['entity_type_id'];

                $attributesList = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($typeId)
                        ->addSetInfo()
                        ->getData();
            } else {

                $attributesList = array();
                $resource = Mage::getSingleton('core/resource');
                $read = $resource->getConnection('core_read');
                $tableSfo = $resource->getTableName($type['table']);
                $fields = $read->describeTable($tableSfo);
                foreach (array_keys($fields) as $field) {
                    $attributesList[]['attribute_code'] = $field;
                }
            }

            usort($attributesList, "cmp");

            $tabOutput .=" <li><a href='#" . $type['label'] . "'> " . $type['label'] . "</a></li>";


            $contentOutput .="<tr><td><a name='" . $type['label'] . "'></a><b>" . $type['label'] . "</b></td></tr>";
            foreach ($attributesList as $attribute) {


                if (!empty($attribute['attribute_code']))
                    $contentOutput.= "<tr><td><span class='pink'>{" . $attribute['attribute_code'] . "<span class='grey'>" . $type['syntax'] . "</span>}</span></td></tr>";
            }
        }
        $class = new Wyomind_Ordersexporttool_Model_Profiles;
        $myCustomAttributes = new Wyomind_Ordersexporttool_Model_MyCustomAttributes;
        foreach ($myCustomAttributes->_getAll() as $group => $attributes) {
            $tabOutput .=" <li><a href='#" . $group . "'> " . $group . "</a></li>";
            $contentOutput .="<tr><td><a name='" . $group . "'></a><b>" . $group . "</b></td></tr>";
            foreach ($attributes as $attr) {
                $contentOutput.= "<tr><td><span class='pink'>{" . $attr . "}</span></td></tr>";
            }
        }

        $contentOutput .="</table></div>";
        $tabOutput .= '</ul>';
        die($tabOutput . $contentOutput);
    }

    function changeAction() {
        $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order'));
        $flags = explode(',', $order->getExportFlag());
        $flag_to_remove = $this->getRequest()->getParam('profil');
        unset($flags[array_search($flag_to_remove, $flags)]);
        $order->setExportFlag(implode(',', $flags))->save();
    }

}
