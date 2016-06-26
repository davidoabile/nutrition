<?php

class Qtm_Faq_Block_Adminhtml_Faq_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("faqGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("faq/faq")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("faq")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("question", array(
				"header" => Mage::helper("faq")->__("Question"),
				"index" => "question",
				));
						$this->addColumn('status', array(
						'header' => Mage::helper('faq')->__('Status'),
						'index' => 'status',
						'type' => 'options',
						'options'=>Qtm_Faq_Block_Adminhtml_Faq_Grid::getOptionArray2(),				
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
			$this->getMassactionBlock()->addItem('remove_faq', array(
					 'label'=> Mage::helper('faq')->__('Remove Faq'),
					 'url'  => $this->getUrl('*/adminhtml_faq/massRemove'),
					 'confirm' => Mage::helper('faq')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array[0]='Active';
			$data_array[1]='In Active';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(Qtm_Faq_Block_Adminhtml_Faq_Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}