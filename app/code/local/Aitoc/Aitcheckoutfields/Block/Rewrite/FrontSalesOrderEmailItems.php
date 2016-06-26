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
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderEmailItems extends Mage_Sales_Block_Order_Email_Items
{
    protected static $_excludeArray = array('paypal','epay','paypaluk');
    
    public function _toHtml()
    {
        $sContent = '';
        
        $aCustomAtrrList = $this->getOrderCustomData();
        if ($aCustomAtrrList)
        {
            $sContent .= '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
              <THEAD>
              <TR>
                <TH 
                style="'.Mage::getStoreConfig('aitcheckoutfields/email_settings/aitcheckoutfields_email_TheadTrThStyle', $this->getStoreId()).'" 
                align=left width="100%" bgColor=#EAEAEA>' . Mage::getStoreConfig('aitcheckoutfields/email_settings/aitcheckoutfields_email_label', $this->getStoreId()) . '</TH></TR></THEAD>
              <TBODY>
              <TR>
                <TD 
                style="'.Mage::getStoreConfig('aitcheckoutfields/email_settings/aitcheckoutfields_email_TbodyTrTdStyle', $this->getStoreId()).'" 
                vAlign=top>';
            
            foreach ($aCustomAtrrList as $aItem)
            {
                if($aItem['value'])
                    $sContent .= '<b>' . $aItem['label'] . ':</b> ' . Mage::helper('core')->escapeHtml($aItem['value']) . '<br>';
            }
            
            $sContent .= '</TD></TR></TBODY></TABLE><BR />';
            
        }
        
        $sContent .= parent::_toHtml();
        
        return $sContent;
    }

    public function getOrderCustomData()
    {
        $oFront = Mage::app()->getFrontController();
        $request = $oFront->getRequest();
        
        $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        
        $iOrderId = 0;
        if (in_array($request->getModuleName(), self::$_excludeArray) && $this->getOrder())
        {
            $iOrderId = $this->getOrder()->getId();
        }
        if (!$iOrderId)
        {
            $iOrderId = $request->getParam('order_id');
        }
        
        if ($iOrderId) // sent order from admin area 
        {
            $oOrder = Mage::getModel('sales/order')->load($iOrderId);
            
            $iStoreId = $oOrder->getStoreId();
            
            $aCustomAtrrList = $oAitcheckoutfields->getEmailOrderCustomData($iOrderId, $iStoreId);
        }
        
        if(empty($aCustomAtrrList)) 
        {
            $oOrder = $this->getOrder();
            
            if (!$oOrder) return false;
            
            $iStoreId = $oOrder->getStoreId();
            $sPathInfo = $request->getPathInfo();
            if (isset($_SESSION['aitoc_checkout_used']['adminorderfields']))
            {
                $sPageType = 'adminorderfields';
            }
            elseif ($sPathInfo AND strpos($sPathInfo, '/multishipping/'))
            {
                $sPageType = 'multishipping';
            }
            else 
            {
                $sPageType = 'onepage';
            }
            $aCustomAtrrList = $oAitcheckoutfields->getSessionCustomData($sPageType, $iStoreId, true);
        }
        return $aCustomAtrrList;
    }
}