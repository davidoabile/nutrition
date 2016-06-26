<?php
	
class NWH_FeatureProduct_Block_Adminhtml_Nwh_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "featureproduct";
				$this->_controller = "adminhtml_nwh";
				$this->_updateButton("save", "label", Mage::helper("featureproduct")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("featureproduct")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("featureproduct")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("nwh_data") && Mage::registry("nwh_data")->getId() ){

				    return Mage::helper("featureproduct")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("nwh_data")->getSku()));

				} 
				else{

				     return Mage::helper("featureproduct")->__("Add Item");

				}
		}
}