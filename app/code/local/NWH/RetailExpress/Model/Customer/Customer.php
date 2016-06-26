<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NWH_RetailExpress_Model_Customer_Customer {

    protected $url = 'http://rex.local/api/addons_retailExpress/customers';

    public function __construct() {
        $this->url = NWH_RetailExpress_Helper_Data::NWH_API . '/addons_retailExpress/customers';
    }

    public function process($customerData) {
        foreach ($customerData as $c => $customer) {
            $this->updateMagentoCustomer($customer);
        }
    }

    public function updateMagentoCustomer($c) {
        if (!isset($c['rex_id'])) {
            return false;
        }

        try {
            if (!isset($c['email'])) {
                return false;
            }

            $customer = Mage::getModel('customer/customer')->setData('website_id', $this->getWebsiteId())->loadByEmail($c['email']);
            $isNewCustomer = $customer->getId() ? false : true;
            $main = array();
            $billing = array();
            $shipping = array();
            foreach ($c as $k => $v) {
                $_t = explode('_', $k, 2);
                if (count($_t) > 1) {
                    if ('b' == $_t[0]) {
                        $billing[$_t[1]] = $v;
                    } elseif ('s' == $_t[0]) {
                        $shipping[$_t[1]] = $v;
                    }
                } else {
                    if ("subscription" == $k) {
                        $v = ($v == '0') ? false : true;
                    }

                    $main[$k] = $v;
                }
            }

            if (isset($shipping['firstname'])) {
                $_t = explode(' ', $shipping['firstname'], 2);
                $shipping['firstname'] = $_t[0];
                if (isset($_t[1])) {
                    $shipping['lastname'] = $_t[1];
                }
            }

            if (isset($billing['address']) || isset($billing['address2'])) {
                $billing['street'] = array();
                $billing['street'][] = isset($billing['address']) ? $billing['address'] : '';
                $billing['street'][] = isset($billing['address2']) ? $billing['address2'] : '';
            }

            if (isset($shipping['address']) || isset($shipping['address2'])) {
                $shipping['street'] = array();
                $shipping['street'][] = isset($shipping['address']) ? $shipping['address'] : '';
                $shipping['street'][] = isset($shipping['address2']) ? $shipping['address2'] : '';
            }

            $c_collection = Mage::getModel('directory/country')->getCollection();
            if (isset($billing['country_id'])) {
                foreach ($c_collection as $country) {
                    if (strtolower($country->getName()) == strtolower($billing['country_id'])) {
                        $billing['country_id'] = $country->getId();
                    }
                }
            }

            if (isset($shipping['country_id'])) {
                foreach ($c_collection as $country) {
                    if (strtolower($country->getName()) == strtolower($shipping['country_id'])) {
                        $shipping['country_id'] = $country->getId();
                    }
                }
            }

            if (!$isNewCustomer) {
                // remove password synchronization for existing customers
                unset($main['password']);
            } else {
                if (!isset($main['password'])) {
                    // generate new password if doesn't exist
                    $main['password'] = isset($main['password']) ? $main['password'] : $customer->generatePassword();
                }
            }

            if (isset($c['rex_group_id'])) {
                $groupCode = isset($c['rex_group_name']) ? $c['rex_group_name'] : ('POS Group Id ' . $c['rex_group_id']);
                $groupModel = Mage::getModel('customer/group');
                $group = $groupModel->load($groupCode, 'customer_group_code');

                if (!$group->getId()) {
                    // if group doesn't exists, create it
                    $group = Mage::getModel('customer/group')
                            ->setCode($groupCode)
                            ->setTaxClassId(Mage::getModel('customer/group')->load('1')->getTaxClassId())
                            ->save();
                }
                $group_id = $group->getId();

                $main['group_id'] = $group_id;
            } else {
                // this assigns default group for customer
                if (!$customer->getGroupId()) {
                    $customer->setGroupId(1);
                }
            }
            $main['retail_express_id'] = $c['rex_id'];
            $customer->setData('website_id', $this->getWebsiteId())->addData($main)->save();
            if (count($billing) && $isNewCustomer === true) {
                if (isset($main['firstname'])) {
                    $billing['firstname'] = $main['firstname'];
                }

                if (isset($main['lastname'])) {
                    $billing['lastname'] = $main['lastname'];
                }

                if ($customer->getDefaultBillingAddress()) {
                    $customer->getDefaultBillingAddress()
                            ->addData($billing)
                            ->save();
                } else {
                    $billing_id = Mage::getModel('customer/address')
                            ->setCustomer($customer)
                            ->addData($billing)
                            ->save()
                            ->getId();
                    $customer->setData('default_billing', $billing_id);
                }
            }

            $same_address = true;
            $_fields = array('address', 'address2', 'company', 'telephone', 'postcode', 'city', 'region', 'country_id');
            foreach ($_fields as $_f) {
                if (!isset($billing[$_f]) && !isset($shipping[$_f])) {
                    continue;
                }

                if (!isset($billing[$_f]) || !isset($shipping[$_f])) {
                    $same_address = false;
                    break;
                }

                if ($billing[$_f] != $shipping[$_f]) {
                    $same_address = false;
                    break;
                }
            }

            if (count($shipping) && $isNewCustomer === true) {
                if ($customer->getDefaultShippingAddress()) {
                    if ($customer->getDefaultBillingAddress() && ($customer->getDefaultShippingAddress()->getId() == $customer->getDefaultBillingAddress()->getId())) {
                        if (!$same_address) {
                            $shiping_id = Mage::getModel('customer/address')
                                    ->setCustomer($customer)
                                    ->addData($shipping)
                                    ->save()
                                    ->getId();
                            $customer->setData('default_shipping', $shiping_id);
                        }
                    } else {
                        $customer->getDefaultShippingAddress()
                                ->addData($shipping)
                                ->save();
                    }
                } else {
                    $shiping_id = Mage::getModel('customer/address')
                            ->setCustomer($customer)
                            ->addData($shipping)
                            ->save()
                            ->getId();
                    $customer->setData('default_shipping', $shiping_id);
                }
            }

            if (count($billing) || count($shipping) || $isNewCustomer === false) {
                $customer->save();
            }

            return array(
                'str' => "POS Customers ID " . $c['rex_id'] . " synchronised (" . $customer->getId() . ")\n",
                'new' => $isNewCustomer,
            );
        } catch (Exception $e) {
            throw new Exception("POS Customers ID " . $c['rex_id'] . " error: " . $e->getMessage() . "\n");
        }
    }

    public function getWebsiteId() {
        return Mage::getModel('core/website')->getCollection()->getFirstItem()->getId();
    }

    public function putCustomer($customer) {
        try {
            $rexData = array();
            $rexData['ExternalCustomerId'] = $customer->getId();
            $rexData['BillEmail'] = $customer->getEmail();
            $rexData['BillFirstName'] = $customer->getData('firstname');
            $rexData['BillLastName'] = $customer->getData('lastname');
            $rexData['Password'] = 'psswrd' . rand(20, 99);
            $rexData['ReceivesNews'] = (int) $customer->getIsSubscribed();
            if ($customer->getDefaultBillingAddress()) {
                $streets = $customer->getDefaultBillingAddress()->getStreet();
                $rexData['BillAddress'] = isset($streets[0]) ? $streets[0] : '';
                $rexData['BillAddress2'] = isset($streets[1]) ? $streets[1] : '';
                $rexData['BillCompany'] = $customer->getDefaultBillingAddress()->getCompany();
                $rexData['BillPhone'] = $customer->getDefaultBillingAddress()->getData('telephone');
                $rexData['BillPostCode'] = $customer->getDefaultBillingAddress()->getData('postcode');
                $rexData['BillSuburb'] = $customer->getDefaultBillingAddress()->getData('city');
                $rexData['BillState'] = $customer->getDefaultBillingAddress()->getData('region');
                $rexData['BillCountry'] = Mage::getModel('directory/country')->loadByCode($customer->getDefaultBillingAddress()->getData('country_id'))->getName();
            }

            if ($customer->getDefaultShippingAddress()) {
                $streets = $customer->getDefaultShippingAddress()->getStreet();
                $rexData['DelName'] = $customer->getDefaultShippingAddress()->getData('firstname') . " " . $customer->getDefaultShippingAddress()->getData('lastname');
                $rexData['DelAddress'] = isset($streets[0]) ? $streets[0] : '';
                $rexData['DelAddress2'] = isset($streets[1]) ? $streets[1] : '';
                $rexData['DelCompany'] = $customer->getDefaultShippingAddress()->getCompany();
                $rexData['DelPhone'] = $customer->getDefaultShippingAddress()->getData('telephone');
                $rexData['DelPostCode'] = $customer->getDefaultShippingAddress()->getData('postcode');
                $rexData['DelSuburb'] = $customer->getDefaultShippingAddress()->getData('city');
                $rexData['DelState'] = $customer->getDefaultShippingAddress()->getData('region');
                $rexData['DelCountry'] = Mage::getModel('directory/country')->loadByCode($customer->getDefaultShippingAddress()->getData('country_id'))->getName();
            }

            if ((int) $customer->getRetailExpressId() > 0) {
                $rexData['CustomerId'] = $customer->getRetailExpressId();
            }

            $result = Mage::helper('nwh_retailexpress')->getCurlJson($this->url, ['customer' => $rexData], 'POST', false );
           
            if (!isset($rexData['CustomerId']) && $result['success'] === true) {
                $updater = "INSERT INTO customer_entity_int VALUES( NULL, 1, 837, ?, ?)";
                Mage::getSingleton('core/resource')->getConnection('core_write')->query($updater, [$customer->getId(), (int) $result['data']['seqno']]);
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'reason' => $e->getMessage()];
        } finally {
            return $result;
        }
    }

}
