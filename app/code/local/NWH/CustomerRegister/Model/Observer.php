<?php

class NWH_CustomerRegister_Model_Observer {

    public function customerRegisterSuccess(Varien_Event_Observer $observer) {
        //$event = $observer->getEvent();
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $dataCustomer = Mage::app()->getRequest()->getPost();
        $shippingCompany = $dataCustomer['shippingCompany'];
        $shippingTelephone = $dataCustomer['shippingTelephone'];
        $shippingStreet = $dataCustomer['shippingStreet'];
        $shippingCity = $dataCustomer['shippingCity'];
        $shippingRegionId = $dataCustomer['shippingRegionId'];
        $shippingRegion = $dataCustomer['shippingRegion'];
        $shippingPostcode = $dataCustomer['shippingPostcode'];
        $shippingCountryId = $dataCustomer['shippingCountryId'];
        $defaultShipping = $dataCustomer['default_shipping'];
        if (!$defaultShipping) {

            $dataShipping = array(
                'firstname' => $dataCustomer['shipping']['firstname'],
                'lastname' => $dataCustomer['shipping']['lastname'],
                'company' => $shippingCompany,
                'street' => $shippingStreet,
                'city' => $shippingCity,
                'country_id' => $shippingCountryId,
                'region' => $shippingRegion,
                'region_id' => $shippingRegionId,
                'postcode' => $shippingPostcode,
                'telephone' => $shippingTelephone
            );

            $customerAddress = Mage::getModel('customer/address');

            if ($defaultShippingId = $customer->getDefaultShipping()) {
                $customerAddress->load($defaultShippingId);
            } else {
                $customerAddress
                        ->setCustomerId($customerId)
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1')
                ;

                $customer->addAddress($customerAddress);
            }

            try {
                $customerAddress
                        ->addData($dataShipping)
                        ->save()
                ;
            } catch (Exception $e) {
                Mage::log('Address Save Error::' . $e->getMessage());
            }

            return $this;
        }
    }

    public function assignNewletter(Varien_Event_Observer $observer) {
        /*
       $quote = $observer->getQuote();
        if (in_array($quote()->getCheckoutMethod(), array('register', 'customer'))) {

            if (Mage::app()->getFrontController()->getRequest()->getParam('is_subscribed')) {
                Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
            }
        }
        return $this;
         * 
         */
    }

}
