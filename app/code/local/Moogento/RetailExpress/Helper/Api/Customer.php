<?php

class Moogento_RetailExpress_Helper_Api_Customer extends Mage_Core_Helper_Abstract {

    public function buildOrderXml($doc, $orderXml, $order) {
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if ((int) $customer->getRetailExpressId() === 0 && (int) $order->getCustomerId() > 0 ) {
            $rexCustomerObejct = new NWH_RetailExpress_Model_Customer_Customer();
            $result = $rexCustomerObejct->putCustomer($customer);
            if (isset($result['data']['seqno']) && (int) $result['data']['seqno'] > 0) {
                $customer->setRetailExpressId($result['data']['seqno']);
            }
        }
        if ((int) $customer->getRetailExpressId() > 0) {

            if ($order->getCustomerId()) {
                $item = $doc->createElement("CustomerId");
                $item->appendChild($doc->createTextNode($customer->getRetailExpressId()));
                $orderXml->appendChild($item);
            }
            $item = $doc->createElement("Password");
            $item->appendChild($doc->createTextNode("RetailPassword"));
            $orderXml->appendChild($item);

            $billingAddress = $order->getBillingAddress();

            $item = $doc->createElement("BillFirstName");
            $item->appendChild($doc->createTextNode($billingAddress->getFirstname()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillLastName");
            $item->appendChild($doc->createTextNode($billingAddress->getLastname()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillAddress");
            $item->appendChild($doc->createTextNode($billingAddress->getStreet(1)));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillAddress2");
            $item->appendChild($doc->createTextNode($billingAddress->getStreet(2)));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillCompany");
            $item->appendChild($doc->createTextNode($billingAddress->getCompany()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillPhone");
            $item->appendChild($doc->createTextNode($billingAddress->getTelephone()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillPostCode");
            $item->appendChild($doc->createTextNode($billingAddress->getPostcode()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillState");
            $item->appendChild($doc->createTextNode($billingAddress->getRegion()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillSuburb");
            $item->appendChild($doc->createTextNode($billingAddress->getCity()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillCountry");
            $item->appendChild($doc->createTextNode($billingAddress->getCountry()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillEmail");
            $item->appendChild($doc->createTextNode($billingAddress->getEmail()));
            $orderXml->appendChild($item);

            $shippingAddress = $order->getShippingAddress();
            if (!$shippingAddress) {
                $shippingAddress = $billingAddress;
            }

            $item = $doc->createElement("DelName");
            $item->appendChild($doc->createTextNode($shippingAddress->getFirstname() . ' '
                            . $shippingAddress->getLastName()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelAddress");
            $item->appendChild($doc->createTextNode($shippingAddress->getStreet(1)));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelAddress2");
            $item->appendChild($doc->createTextNode($shippingAddress->getStreet(2)));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelCompany");
            $item->appendChild($doc->createTextNode($shippingAddress->getCompany()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelPhone");
            $item->appendChild($doc->createTextNode($shippingAddress->getTelephone()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelPostCode");
            $item->appendChild($doc->createTextNode($shippingAddress->getPostcode()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelSuburb");
            $item->appendChild($doc->createTextNode($shippingAddress->getCity()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelState");
            $item->appendChild($doc->createTextNode($shippingAddress->getRegion()));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelCountry");
            $item->appendChild($doc->createTextNode($shippingAddress->getCountry()));
            $orderXml->appendChild($item);
        } else {

            $data = Mage::getStoreConfig('moogento_retailexpress/customer');

            $item = $doc->createElement("CustomerId");
            $item->appendChild($doc->createTextNode($data['customer_id']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("Password");
            $item->appendChild($doc->createTextNode("RetailPassword"));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillFirstName");
            $item->appendChild($doc->createTextNode($data['firstname']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillLastName");
            $item->appendChild($doc->createTextNode($data['lastname']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillAddress");
            $item->appendChild($doc->createTextNode($data['address']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillAddress2");
            $item->appendChild($doc->createTextNode($data['address2']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillCompany");
            $item->appendChild($doc->createTextNode($data['company']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillPhone");
            $item->appendChild($doc->createTextNode($data['phone']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillPostCode");
            $item->appendChild($doc->createTextNode($data['postcode']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillState");
            $item->appendChild($doc->createTextNode($data['state']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillSuburb");
            $item->appendChild($doc->createTextNode($data['city']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillCountry");
            $item->appendChild($doc->createTextNode($data['country']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("BillEmail");
            $item->appendChild($doc->createTextNode($data['email']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelName");
            $item->appendChild($doc->createTextNode($data['firstname'] . ' '
                            . $data['lastname']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelAddress");
            $item->appendChild($doc->createTextNode($data['address']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelAddress2");
            $item->appendChild($doc->createTextNode($data['address2']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelCompany");
            $item->appendChild($doc->createTextNode($data['company']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelPhone");
            $item->appendChild($doc->createTextNode($data['phone']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelPostCode");
            $item->appendChild($doc->createTextNode($data['postcode']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelSuburb");
            $item->appendChild($doc->createTextNode($data['city']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelState");
            $item->appendChild($doc->createTextNode($data['state']));
            $orderXml->appendChild($item);

            $item = $doc->createElement("DelCountry");
            $item->appendChild($doc->createTextNode($data['country']));
            $orderXml->appendChild($item);
        }
    }

}
