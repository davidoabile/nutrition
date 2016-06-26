<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Magebright_Orderamount_CartController extends Mage_Checkout_CartController
{
	public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
            $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
            $role = strtolower($role);
            $amountData = Mage::getStoreConfig('sales/minimum_order/amount');
            $amountDatauns = unserialize($amountData);
            foreach ($amountDatauns as $values) {
                if ($values['customer_group'] == $roleId ) {
                   $amount = (float)$values['minimum_amount'];
                }
            }
            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency($amount);
                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);
                $cart->getCheckoutSession()->addNotice($warning);
            }
        }
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);
        $this->_getSession()->setCartWasUpdated(true);
        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
}
?>