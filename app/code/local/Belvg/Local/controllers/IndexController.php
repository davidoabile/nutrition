<?php

class Belvg_Local_IndexController extends Mage_Core_Controller_Front_Action
{
	/*protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
	
	public function shipestimateAction(){
		
		$country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_getCart()->init();
        $this->_getCart()->save();
		$shippingHtml = $this->getLayout()->createBlock('checkout/cart_shipping')->setTemplate('ewave/temando/checkout/cart/shipping.phtml')->toHtml();
		$this->getResponse()->setBody($shippingHtml);
	}*/

	public function shopAssistenceAction()
    {
        $helper = Mage::helper('local/shopassistence');
        $return = array(
            'attribute' => $helper->nextAttributeName(),
            'select'    => $helper->getOptionsHtml(),
        );

        $return = Mage::helper('core')->jsonEncode($return);

        $this->getResponse()->setBody($return);
    }

    public function brandsToPagesAction()
    {
        $brandOptions = array();
        $attribute    = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'brand');
        if ($attribute->usesSource()) {
            $brandOptions = $attribute->getSource()->getAllOptions(false);
        }

        //print_r($brandOptions); die;

        foreach ($brandOptions AS $option) {
            if (!$this->_isSplashPage($option['value'])) {
                $data = array(
                    'option_id'       => $option['value'],
                    'display_name'    => $option['label'],
                    'other'           => 'a:0:{}',
                    'url_key'         => str_replace(' ', '-', strtolower(trim($option['label']))),
                    'display_mode'    => 'PRODUCTS',
                    'is_enabled'      => '1',
                    'include_in_menu' => '1',
                );

                $model = Mage::getModel('attributeSplash/page');
                $model->setData($data);
                $model->save();
                //print_r($data);
                //die;
            }
        }

    }

    protected function _isSplashPage($optionId) {
        $pages = Mage::getResourceModel('attributeSplash/page_collection');
        $pages->addFieldToFilter('option_id', $optionId);

        if ($pages->count()) {
            return TRUE;
        }

        return FALSE;
    }

}

?>