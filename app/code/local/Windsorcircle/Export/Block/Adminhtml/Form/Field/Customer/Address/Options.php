<?php
/**
 * Customer Address Options Class
 *
 * @category  Lyons
 * @package   Windsorcircle_Export
 * @author    Mark Hodge <mhodge@lyonscg.com>
 * @copyright Copyright (c) 2014 Lyons Consulting Group (www.lyonscg.com)
 */ 

class Windsorcircle_Export_Block_Adminhtml_Form_Field_Customer_Address_Options extends Mage_Core_Block_Html_Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_customerAddressAttributes;

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    protected function _getCustomerAddressAttributes()
    {
        if (is_null($this->_customerAddressAttributes)) {
            $this->_customerAddressAttributes = array();

            $type = Mage::getModel('eav/entity_type')->loadByCode('customer_address');
            $collection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($type);

            $this->_customerAddressAttributes[0] = '';
            foreach ($collection as $item) {
                $label = $item->getFrontendLabel();
                if (!empty($label)) {
                    /* @var $item Mage_Catalog_Model_Resource_Eav_Attribute */
                    $this->_customerAddressAttributes[$item->getAttributeCode()] = $label;
                }
            }
        }
        return $this->_customerAddressAttributes;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCustomerAddressAttributes() as $attributeCode => $label) {
                $this->addOption($attributeCode, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
