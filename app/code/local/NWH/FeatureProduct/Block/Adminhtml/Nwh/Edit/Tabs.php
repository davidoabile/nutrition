<?php
class NWH_FeatureProduct_Block_Adminhtml_Nwh_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("nwh_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("featureproduct")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("featureproduct")->__("Item Information"),
				"title" => Mage::helper("featureproduct")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("featureproduct/adminhtml_nwh_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
