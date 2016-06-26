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
class Aitoc_Aitcheckout_Block_Links extends Mage_Checkout_Block_Links
{
    protected $_rule = null; 

    /**
     * Add shopping cart link to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCartLink()
    {
        if(!$this->_checkRule() || $this->helper('aitcheckout')->isDisabled() || !$this->helper('aitcheckout')->isShowCartInCheckout())
        {
            return parent::addCartLink();
        }
        return $this;
    }

public function addCartLinkCustom()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Mage_Checkout')) {
            $count = $this->getItemsCount() ? $this->getItemsCount()
                : $this->helper('checkout/cart')->getItemsCount();
            if ($count == 1) {
                $text = $this->__('My Cart (%s item)', $count);
            } elseif ($count > 0) {
                $text = $this->__('My Cart (%s items)', $count);
            } else {
                $text = $this->__('My Cart');
            }

            $parentBlock->removeLinkByUrl($this->getUrl('checkout/cart'));
            $parentBlock->addLink($text, 'checkout/cart', $text, true, array(), 50, null, 'class="top-link-cart"');
        }
        return $this;
    }


    /**
     * Add link on checkout page to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCheckoutLink()
    {
        if (!$this->_checkRule() || $this->helper('aitcheckout')->isDisabled() || $this->helper('aitcheckout')->isShowCheckoutOutsideCart())
        {
            return parent::addCheckoutLink();
        }
        
        if ($this->helper('aitcheckout')->isShowCartInCheckout())
        {
            $parentBlock = $this->getParentBlock();
            if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Mage_Checkout')) {
                $count = $this->helper('checkout/cart')->getSummaryCount();
                
                $text = Mage::helper('checkout')->__('Checkout');
                if( $count > 0 ) {
                    $text .= " ($count ".$this->__(($count==1)?'item':'items').')';
                }
    
                $parentBlock->addLink($text, 'checkout', $text, true, array(), 50, null, 'class="top-link-checkout"');
            }
        }
        return $this;
    }
    
    private function _checkRule()
    {
        if(is_null($this->_rule))
        {
            $this->_rule = true;
            /* {#AITOC_COMMENT_END#}
            $iStoreId = Mage::app()->getStore()->getId();
            $iSiteId  = Mage::app()->getWebsite()->getId();
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckout')->getLicense()->getPerformer();
            $ruler     = $performer->getRuler();
            if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
            {
                $this->_rule = false;
            }
            {#AITOC_COMMENT_START#} */
        }
    
        return $this->_rule;
    }
}