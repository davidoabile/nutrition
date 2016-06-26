<?php
/**
 * Customer Options Class
 *
 * @category  Lyons
 * @package   Windsorcircle_Export
 * @author    Mark Hodge <mhodge@lyonscg.com>
 * @copyright Copyright (c) 2014 Lyons Consulting Group (www.lyonscg.com)
 */ 

class Windsorcircle_Export_Block_Adminhtml_Form_Field_Customer_Options extends Mage_Core_Block_Html_Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_customerAttributes;

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    protected function _getCustomerAttributes()
    {
        if (is_null($this->_customerAttributes)) {
            $this->_customerAttributes = array();

            $type = Mage::getModel('eav/entity_type')->loadByCode('customer');
            $collection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($type);

            $this->_customerAttributes[0] = '';
            foreach ($collection as $item) {
                $label = $item->getFrontendLabel();
                if (!empty($label)) {
                    /* @var $item Mage_Catalog_Model_Resource_Eav_Attribute */
                    $this->_customerAttributes[$item->getAttributeCode()] = $label;
                }
            }
        }
        return $this->_customerAttributes;
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
            foreach ($this->_getCustomerAttributes() as $attributeCode => $label) {
                $this->addOption($attributeCode, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
