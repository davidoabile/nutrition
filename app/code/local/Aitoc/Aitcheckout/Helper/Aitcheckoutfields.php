<?php
/**
 * One Step Checkout Manager : One Step Checkout Manager (OPCB Unit)
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckout / Aitoc_Aitcheckout
 * @version      1.0.9 - 1.4.9
 * @license:     Nichj4LUEMsSNLvlLmobwL49OlCNVmKqxOe78SZxGK
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckout_Helper_Aitcheckoutfields extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;
    
    protected $_version = null;

    /**
     * Main CFM model
     * 
     * @var Aitoc_Aitcheckoutfields_Model_Aitcheckoutfields
     */
    protected $_cfmModel;
    
    /**
     * Check whether the CFM module is active or not
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        if($this->_isEnabled === null)
        {
            $this->_isEnabled = $this->isModuleEnabled('Aitoc_Aitcheckoutfields')?true:false;
            if($this->_isEnabled)
            {
                $this->_cfmModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            }
        }
        return $this->_isEnabled;
    }
    
    public function getVersion()
    {  
        if($this->_version === null)
        {
            $this->_version = Mage::getConfig()->getModuleConfig('Aitoc_Aitcheckoutfields')->version;
        }
        return $this->_version;
    }
    
    /**
     * Retrieve custom field's html code from the CFM module
     * 
     * @param string $step Checkout step name
     * @param string $field Custom field unique code
     * 
     * @return string
     */
    public function getFieldHtml($step, $field, $type = 'onepage')
    {
        return $this->_cfmModel->getAttributeHtml($field, $step, $type);
    }
    
    /**
     * Retrieve custom fields list from the CFM module
     * 
     * @param string $step Checkout step name
     * @param integer $templatePlaceId Indicates whether fields will be placed at the top of a template or at the bottom (1-top; 2-bottom).
     * 
     * @return array or boolean false
     */
    public function getCustomFieldList($step, $templatePlaceId)
    {
        $fields = false;
        
        if($this->isEnabled())
        {
            $stepId = Mage::helper('aitcheckoutfields')->getStepId($step);
            if ($stepId)
            {
                $fields = $this->_cfmModel->getCheckoutAtrributeList($stepId, $templatePlaceId, 'onepage');
            }
        }
        
        return $fields;
    }
    

    public function getRegCustomFieldList()
    {
        $fields = false;
        
        if($this->isEnabled())
        {
            if (version_compare($this->getVersion(), "2.5.3", "<")) return false;
                
            $stepId = Mage::helper('aitcheckoutfields')->getStepId('billing');
            
            if ($stepId)
            {
                $fieldsTmp = $this->_cfmModel->getCustomerAtrributeList();
                if($fieldsTmp)
                {
                    $fields = array();
                    foreach($fieldsTmp as $placeholder)
                    {
                        foreach ($placeholder as $id => $data)
                        {
                            if(!$data['is_searchable'])
                            {
                                $fields[$id]=$data;
                            }
                        }
                    }
                }
            }
        }
        return $fields;
    }
    

    /**
     * Retrieve custom fields list to be used on registration page
     * 
     * @param integer $templatePlaceId Indicates whether fields will be placed at the top of a template or at the bottom (false-all; 1-top; 2-bottom).
     * 
     * @return array or boolean false
     */
    public function getCustomerFieldList($templatePlaceId = false)
    {
        $fields = false;
        if($this->isEnabled())
        {
            $fields = $this->_cfmModel->getCustomerAtrributeList($templatePlaceId);
            if(!$templatePlaceId && is_array($fields))
            {
                $fieldsTmp = array();
                foreach($fields as $fieldsArr)
                {
                    $fieldsTmp = array_merge($fieldsTmp, $fieldsArr);
                }
                $fields = $fieldsTmp;
            }
        }
        
        return $fields;
    }
    
    /**
     * Compatibility with CC module
     * 
     * @return boolean 
     */
    public function checkStepHasRequired()
    {
        if($this->isEnabled())
        {
            $iStepId = Mage::helper('aitcheckoutfields')->getStepId('shippinfo');
        
            if (!$iStepId) return false;

            return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->checkStepHasRequired($iStepId, 'onepage');
        }
        return false;
    }
    
    public function saveCustomData($data)
    {
        if ($this->isEnabled())
        {
            if ($data)
            {
                $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
                
                foreach ($data as $sKey => $sVal)
                {
                    $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
                }
            }        
        }
    }
    
}