<?php
class SideAds_SideAds_Block_Adminhtml_Sideads_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("sideads_form", array("legend"=>Mage::helper("sideads")->__("Item information")));

				
						$fieldset->addField("banner_name", "text", array(
						"label" => Mage::helper("sideads")->__("Banner Name"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "banner_name",
						));
									
						$fieldset->addField('banner_image', 'image', array(
						'label' => Mage::helper('sideads')->__('Banner Image'),
						'name' => 'banner_image',
						'note' => 'Image should be 190 x 409 pixels.(*.jpg, *.png, *.gif)',
						));

				if (Mage::getSingleton("adminhtml/session")->getSideadsData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getSideadsData());
					Mage::getSingleton("adminhtml/session")->setSideadsData(null);
				} 
				elseif(Mage::registry("sideads_data")) {
				    $form->setValues(Mage::registry("sideads_data")->getData());
				}
				return parent::_prepareForm();
		}
}
