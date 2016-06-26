<?php

class NWH_FeatureProduct_Adminhtml_Featureproduct_NwhController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("featureproduct/nwh")->_addBreadcrumb(Mage::helper("adminhtml")->__("NWH Manager"),Mage::helper("adminhtml")->__("NWH Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Feature Product"));
			    $this->_title($this->__("Add Sku For Feature Product"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Feature Product"));
				$this->_title($this->__("NWH"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("featureproduct/nwh")->load($id);
				if ($model->getId()) {
					Mage::register("nwh_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("featureproduct/nwh");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NWH Manager"), Mage::helper("adminhtml")->__("NWH Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NWH Description"), Mage::helper("adminhtml")->__("NWH Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("featureproduct/adminhtml_nwh_edit"))->_addLeft($this->getLayout()->createBlock("featureproduct/adminhtml_nwh_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("featureproduct")->__("SKU does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Feature Product"));
		$this->_title($this->__("NWH"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("featureproduct/nwh")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("nwh_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("featureproduct/nwh");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NWH Manager"), Mage::helper("adminhtml")->__("NWH Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("NWH Description"), Mage::helper("adminhtml")->__("NWH Description"));


		$this->_addContent($this->getLayout()->createBlock("featureproduct/adminhtml_nwh_edit"))->_addLeft($this->getLayout()->createBlock("featureproduct/adminhtml_nwh_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("featureproduct/nwh")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("SKU was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setNwhData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setNwhData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("featureproduct/nwh");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("SKU was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("featureproduct/nwh");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("SKU(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
}
