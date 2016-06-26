<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Helper_Bonus extends Mage_Core_Helper_Abstract
{
    public $_GiftSku = 'FreeShippingGift';
    public function messageGift($ruleid=null) {
        if(empty($ruleid)) $ruleid = Mage::getStoreConfig('ampromo/gift/ruleid');
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        $total = $totals["subtotal"]->getValue();
        $rule = Mage::getModel('salesrule/rule')->load($ruleid);
        //$conditions = unserialize($rule->getData('conditions_serialized'))['conditions'];
        //$configTotal = (float) $conditions[0]['value'];
        //$operator = $conditions[0]['operator'];
        //$attribute = $conditions[0]['attribute'];
        
        
        $configTotal = (float)$rule->getDiscountStep();
        $qty=$this->AddedGiftQty($ruleid);
        if ($qty <=0) {
            if ($total >= $configTotal) {
                return Mage::getStoreConfig('ampromo/gift/earn');//"<span>YOU"."'"."VE EARNT</span><br />A FREE GIFT!";
            } else {
                //"<span>YOU"."'"."VE ALMOST</span><br />EARNT A FREE GIFT!";
                return str_replace(['{{subtotal}}', '{{whatever}}'], [Mage::helper('checkout')->formatPrice($configTotal) , Mage::helper('checkout')->formatPrice( ($configTotal - $total)) ], Mage::getStoreConfig('ampromo/gift/almostearn'));
            }
            
          // <h2 class="ampromo-so-close">You're so close to a FREE GIFT!</h2>
            //Your SUBTOTAL is under ${{subtotal}} so you haven't earned a free gift yet! To choose a free gift,add
            // ${{whatever}} or more to your order. Click <a href="/ampromo/cart/freegift" id="view-freebies"> here </a> to view a list of our freebies.  
        } else {
            return $this->__("<span>A FREE GIFT HAS BEEN ADDED TO YOUR CART</span><br/><br/>");
        }
    }
    public function miniMessage($ruleid=null) {
        if(empty($ruleid)) $ruleid = Mage::getStoreConfig('ampromo/gift/ruleid');
        $total = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        $rule = Mage::getModel('salesrule/rule')->load($ruleid);
        $configTotal = (float)$rule->getDiscountStep();
        $qty=(int)$this->AddedGiftQty($ruleid);
        if ($qty <=0) {
            if ($total < $configTotal) {
                return 'Spend over $'.$configTotal.' and recieve a gift!';
            } else if ($total > $configTotal) {
                $floorTotal = $total - floor($total);
                $per = floor($total) % $configTotal;
                $per = $per + $floorTotal;
                if ($per == 0) {
                    return $this->__("You have earnt another free gift");
                } else {
                    $spend = $configTotal - $per;
                    $spend = Mage::helper('core')->currency($spend, true, false);
                    //return $this->__("Spend {$spend} more to get another free gift");
                    return 'Click here to view your free gifts';
                }
            } else {
                return $this->__("Free gift has been added to your cart");
            }
        }else{
            return $this->__("Free gift has been added to your cart");
        }
    }
    public function AddedGiftQty($ruleid = null) {
        $qty=Mage::registry('ampromo_added_qty');
        if(!empty($qty) && $qty > 0) return $qty;
        $qty=0;
        if(empty($ruleid)) $ruleid = Mage::getStoreConfig('ampromo/gift/ruleid');
        if(!empty($ruleid)){
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $cartItems = $quote->getAllVisibleItems();
            foreach ($cartItems as $item) {
                $option=$item->getBuyRequest()->getOptions();
                if(!empty($option)&& !empty($option['ampromo_rule_id']) && $option['ampromo_rule_id'] == $ruleid ) $qty=$qty+$item->getBuyRequest()->getQty();
            }
        }
        Mage::unregister('ampromo_added_qty');
        Mage::register('ampromo_added_qty',$qty);
        return $qty;
    }
    public function isShowPopup() {
        $products = Mage::helper('ampromo')->getNewItems();
        return (sizeof($products) >0)?true:false;
    }
    public function isBonusItem($_item){
        if($_item->getSku()==$this->_GiftSku) return true;
        $option=$_item->getBuyRequest()->getOptions();
        if(!empty($option) && !empty($option['ampromo_rule_id'])) return true;
        return false;
    }
    public function IsFreeShippingGiftInCart() {
        $freeshipping=Mage::registry('IsFreeShippingGiftInCart');
        if(!empty($freeshipping)) return $freeshipping;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        if(Mage::app()->getStore()->isAdmin()){
            $cartItems= Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllItems();
        }
        foreach ($cartItems as $item) {
            $sku = $item->getSku();
            if($sku == $this->_GiftSku) {
                Mage::register('IsFreeShippingGiftInCart',1);
                return true;
            }
        }
        return false;
    }
    public function GetFreeShippingGiftId() {
        $_id = Mage::getModel('catalog/product')->loadByAttribute('sku',$this->_GiftSku)->getEntityId();
        if(!empty($_id)) return $_id;
        return false;
    }
    public function getFreeSku(){
        $items = Mage::getSingleton('ampromo/registry')->getLimits();
        $freerule = Mage::getStoreConfig('ampromo/gift/ruleid');
        $groups = $items['_groups'];
        foreach($groups as $key=>$rule){
            if($key == $freerule){
                return implode(",",$rule['sku']);
            }
        }
        return '';
    }
    public function getBonusSku(){
        $bonus=array();
        $items = Mage::getSingleton('ampromo/registry')->getLimits();
        $freerule = Mage::getStoreConfig('ampromo/gift/ruleid');
        $groups = $items['_groups'];
        foreach($groups as $key=>$rule){
            if($key != $freerule){
                foreach($rule['sku'] as $sku){
                    $bonus[]=$sku;
                };
            }
        }
        unset($items['_groups']);
        $allowedSku = array_keys($items);
        foreach($allowedSku as $asku){
            $bonus[]=$asku;
        }
        if(!empty($bonus)) return implode(",",$bonus);
        return '';
    }
    public function get_cart_item_qty($quote){
        $cart_item=array();
        foreach ($quote->getAllVisibleItems() as $item)
        {
            $qty=$cart_item[$item->getData('sku')];
            if(!empty($qty))$cart_item[$item->getData('sku')]=$qty+$item->getQty();
            else $cart_item[$item->getData('sku')]=$item->getQty();
        }
        return $cart_item;
    }
    public function useLandingPage(){
        return false;
        //return true;
    }
    public function FreegiftLandingPage(){
        return Mage::getUrl('ampromo/cart/freegift');
    }
    public function BonusLandingPage(){
        return Mage::getUrl('ampromo/cart/bonus');
    }
    public function getBuyXBonusItem($rule= null){
        $buy1bonus=null;
        if(!$rule) return $buy1bonus;
        /*code for buy x get x bonus*/
        $buy1bonus=null;
        //Mage::log($rule, Zend_Log::DEBUG, 'bi_debug.log');
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item){
            $ruleid = explode(",",$item->getAppliedRuleIds());
            if(in_array($rule,$ruleid)){
                if($item->getParentItemId()){
                    continue;
                }
                $buy1bonus = $item->getQty();
            }
        }
        return $buy1bonus;
        /*end code*/
    }
}
