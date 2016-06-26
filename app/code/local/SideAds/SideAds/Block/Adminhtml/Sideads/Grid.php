<?php

class SideAds_SideAds_Block_Adminhtml_Sideads_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("sideadsGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("sideads/sideads")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("sideads")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("banner_name", array(
				"header" => Mage::helper("sideads")->__("Banner Name"),
				"index" => "banner_name",
				));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_sideads', array(
					 'label'=> Mage::helper('sideads')->__('Remove Sideads'),
					 'url'  => $this->getUrl('*/adminhtml_sideads/massRemove'),
					 'confirm' => Mage::helper('sideads')->__('Are you sure?')
				));
			return $this;
		}
			

}