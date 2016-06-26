<?php

class NWH_FeatureProduct_Block_Adminhtml_Nwh_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("nwhGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("featureproduct/nwh")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("featureproduct")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("sku", array(
				"header" => Mage::helper("featureproduct")->__("SKU"),
				"index" => "sku",
				));
						$this->addColumn('status', array(
						'header' => Mage::helper('featureproduct')->__('TYPE'),
						'index' => 'status',
						'type' => 'options',
						'options'=>NWH_FeatureProduct_Block_Adminhtml_Nwh_Grid::getOptionArray1(),				
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
			$this->getMassactionBlock()->addItem('remove_nwh', array(
					 'label'=> Mage::helper('featureproduct')->__('Remove Nwh'),
					 'url'  => $this->getUrl('*/adminhtml_nwh/massRemove'),
					 'confirm' => Mage::helper('featureproduct')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray1()
		{
            $data_array=array(); 
			$data_array[0]='Featured Product';
			$data_array[1]='Weekly Featured Product';
            return($data_array);
		}
		static public function getValueArray1()
		{
            $data_array=array();
			foreach(NWH_FeatureProduct_Block_Adminhtml_Nwh_Grid::getOptionArray1() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}