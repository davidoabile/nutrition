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
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Giftreg% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Giftreg')){
    class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs_Aittmp extends AdjustWare_Giftreg_Block_Rewrite_AdminhtmlCustomerEditTabs {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs_Aittmp extends Mage_Adminhtml_Block_Customer_Edit_Tabs {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs extends Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs_Aittmp
{
    protected function _beforeToHtml()
    {
    	$mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
    
        $this->addTab('account', array(
            'label'     => Mage::helper('customer')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));

        $this->addTab('addresses', array(
            'label'     => Mage::helper('customer')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));
        
        if($mainModel->getCustomerAttributeList() && Mage::app()->getRequest()->getParam('id')>0)
        {
            $this->addTab('additional', array(
                'label'     => Mage::helper('aitcheckoutfields')->__('Additional Info'),
                'content'   => $this->getLayout()->createBlock('aitcheckoutfields/customer_edit_tab_additional')->initForm()->toHtml(),
            ));
        }

        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }
}