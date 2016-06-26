<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (CFM Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckoutfields
 * @version      1.0.9 - 2.9.8
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
abstract class Aitoc_Aitcheckoutfields_Model_Field_Abstract extends Mage_Core_Model_Abstract
{
    protected $_eventObject = 'field';
    
    protected $_attribute;
    
    protected $_fieldType;
    
    public function getFieldType()
    {
        return $this->_fieldType;
    }
    
    public function getAttribute()
    {
        if(is_null($this->_attribute) && $this->getAttributeId())
        {
            $this->_attribute = Mage::getModel('eav/entity_attribute')->load($this->getAttributeId());
        }
        return $this->_attribute;
    }
    
    public function getAttributeCode()
    {
        return $this->getAttribute()->getAttributeCode();
    }
}