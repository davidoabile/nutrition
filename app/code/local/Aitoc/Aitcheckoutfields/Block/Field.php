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
class Aitoc_Aitcheckoutfields_Block_Field  extends Mage_Core_Block_Abstract
{
    public function getAttributeHtml($aField, $sSetName, $sPageType, $iStoreId = 0, $bForAdmin = false)
    {
        if (!$iStoreId)
        {
    	    $iStoreId = Mage::app()->getStore()->getId();
        }
        $iItemId = $aField['attribute_id'];
        $sPrefix = 'aitoc_checkout_';

        $sFieldId = $sSetName . ':' . $sPrefix . $iItemId;
        
        $sLabel = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeLabel($iItemId, $iStoreId);
        
        if (in_array($aField['frontend_input'], array('checkbox', 'radio')))
        {
            $sLiClass = 'control';
        }
        else 
        {
            $sLiClass = 'fields';
        }
        
        $sHtml = '<li class="' . $sLiClass . '" style="clear:both;"><div class="field" style="padding-bottom: 5px'.(($aField['frontend_input']==='static')?';width:auto':'').'">';
        
        $sHtml .= '<label for="' . $sFieldId . '"';
        
        if (!$bForAdmin)
        {
            if ($aField['is_required'])
            {
                $sHtml .= ' class="required"><em>*</em>';
            }
            else 
            {
                $sHtml .= '>';
            }
            
            $sHtml .= $sLabel;
        }
        else 
        {
            $sHtml .= '>' .$sLabel;
            
            if ($aField['is_required'])
            {
                $sHtml .= ' <span class="required">*</span>';
            }
        }
        
        $sHtml .= '</label><div class="input-box"  style="'.(($aField['frontend_input']==='static')?'width:auto; ':'').'float:none;"> ';

        $sFieldName     = $sSetName . '[' . $sPrefix . $iItemId . ']';
        $sFieldValue    = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getCustomValue($aField, $sPageType);
        
        $sFieldClass = '';
        
        if ($aField['frontend_class'])
        {
            $sFieldClass .= $aField['frontend_class'];
        }
        
        if ($aField['is_required'])
        {
            $sFieldClass .= ' required-entry';
        }
        
        $aParams = array
        (
            'id' => $sFieldId,
            'class' => $sFieldClass, 
            'title' => $sLabel,
        );
                
        $aOptionHash = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getOptionValues($iItemId, $iStoreId);
        
        // add 'please select' value to option list
         
        if (!empty($aField['used_in_product_listing']) AND $aField['frontend_input'] != 'multiselect')
        {
            
            $aTitleHash = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeNeedSelect($iItemId);
            
            if ($aTitleHash AND isset($aTitleHash[$iStoreId]) AND $aTitleHash[$iStoreId])
            {
                $sNeedSelectTitle = $aTitleHash[$iStoreId];
            }
            elseif ($aTitleHash) 
            {
                $sNeedSelectTitle = current($aTitleHash);
            }
            else // must not happen
            {
                $sNeedSelectTitle = '';
            }
        
            if ($aOptionHash)
            {
                $aFullOptionHash = array('' => $sNeedSelectTitle);
                
                foreach ($aOptionHash as $iKey => $sOption)
                {
                    $aFullOptionHash[$iKey] = $sOption;
                }
                
                $aOptionHash = $aFullOptionHash;
            }
            else 
            {
                $aOptionHash = array('' => $sNeedSelectTitle);
            }
        }
                    
        $params = array(
            'aField'=>$aField,
            'sFieldName'=>$sFieldName,
            'sFieldId'=>$sFieldId,
            'sLabel'=>$sLabel,
            'sFieldClass'=>$sFieldClass,
            'sFieldValue'=>$sFieldValue,
            'aOptionHash'=>$aOptionHash,
            'aParams' => $aParams,
            'sPageType' => $sPageType,
        );

        $sHtml .= $this->getLayout()->createBlock('aitcheckoutfields/field_renderer')->setType($aField['frontend_input'])->setParams($params)->render();



        $aDescHash = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeDescription($iItemId);
        $iStoreId = Mage::app()->getStore()->getId();
        $sHtml .= '</div>';
	    
        if ($aDescHash AND isset($aDescHash[$iStoreId]))
        {
            $sHtml .= '' . $aDescHash[$iStoreId];
        }
        
	    $sHtml .= '</div></li>';
        
        return $sHtml;         
    }

    
}

?>