<?php

class Moogento_CourierRules_Model_Resource_Rule extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('moogento_courierrules/rule', 'id');
        if (Mage::helper('moogento_courierrules')->isProductAttributeMultiple('product_attribute')) {
            $this->_serializableFields['product_attribute'] = array(null, null, true);
        }
        if (Mage::helper('moogento_courierrules')->isProductAttributeMultiple('product_attribute2')) {
            $this->_serializableFields['product_attribute2'] = array(null, null, true);
        }
        if (Mage::helper('moogento_courierrules')->isProductAttributeMultiple('product_attribute3')) {
            $this->_serializableFields['product_attribute3'] = array(null, null, true);
        }
        
    }

    public function unsetProductAttribute()
    {
        $this->_getWriteAdapter()->update($this->getMainTable(), array('product_attribute' => null));
    }
    
    public function unsetProductAttribute2()
    {
        $this->_getWriteAdapter()->update($this->getMainTable(), array('product_attribute2' => null));
    }

    public function unsetProductAttribute3()
    {
        $this->_getWriteAdapter()->update($this->getMainTable(), array('product_attribute3' => null));
    }


}