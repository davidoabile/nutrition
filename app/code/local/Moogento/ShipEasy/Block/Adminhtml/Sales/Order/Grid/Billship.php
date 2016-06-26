<?php 

class Moogento_ShipEasy_Block_Adminhtml_Sales_Order_Grid_Billship extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Address
{
    protected $_preset = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/address/form.phtml');
    }

    protected function _getAddress()
    {
        switch ($this->getType()) {
            case 'bill':
                return $this->getOrder()->getBillingAddress();
            case 'ship':
                return $this->getOrder()->getShippingAddress();
        }
    }

    protected function _getFieldsAttribute()
    {
        $fields = array();
        switch ($this->getType()) {
            case 'bill':
                $fields = explode(",", Mage::getStoreConfig('moogento_shipeasy/grid/billing_name_fields'));
                break;
            case 'ship':
                $fields = explode(",", Mage::getStoreConfig('moogento_shipeasy/grid/shipping_name_fields'));
                break;
        }
        if (in_array('region_field', $fields)) {
            array_splice( $fields, array_search('region_field', $fields), 0, array('region', 'region_id') );
        }

        return $fields;
    }
    
    protected function _getAllFieldsAttribute()
    {
        $addressForm = Mage::getModel('customer/form')
            ->setFormCode('adminhtml_customer_address')
            ->setStore(Mage::app()->getStore()->getId())
            ->setEntity(Mage::getModel('customer/address'));
        $attributes = $addressForm->getAttributes();
        
        $list = array();
        foreach($attributes as $attribute){
            $list[] = $attribute->getAttributeCode(); 
        }
        return $list;
    }
    
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_form->setId('ship_bill_form');
        $this->_form->setClass('send_address_by_ajax');
        $this->_form->setMethod('post');
        $this->_form->setAction($this->getUrl('*/sales_grid/addressSave', array('address_id' => $this->_getAddress()->getId())));
        $this->_form->setUseContainer(true);
        
        $fieldset = $this->_form->getElement('main');        
        $fields = array_diff($this->_getAllFieldsAttribute(),$this->_getFieldsAttribute());
        
        foreach($fields as $field_id){
            $fieldset->removeField($field_id);
        }

        $fieldset->addField('hidden', 'hidden', array(
          'required'    => true,
          'name'        => 'type_billship',
          'value'       => $this->getType(),
          'visible'     => false,
        ));
        
        $fieldset->addField('submit', 'submit', array(
            'required'  => true,
            'value'     => 'Save',
            'tabindex'  => 1,
            'class'     => 'btn-bs-form btn-bs-form-submit unchanging',
            'after_element_html'=> '<input id="reset" name="" value="Cancel" class="btn-bs-form btn-bs-form-cancel" type="button">'
        ));

        return $this;
    }
    
    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Order Address Information');
    }

    public function getFormValues()
    {
        return $this->_getAddress()->getData();
    }    
}
