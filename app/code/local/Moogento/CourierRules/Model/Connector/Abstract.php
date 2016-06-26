<?php


abstract class Moogento_CourierRules_Model_Connector_Abstract extends Mage_Core_Model_Abstract
{
    protected $_key = false;
    protected $_config = null;
    protected $_connectorType = false;
    protected $_client = null;
    protected $_carriers = null;

    abstract public function getName();

    abstract public function getCode();

    public function getShippingDescription()
    {
//        /return $this->getName() . ' - ' . $this->get . ' - ' . $this->get
    }

    public function getConnectorType()
    {
        if (!isset($this->_connectorType)) {
            throw new Moogento_CourierRules_Model_Connector_Exception('Connector type is not defined');
        }
        return $this->_connectorType;
    }

    public function setConfig($config)
    {
        $this->_config = $config;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getClient()
    {
        if (is_null($this->_client)) {
            $this->_initClient();
        }

        if (!$this->_client) {
            throw new Moogento_CourierRules_Model_Connector_Exception('Failed to create connector');
        }

        return $this->_client;
    }

    protected abstract function _initClient();

    public function getCarriers()
    {
        if(is_null($this->_carriers)) {
            $conf = $this->getConfig();
            if(isset($conf['couriers']) && count($conf['couriers'])) {
                foreach($conf['couriers'] as $code => $courierConf) {
                    $courier = Mage::getModel($courierConf['model']);
                    $courier->setConnector($this);
                    $this->_carriers[$courier->getCode()] = $courier;
                }
            }
        }

        return $this->_carriers;
    }

    public function getCarrier($code)
    {
        $carriers = $this->getCarriers();
        if(isset($carriers[$code])) {
            return $carriers[$code];
        }
        return null;
    }

    public function getSystemConfig()
    {
        $helper = Mage::helper('moogento_courierrules');
        $code = $this->getCode();
        $connector = array(
            'position' => 10,
            'name' => $this->getName(),
            'enabled' => Mage::getStoreConfig('moogento_connectors/'.$code.'/enabled'),
            'html_name_prefix' => 'connectors['.$code.']',

            'config' => array(
                array(
                    'key' => 'user_id',
                    'label' => $helper->__('Username'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_connectors/'.$code.'/user_id'),
                ),
                array(
                    'key' => 'user_password',
                    'label' => $helper->__('Password'),
                    'type' => 'password',
                    'value' => Mage::getStoreConfig('moogento_connectors/'.$code.'/user_password'),
                ),
                array(
                    'key' => 'url',
                    'label' => $helper->__('URL'),
                    'type' => 'text',
                    'value' => Mage::getStoreConfig('moogento_connectors/'.$code.'/url'),
                ),
            ),

            'carriers' => array(),
        );

        $carriers = $this->getCarriers();
        foreach ($carriers as $carrierCode => $carrier) {
            $data['code'] = $carrierCode;
            $data['label'] = $carrier->getLabel();
            $data['enabled'] = Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_enabled');
            $data['services_used'] = Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_services_used') ? Mage::helper('core')->jsonDecode(Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_services_used')) : array();

            $data['services'] = $this->_reformatOptions($carrier->getServices());
            $packages = $carrier->getPackages();
            if (count($packages)) {
                $data['packages'] = $this->_reformatOptions($packages);
            }

            $data['additional_fields'] = $this->_getCarrierAdditionalField($carrierCode);

            $data['used_in_rules'] = Mage::helper('moogento_courierrules/connector')->getUsedInRules($code, $carrierCode);
            $connector['carriers'][] = $data;
        }

        return $connector;
    }

    protected function _getCarrierAdditionalField($carrierCode)
    {
        return array();
    }

    public function sendSoapRequest($function, $request, $showRequest = false, $showResponse = false)
    {
        $client = $this->getClient();
        $response = $client->$function($request);
        if($response instanceof SoapFault) {
            header("Content-Type: text/xml");
            echo $client->__getLastRequest();
            die();
            //throw new Exception($response->getMessage());
        }

        foreach($response as $row) {
            $response = $row;
        }

        if($showRequest) {
            header("Content-Type: text/xml");
            echo $client->__getLastRequest();
            $e = new Exception();
            echo $e->getTraceAsString();
            die();
        }

        if($showResponse) {
            var_dump($response);
            die();
        }

        return $response;
    }

    protected function _prepareCarrierGroups($shipments)
    {
        $carrierServiceGroups = array();
        foreach($shipments as $shipment) {
            $order = $shipment->getOrder();

            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($order->getCourierrules());
            $connector = $connectorInfo['connector'];
            $carrier = $connectorInfo['carrier'];
            if($connector->getCode() === $this->getCode())
            {
                if (!isset($carrierServiceGroups[$carrier->getCode()])) {
                    $carrierServiceGroups[$carrier->getCode()] = array();
                }
                $carrierServiceGroups[$carrier->getCode()][] = $shipment->getId();
            }
        }
        return $carrierServiceGroups;
    }

    protected function _saveManifest($carrier, $content, $type = 'pdf')
    {
        $date = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
        $filename = $date . '.' . $type;

        $folder = $this->_getManifestFolder($carrier);

        file_put_contents($folder . $filename, $content);

        $manifest = Mage::getModel('moogento_courierrules/connector_manifest');
        $manifest->setData(array(
            'connector' => $this->getCode(),
            'carrier' => $carrier,
            'file' => 'moogento/courierrules/manifests/' . $this->getCode() . '/' . $carrier . '/' . $filename,
            'date' => $date,
         ));

        $manifest->save();

        return $manifest;
    }

    protected function _getManifestFolder($carrier)
    {
        $folder = Mage::getBaseDir('media') . DS . 'moogento';
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $folder .= DS . 'courierrules';
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $folder .= DS . 'manifests';
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $folder .= DS . $this->getCode();
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $folder .= DS . $carrier;
        if (!file_exists($folder)) {
            mkdir($folder);
        }

        return $folder . DS;
    }

    abstract public function processShipments($shipments);

    abstract public function deleteShipment($shipment);

    abstract public function commitShipments($shipments);

    abstract public function validateOrder($order, $rule);
} 