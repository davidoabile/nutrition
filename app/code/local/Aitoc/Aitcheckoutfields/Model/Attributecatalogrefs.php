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
/**
 * @refactor Direct usage of the database in all methods
 */
class Aitoc_Aitcheckoutfields_Model_Attributecatalogrefs extends Mage_Core_Model_Abstract
{

    protected $_sCRAttrTable = 'aitoc_custom_attribute_cat_refs';    
    
    public function _construct()
    {
        parent::_construct();
        $this->_sCRAttrTable    = Mage::getSingleton('core/resource')->getTableName('aitoc_custom_attribute_cat_refs');
        $this->_init('aitcheckoutfields/attributecategoryrefs');
    }    
    
    
    public function getRefs($iAttributeId, $sType)
    {

        $oDb = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $oDb->select()
            ->from(array('c' => $this->_sCRAttrTable), array('value'))
            ->where('c.attribute_id=?', (int)$iAttributeId)
            ->where('c.type=?',$sType);

        return $oDb->fetchCol($select);
    }
    
    
    public function saveRefs($iAttributeId, $sType,$aValues)
    {
       $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
       $this->deleteRefs($iAttributeId, $sType);
         
        
        foreach ($aValues as $iValue)
        {
            if($iValue)
            {
                $aDBInfo = array
                (
                    'attribute_id'  => $iAttributeId,
                    'type' => $sType,
                    'value'     => $iValue,
                );

                $oDb->insert($this->_sCRAttrTable, $aDBInfo);
            }
        }
        return $this;
    }
    
    public function deleteRefs($iAttributeId, $sType)
    {
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $oDb->delete($this->_sCRAttrTable, array('attribute_id = ?' => $iAttributeId, 'type = ?'=> $sType));
        return $this;
    }            
    
}