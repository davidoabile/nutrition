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
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Date
 *
 * @author kirichenko
 */
class Aitoc_Aitcheckoutfields_Block_Field_Renderer_Multiselect extends Aitoc_Aitcheckoutfields_Block_Field_Renderer_Abstract 
{
    public function render() 
    {
                $select = Mage::getModel('core/layout')->createBlock('core/html_select')
                    ->setName($this->sFieldName . '[]')
                    ->setId($this->sFieldId)
                    ->setTitle($this->sLabel)
                    ->setClass($this->sFieldClass)
                    ->setValue($this->sFieldValue)
                    ->setExtraParams('multiple')
                    ->setOptions($this->aOptionHash);
                
                    $sHidden = '<input type="hidden" name="'.$this->sFieldName.'"  value="" />';
                    
                    return $sHidden . $select->getHtml();
    }
}

?>