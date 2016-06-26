<?php
class Magebright_Orderamount_Block_Adminhtml_Config_Customergroup
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_itemRenderer;

    public function _prepareToRender()
    {
        $this->addColumn('customer_group', array(
            'label' => Mage::helper('orderamount')->__('Customer Group'),
            'renderer' => $this->_getRenderer(),
        ));
        $this->addColumn('minimum_amount', array(
            'label' => Mage::helper('adminhtml')->__('Minimum Amount'),
            'size' => 28
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('orderamount')->__('Add Minimum amount');
    }

    protected function  _getRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'orderamount/adminhtml_config_groupoption', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()
                ->calcOptionHash($row->getData('customer_group')),
            'selected="selected"'
        );
    }
}