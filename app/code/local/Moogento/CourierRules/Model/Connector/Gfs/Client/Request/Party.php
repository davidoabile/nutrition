<?php


class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Party
{
    /** @var  string */
    public $Company;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Address */
    public $ContactAddress;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Person */
    public $ContactPerson;

    public function __construct()
    {
        $this->ContactAddress = Mage::getModel('moogento_courierrules/connector_gfs_client_request_address');
        $this->ContactPerson = Mage::getModel('moogento_courierrules/connector_gfs_client_request_person');
    }

    public function setAddress($address)
    {
        $this->ContactPerson->PersonName = $address->getName();
        $this->ContactPerson->Phone = $address->getTelephone();
        $this->ContactPerson->Fax = $address->getFax();
        $this->ContactPerson->Mobile = $address->getPhone();
        $this->ContactPerson->E_Mail = $address->getEmail();

        $street = implode(' ', $address->getStreet());
        $county = $address->getRegion();
        $town = $address->getCity();
        $postcode = $address->getPostcode();
        $countryCode = $address->getCountryId();

        /*
        $street = 'SOUTH STREET';
        $county = 'WEST SUSSEX';
        $town = 'CHICHESTER';
        $postcode = 'PO19 1EJ';
        $countryCode = 'GB';
        */

        $this->ContactAddress->Street = $street;
        $this->ContactAddress->County = $county;
        $this->ContactAddress->Town = $town;
        $this->ContactAddress->Postcode = $postcode;
        $this->ContactAddress->CountryCode = $countryCode;

        // For FedEx
        //$this->ContactAddress->District = '';
        //$this->ContactAddress->UrbanCode = '';
        //$this->ContactAddress->Residential = '';
    }
} 