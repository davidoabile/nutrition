<?php

class Moogento_EasyCoupon_Model_Observer extends Varien_Event_Observer
{
    const TARGET_CART = 'cart';
    const TARGET_PRODUCT = 'product';
    const TARGET_CHECKOUT = 'checkout';

    public function easyCouponAdd($observer)
    {

        $request  = Mage::app()->getRequest();
        if(!$request){
            return;
        }
        $qVars = $request->getParams();
        if(!$qVars || !is_array($qVars) || (sizeof($qVars) == 0)){
            return;
        }

        $this->_checkShortLinks($qVars);

        $coupon = Mage::helper('core')->stripTags($request->getParam('coupon'), null, true);
        if(!$coupon){
            return;
        }
        $productIds = $request->getParam('ezid', array());
        if (!array($productIds)) {
            $productIds = array($productIds);
        }
        $productSkus = $request->getParam('ezsku', array());
        if (!is_array($productSkus)) {
            $productSkus = array($productSkus);
        }
        $productQtys = $request->getParam('ezqty', array());
        if (!is_array($productQtys)) {
            $productQtys = array($productQtys);
        }

        $target = Mage::helper('core')->stripTags($request->getParam('target'), null, true);
        if(!$coupon && !count($productIds) && !count($productSkus) && !$target){
            return;
        }

        $productToRedirect = $this->_addProducts($productIds, $productSkus, $productQtys);

        $customerSession = $this->_getSession();

        if ($coupon) {
            $addCoupon = $this->setCoupon($coupon);
            if (!$addCoupon) {
                $customerSession->setRemoveCouponCodes(0);
                $customerSession->setIsCouponAdded(0);
            } else {
                $customerSession->setCouponCode($coupon);
            }
        }


        if(isset($target) && !empty($target)){

            switch($target){
                case self::TARGET_CART:
                    $this->_preservMessages();
                    $responce = Mage::app()->getResponse();
                    $cartUrl = Mage::getUrl("checkout/cart");
                    $responce->setRedirect($cartUrl);
                    break;

                case self::TARGET_PRODUCT:
                    if ($productToRedirect) {
                        $this->_preservMessages();
                        $productUrl = $productToRedirect->getProductUrl();
                        $responce   = Mage::app()->getResponse();
                        $responce->setRedirect($productUrl);
                    }
                    break;

                case self::TARGET_CHECKOUT:
                    $this->_preservMessages();
                    $checkoutUrl = Mage::helper('checkout/url')->getCheckoutUrl();
                    $responce = Mage::app()->getResponse();
                    $responce->setRedirect($checkoutUrl);
                    break;

                default:
            }

        }
    }

    protected function _addProducts($productIds, $skus, $qties)
    {
        $productToRedirect = false;
        foreach ($productIds as $index => $productId) {
            $productId = (int) $productId;
            if (!$productId) {
                continue;
            }
            $product = $this->_initProduct($productId);
            if ($product->getId() && $product->isVisibleInCatalog()) {
                if (!$productToRedirect) {
                    $productToRedirect = $product;
                }
                try {
                    $qty = isset($qties[$index]) ? (int)$qties[$index] : 1;
                    $this->_getCart()->getQuote()->addProduct($product, $qty);
                } catch (Exception $e){
                }
            }
        }

        foreach ($skus as $index => $sku) {
            $product = $this->_initProductBySku($sku);
            if ($product && $product->getId() && $product->isVisibleInCatalog()) {
                if (!$productToRedirect) {
                    $productToRedirect = $product;
                }
                try {
                    $qty = isset($qties[$index]) ? (int)$qties[$index] : 1;
                    $this->_getCart()->getQuote()->addProduct($product, $qty);
                } catch (Exception $e){
                }
            }
        }

        try{
            $this->_getCart()->save();
            $this->_getSession('checkout')->setCartWasUpdated(true);
        } catch(Exception $e){
        }

        return $productToRedirect;
    }

    /**
     * @param $observer
     */
    public function easyCouponUpdate($observer){
        $quote = null;
        $helper = Mage::helper('moogento_easycoupon');
        $customerSession = $this->_getSession();
        $sessionCouponCode = $customerSession->getCouponCode();
        $request  = Mage::app()->getRequest();
        $removeCoupon = $request->getParam('remove');
        if($removeCoupon){
            $customerSession->unsCouponCode($sessionCouponCode);
            $sessionCouponCode = '';
        }
        if($sessionCouponCode && $this->_checkCouponCode($sessionCouponCode)){
            if(!$removeCoupon){
                $quote = $observer->getEvent()->getQuote();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quoteCouponCode = $quote->getCouponCode();
                if($sessionCouponCode != $quoteCouponCode){
                    $quote->setCouponCode(strlen($sessionCouponCode) ? $sessionCouponCode : '');
                    $coupon_message_bar = $helper->__('Coupon code "%s" is running.', $sessionCouponCode);
                    $customerSession->setCouponStatus($coupon_message_bar);

                }
            }
        } else {
            $customerSession->setCouponStatus('');
            if(strlen($sessionCouponCode) > 0){
                $coupon_message = $helper->__('Coupon code "%s" is not valid. Please try by another one. Thank you!', $sessionCouponCode);
                $coreSession = $this->_getSession('core');
                $coreSession->addError($coupon_message);
            }

        }

    }

    /**
     * @param $couponCode
     * @return bool
     */
    public function setCoupon($couponCode)
    {
        $helper = Mage::helper('moogento_easycoupon');
        $customerSession = $this->_getSession();
        $request  = Mage::app()->getRequest();
        $productId = $request->getParam('product');
        $coreSession = $this->_getSession('core');
        if ($this->_checkCouponCode($couponCode)) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if($couponCode != $quote->getCouponCode()){
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode(strlen($couponCode) ? $couponCode : '');
                $quote->collectTotals();
                $quote->save();
                if (Mage::getModel('core/cookie')->get("esycpn")) {
                    Mage::getModel('core/cookie')
                        ->set("esycpn", "enable_bounce", time() + 86400, '/', null, null, false);
                }

                if ($couponCode === $quote->getCouponCode()) {
                    $coupon_message = $helper->__('Coupon code "%s" was applied successfully', $couponCode);
                    $this->_loadCouponInfo($couponCode);
                    $coupon_message_bar = $helper->__('Coupon code "%s" is running.', $couponCode);
                    $coreSession->addSuccess($coupon_message);
                    $customerSession->setCouponStatus($coupon_message_bar);
                    return true;
                } else {
                    $coupon_message        = $helper->__('Coupon code "%s" is saved!', $couponCode);
                    $empty_message         = $helper->__('This will be automatically used when you checkout. Thank you!');
                    $this->_loadCouponInfo($couponCode);
                    $coupon_message_bar = $helper->__('Coupon code "%s" is running.', $couponCode);
                    $customerSession->setCouponStatus($coupon_message_bar);

                    $coreSession->addSuccess($coupon_message);
                    $coreSession->addSuccess($empty_message);
                    return false;
                }
            }

        } else {
            $productAdded = false;
            if($productId){
                $productAdded = Mage::helper('moogento_easycoupon')->isInCart($productId);
            }

            if($productAdded){
                $coupon_message = $helper->__('The coupon you used ("%s") isn’t valid - we’ve added that product to the cart, so you can still grab it!', $couponCode);
            } else {
                $coupon_message = $helper->__('Coupon code "%s" is not valid. Please try by another one. Thank you!', $couponCode);
            }
            $customerSession->setCouponStatus('');

            $coreSession->addError($coupon_message);
            return false;
        }

        return false;
    }

    protected function _checkCouponCode($couponCode)
    {
        if ($couponCode) {
            $coupon = Mage::getModel('salesrule/coupon')->loadByCode($couponCode);

            if ($coupon->getId()) {
                $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
                if ($rule->getIsActive()) {
                    return true;
                }
            }
            $session = $this->_getSession();
            $session->unsCouponCode();
        }

        return false;
    }

    protected function _loadCouponInfo($couponCode)
    {
        $customerSession = $this->_getSession();
        $rule =Mage::getModel('salesrule/coupon')->loadByCode($couponCode);
        if ($rule->getId()) {
            $ruleDetail = Mage::getModel('salesrule/rule')->load($rule->getId());
            $customerSession->setCouponCode($couponCode);
            $customerSession->setCouponName($ruleDetail->getData('name'));
            $customerSession->setCouponDescription($ruleDetail->getData('description'));
        }
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getCart(){
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * @param $productId
     * @return bool
     */
    protected function _initProduct($productId){
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
    protected function _initProductBySku($sku){
        if ($sku) {
            $product = Mage::getModel('catalog/product')
                           ->setStoreId(Mage::app()->getStore()->getId());
            $product->load($product->getIdBySku($sku));
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession($type = 'customer')
    {
        switch($type){
            case 'core':
                return Mage::getSingleton('core/session');
                break;
            case 'catalog':
                return Mage::getSingleton('catalog/session');
                break;

            case 'checkout':
                return Mage::getSingleton('checkout/session');
                break;

            default:
            return Mage::getSingleton('customer/session');
        }
    }

    protected function _checkShortLinks($getVars)
    {
        $possibleShortLinks = array();
        foreach ($getVars as $key => $value) {
            if (strlen($key) == 2 || strlen($key) == 3) {
                $possibleShortLinks[] = $key;
            }
        }

        if (count($possibleShortLinks)) {
            $collection = Mage::getResourceModel('moogento_easycoupon/shortlink_collection');
            $collection->addFieldToFilter('shortlink', array('in' => $possibleShortLinks));
            $storeOptions = array(
                'website_' . Mage::app()->getWebsite()->getCode(),
                'store_' . Mage::app()->getStore()->getCode(),
            );
            $collection->getSelect()->where('website is null OR website in (?)', new Zend_Db_Expr('"' . implode('","', $storeOptions) . '"'));

            $shortLink = $collection->getFirstItem();
            if ($shortLink->getId()) {
                if ($shortLink->getCoupon()) {
                    Mage::app()->getRequest()->setParam('coupon', $shortLink->getCoupon());
                }
                if ($shortLink->getTarget()) {
                    Mage::app()->getRequest()->setParam('target', $shortLink->getTarget());
                }
                if ($shortLink->getSkus() && count($shortLink->getSkus())) {
                    $ezsku = array();
                    $ezqty = array();
                    foreach ($shortLink->getSkus() as $index => $skuData) {
                        $sku = isset($skuData['sku']) ? trim($skuData['sku']) : false;
                        if ($sku) {
                            $ezsku[$index] = $sku;
                            $ezqty[$index] = isset($skuData['qty']) && $skuData['qty'] > 0 ? (int)$skuData['qty'] : 1;
                        }
                    }
                    Mage::app()->getRequest()->setParam('ezsku', $ezsku);
                    Mage::app()->getRequest()->setParam('ezqty', $ezqty);
                }
            }
        }
    }

    /**
     * save messages before redirect
     */
    public function _preservMessages(){
        $coreSession = $this->_getSession('core');
        $messages = $coreSession->getMessages()->getItems();
        $coreSession->getMessages(true);
        $coreSession->setCustomMessages($messages);
        return;
    }

    /**
     * restore messages after redirect;
     */
    public function restoreMessages(){
        $coreSession = $this->_getSession('core');
        $messages = $coreSession->getCustomMessages();
        if($messages){
            $coreSession->getMessages(true);
            $coreSession->addMessages($messages);
            $coreSession->setCustomMessages(false);
        }
    }

    public function restoreCartMessages(){
        $coreSession = $this->_getSession('core');
        $messages = $coreSession->getCustomMessages();
        $checkoutSession = $this->_getSession('checkout');
        if($messages){
            $coreSession->getMessages(true);
            $checkoutSession->addMessages($messages);
            $coreSession->setCustomMessages(false);
        }
    }

    public function adminhtml_promo_quote_edit_tab_main_prepare_form($observer)
    {
        $rule = Mage::registry('current_promo_quote_rule');
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElements()->searchById('base_fieldset');
        $barImage = $rule->getData('easycoupon_bar_image');
        if ($barImage && !is_array($barImage)) {
            $barImage = array('value' => $barImage);
        }
        $fieldset->addField('bar_image', 'image', array(
            'name' => 'easycoupon_bar_image',
            'label' => Mage::helper('moogento_easycoupon')->__('easyCoupon Bounce-bar Image'),
            'note' => Mage::helper('moogento_easycoupon')->__('Recomended Dimensions : 308 x 96px'),
            'value' => $barImage ?
                Mage::getBaseUrl('media') . '/moogento/easycoupon/rules/' . $barImage['value'] :
                null
        ));
        $fieldset->addField('easycoupon_bar_message', 'textarea', array(
            'name' => 'easycoupon_bar_message',
            'label' => Mage::helper('moogento_easycoupon')->__('easyCoupon Bounce-bar message'),
            'note' => Mage::helper('moogento_easycoupon')->__('Template for bounce-bar message. Use these:
								<br /><b>[coupon code]</b>
								<br /><b>[coupon name]</b>
                                <br /><b>[coupon description]</b>'),
            'value' => $rule->getData('easycoupon_bar_message'),
        ));
        $fieldset->addField('easycoupon_bar_color', 'text', array(
            'name' => 'easycoupon_bar_color',
            'class' => 'color {required:false}',
            'label' => Mage::helper('moogento_easycoupon')->__('easyCoupon Bounce-bar text color'),
            'value' => $rule->getData('easycoupon_bar_color'),
        ));
        $fieldset->addField('easycoupon_bar_background', 'text', array(
            'name' => 'easycoupon_bar_background',
            'class' => 'color {required:false}',
            'label' => Mage::helper('moogento_easycoupon')->__('easyCoupon Bounce-bar background color'),
            'value' => $rule->getData('easycoupon_bar_background'),
        ));
    }

    public function adminhtml_block_html_before($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Promo_Quote_Edit_Form) {
            $block->getForm()->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
        }
    }

    public function salesrule_rule_save_before($observer)
    {
        $rule = $observer->getEvent()->getRule();

        if (isset($_FILES['easycoupon_bar_image']) && $_FILES['easycoupon_bar_image']['tmp_name']){

            $uploadDir = Mage::getBaseDir('media') . '/moogento/easycoupon/rules/';

            $result = false;
            try {
                $uploader = new Mage_Core_Model_File_Uploader($_FILES['easycoupon_bar_image']);
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($uploadDir);
            } catch (Exception $e) {
            }

            if ($result && $result['file']) {
                $rule->setData('easycoupon_bar_image', $result['file']);
            }
        } else {
            $post = Mage::app()->getRequest()->getPost('easycoupon_bar_image', false);
            if (is_array($post)) {
                if (isset($post['delete'])) {
                    $rule->setData('easycoupon_bar_image', null);
                } else {
                    $rule->setData('easycoupon_bar_image', $rule->getOrigData('easycoupon_bar_image'));
                }
            }
        }
    }
}
