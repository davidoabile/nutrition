<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 06.02.15
 * Time: 10:54
 */

abstract class Moogento_CourierRules_Model_Connector_Carrier_Abstract extends Mage_Core_Model_Abstract
{
    protected $_label = null;

    protected $_packageRequired = false;

    protected $_servicesConfig;

    protected $_packagesConfig = array();

    protected $_packages = null;

    protected $_services = null;

    protected $_limits = array(
        'name' => null,
        'company' => null,
        'street' => null,
        'district' => null,
        'town' => null,
        'county' => null,
        'reference' => null,
        'instructions' => null,
        'countent' => null,
    );

    abstract public function getCode();
    abstract public function getConnectorCode();

    public function getLabel()
    {
        return Mage::helper('moogento_courierrules')->__($this->_label);
    }

    public function getPackageRequired()
    {
        return $this->_packageRequired;
    }

    public function getServicesConfig()
    {
        return $this->_servicesConfig;
    }


    public function getPackagesConfig()
    {
        return $this->_packagesConfig;
    }

    public function getServices()
    {
        if(is_null($this->_services)) {
            $conf = $this->getServicesConfig();
            foreach($conf as $code => $label) {
                $service = Mage::getModel('moogento_courierrules/connector_carrier_service');
                $service->setCarrier($this);
                $service->setCode($code);
                $service->setLabel($label);
                $service->loadConfig();
                $this->_services[$code] = $service;
            }
        }

        return $this->_services;
    }

    public function getService($serviceId)
    {
        $services = $this->getServices();
        if(isset($services[$serviceId])) {
            $service = $services[$serviceId];
            return $service;
        }
        return null;
    }

    public function getPackages()
    {
        if(is_null($this->_packages)) {
            $conf = $this->getPackagesConfig();
            foreach($conf as $code => $label) {
                $package = Mage::getModel('moogento_courierrules/connector_carrier_package');
                $package->setCode($code);
                $package->setLabel($label);
                $this->_packages[$code] = $package;
            }
        }

        return $this->_packages;
    }

    public function getPackage($packageId)
    {
        $packages = $this->getPackages();
        if(isset($packages[$packageId])) {
            $package = $packages[$packageId];
            return $package;
        }
        return null;
    }
}