<?php
class Qtm_Faq_Block_Adminhtml_Faq_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("faq_form", array("legend"=>Mage::helper("faq")->__("Item information")));

				
						$fieldset->addField("question", "text", array(
						"label" => Mage::helper("faq")->__("Question"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "question",
						));
					
						$fieldset->addField("answer", "textarea", array(
						"label" => Mage::helper("faq")->__("Answer"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "answer",
						));
									
						$fieldset->addField('status', 'select', array(
						'label'     => Mage::helper('faq')->__('Status'),
						'values'   => Qtm_Faq_Block_Adminhtml_Faq_Grid::getValueArray2(),
						'name' => 'status',
						));

				if (Mage::getSingleton("adminhtml/session")->getFaqData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getFaqData());
					Mage::getSingleton("adminhtml/session")->setFaqData(null);
				} 
				elseif(Mage::registry("faq_data")) {
				    $form->setValues(Mage::registry("faq_data")->getData());
				}
				return parent::_prepareForm();
		}
}
