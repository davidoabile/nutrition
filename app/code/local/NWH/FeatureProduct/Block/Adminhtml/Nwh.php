<?php


class NWH_FeatureProduct_Block_Adminhtml_Nwh extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_nwh";
	$this->_blockGroup = "featureproduct";
	$this->_headerText = Mage::helper("featureproduct")->__("Single Feature Product");
	$this->_addButtonLabel = Mage::helper("featureproduct")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}