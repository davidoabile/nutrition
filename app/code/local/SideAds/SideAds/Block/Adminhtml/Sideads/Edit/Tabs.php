<?php
class SideAds_SideAds_Block_Adminhtml_Sideads_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("sideads_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("sideads")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("sideads")->__("Item Information"),
				"title" => Mage::helper("sideads")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("sideads/adminhtml_sideads_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
