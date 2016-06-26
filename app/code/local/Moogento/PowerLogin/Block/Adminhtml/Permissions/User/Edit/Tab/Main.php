<?php



class Moogento_PowerLogin_Block_Adminhtml_Permissions_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{

    protected function _prepareForm()
    {
        
        $model = Mage::registry('permissions_user');
        
        $parent = parent::_prepareForm();

        $form = $this->getForm();

        $fieldset = $form->getElement('base_fieldset');
        
        
        $fieldset->addField('home_page', 'select', array(
            'name'  => 'home_page',
            'label' => Mage::helper('adminhtml')->__('Start Page'),
            'id'    => 'home_page',
            'title' => Mage::helper('adminhtml')->__('Start Page'),
            'required' => false,
            'values' => Mage::getModel('moogento_powerlogin/system_admin_startup_page')->toOptionArray(),
            'value' => $model->getData('home_page'),
        ));

        

        return $parent;
    }
}
