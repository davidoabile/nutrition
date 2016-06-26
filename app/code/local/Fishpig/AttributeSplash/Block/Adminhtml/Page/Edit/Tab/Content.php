<?php
/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_AttributeSplash_Block_Adminhtml_Page_Edit_Tab_Content extends Fishpig_AttributeSplash_Block_Adminhtml_Page_Edit_Tab_Abstract
{
	/**
	 * Prepare the form
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();
		
		$fieldset = $this->getForm()->addFieldset('splash_page_content', array(
			'legend'=> $this->helper('adminhtml')->__('Content'),
			'class' => 'fieldset-wide',
		));

		$htmlConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
			'add_widgets' => true,
			'add_variables' => true,
			'add_image' => true,
			'files_browser_window_url' => $this->getUrl('adminhtml/cms_wysiwyg_images/index')
		));

		$fieldset->addField('video', 'editor', array(
			'name' 		=> 'video',
			'label' 	=> $this->__('Video (code)'),
			'title' 	=> $this->__('Video (code)'),
			'required'	=> FALSE,
		));

		$fieldset->addField('promo_block', 'editor', array(
			'name' 		=> 'promo_block',
			'label' 	=> $this->__('Promo Block'),
			'title' 	=> $this->__('Promo Block'),
			'required'	=> FALSE,
			'style' => 'width:100%; height:400px;',
			'config' => $htmlConfig,
		));

		$fields = array(
			'short_description' => 'Short Description',
			'description' => 'Description',
		);
		
		foreach($fields as $field => $label) {
			$fieldset->addField($field, 'editor', array(
				'name' => $field,
				'label' => $this->helper('adminhtml')->__($label),
				'title' => $this->helper('adminhtml')->__($label),
				'style' => 'width:100%; height:400px;',
				'config' => $htmlConfig,
			));
		}

		$this->getForm()->setValues($this->_getFormData());

		return $this;
	}
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
	}
}
