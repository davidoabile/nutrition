<?php
class NWH_FeatureProduct_Block_Adminhtml_Nwh_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("featureproduct_form", array("legend"=>Mage::helper("featureproduct")->__("Item information")));

				
						$fieldset->addField("sku", "text", array(
						"label" => Mage::helper("featureproduct")->__("SKU"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "sku",
						));
									
						 $fieldset->addField('status', 'select', array(
						'label'     => Mage::helper('featureproduct')->__('TYPE'),
						'values'   => NWH_FeatureProduct_Block_Adminhtml_Nwh_Grid::getValueArray1(),
						'name' => 'status',
						));

				if (Mage::getSingleton("adminhtml/session")->getNwhData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getNwhData());
					Mage::getSingleton("adminhtml/session")->setNwhData(null);
				} 
				elseif(Mage::registry("nwh_data")) {
				    $form->setValues(Mage::registry("nwh_data")->getData());
				}
				return parent::_prepareForm();
		}
}
