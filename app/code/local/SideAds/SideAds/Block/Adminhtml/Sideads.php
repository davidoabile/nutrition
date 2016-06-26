<?php


class SideAds_SideAds_Block_Adminhtml_Sideads extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_sideads";
	$this->_blockGroup = "sideads";
	$this->_headerText = Mage::helper("sideads")->__("Side Ads Manager");
	$this->_addButtonLabel = Mage::helper("sideads")->__("Add New Item");
	parent::__construct();
	
	}

}