<?php

class SideAds_SideAds_Adminhtml_Sideads_SideadsController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("sideads/sideads")->_addBreadcrumb(Mage::helper("adminhtml")->__("Side Ads  Manager"),Mage::helper("adminhtml")->__("Side Ads Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Side Ads"));
			    $this->_title($this->__("Manager Side Ads"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Side Ads"));
				$this->_title($this->__("Side Ads"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("sideads/sideads")->load($id);
				if ($model->getId()) {
					Mage::register("sideads_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("sideads/sideads");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Side Ads Manager"), Mage::helper("adminhtml")->__("Side Ads Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Side Ads Description"), Mage::helper("adminhtml")->__("Side Ads Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("sideads/adminhtml_sideads_edit"))->_addLeft($this->getLayout()->createBlock("sideads/adminhtml_sideads_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sideads")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Side Ads"));
		$this->_title($this->__("Side Ads"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("sideads/sideads")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("sideads_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("sideads/sideads");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Side Ads Manager"), Mage::helper("adminhtml")->__("Sideads Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Side Ads Description"), Mage::helper("adminhtml")->__("Side Ads Description"));


		$this->_addContent($this->getLayout()->createBlock("sideads/adminhtml_sideads_edit"))->_addLeft($this->getLayout()->createBlock("sideads/adminhtml_sideads_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						
				 //save image
		try{

if((bool)$post_data['banner_image']['delete']==1) {

	        $post_data['banner_image']='';

}
else {

	unset($post_data['banner_image']);

	if (isset($_FILES)){

		if ($_FILES['banner_image']['name']) {

			if($this->getRequest()->getParam("id")){
				$model = Mage::getModel("sideads/sideads")->load($this->getRequest()->getParam("id"));
				if($model->getData('banner_image')){
						$io = new Varien_Io_File();
						$io->rm(Mage::getBaseDir('media').DS.implode(DS,explode('/',$model->getData('banner_image'))));	
				}
			}
						$path = Mage::getBaseDir('media') . DS . 'sideads' . DS .'sideads'.DS;
						$uploader = new Varien_File_Uploader('banner_image');
						$uploader->setAllowedExtensions(array('jpg','png','gif'));
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						$destFile = $path.$_FILES['banner_image']['name'];
						$filename = $uploader->getNewFileName($destFile);
						$uploader->save($path, $filename);

						$post_data['banner_image']='sideads/sideads/'.$filename;
		}
    }
}

        } catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
        }
//save image


						$model = Mage::getModel("sideads/sideads")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Side Ads was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setSideadsData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setSideadsData($this->getRequest()->getPost());
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
						$model = Mage::getModel("sideads/sideads");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
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
                      $model = Mage::getModel("sideads/sideads");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
}
