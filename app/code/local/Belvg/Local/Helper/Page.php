<?php
class Belvg_Local_Helper_Page extends Mage_Core_Helper_Data
{
    protected $_isHome = NULL;

    public function isHome()
    {
        if (is_null($this->_isHome)) {
            if (Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true))) {
                $this->_isHome = TRUE;
            }

            $this->_isHome = FALSE;
        }

        return $this->_isHome;
    }

    public function additionalMainPageClass()
    {
        $chckpage  = Mage::app()->getFrontController()->getRequest()->getRouteName();

        return ($chckpage == "onibi_storelocator") ? "location_bg" : "";
    }

    public function onlyContent()
    {
        $action   = Mage::app()->getFrontController()->getAction();
        $chckpage = Mage::app()->getFrontController()->getRequest()->getRouteName();

        if ($chckpage == "aitcheckout" || $chckpage == "checkout" || $action->getFullActionName('_') == "customer_account_login" || $action->getFullActionName('_') == "customer_account_create") {
            return TRUE;
        }

        return FALSE;
    }
}