<?php


class Moogento_CourierRules_Model_Connector_Gfs_Connector extends Moogento_CourierRules_Model_Connector_Abstract
{
    protected $_key = 'gfs';
    protected $_connectorType = 'soap';

    protected $_currentCarrier = null;

    protected function _initClient()
    {
        $wsdl = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'gfs.wsdl';
        $options = array(
            'trace' => 1,
            'exceptions' => false,
            //'proxy_host' => '127.0.0.1',
        );
        $this->_client = new Moogento_CourierRules_Model_Connector_Gfs_Client($wsdl, $options);
        if (Mage::getStoreConfig('moogento_connectors/gfs/url')) {
            $this->_client->__setLocation(Mage::getStoreConfig('moogento_connectors/gfs/url'));
        }
    }

    public function getClient()
    {
        if(is_null($this->_client)) {
            $this->_initClient();
        }

        return $this->_client;
    }


    public function getName()
    {
        return 'GFS';
    }

    public function getCode()
    {
        return 'gfs';
    }

    protected function _getCarrierAdditionalField($carrierCode)
    {
        $helper = Mage::helper('moogento_courierrules');
        $fields = array();

        $fields[] = array(
            'key' => 'save_not_valid',
            'label' => $helper->__('Save error shipments?'),
            'type' => 'checkbox',
            'checked' => Mage::getStoreConfig('moogento_connectors/'.$this->getCode().'/carriers_' . $carrierCode . '_save_not_valid'),
        );
        $fields[] = array(
            'key' => 'auto_create_shipment',
            'label' => $helper->__('Auto-create shipment?'),
            'type' => 'checkbox',
            'checked' => Mage::getStoreConfig('moogento_connectors/'.$this->getCode().'/carriers_' . $carrierCode . '_auto_create_shipment'),
        );
        $fields[] = array(
            'key' => 'dispatch_date',
            'label' => $helper->__("Dispatch"),
            'type' => 'select',
            'options' => array(
                array(
                    'value' => 0,
                    'label' => $helper->__('Same day'),
                ),
                array(
                    'value' => 1,
                    'label' => $helper->__('Next day'),
                ),
                array(
                    'value' => 2,
                    'label' => $helper->__('In 2 days'),
                ),
                array(
                    'value' => 3,
                    'label' => $helper->__('In 3 days'),
                ),
                array(
                    'value' => 4,
                    'label' => $helper->__('In 4 days'),
                ),
                array(
                    'value' => 5,
                    'label' => $helper->__('In 5 days'),
                ),
                array(
                    'value' => 6,
                    'label' => $helper->__('In 6 days'),
                ),
            ),
        );

        return $fields;
    }

    protected function _reformatOptions($services)
    {
        $result = array();
        foreach ($services as $services) {
            $result[] = array(
                'value' => $services->getCode(),
                'label' => $services->getLabel(),
            );
        }

        return $result;
    }

    public function processShipments($shipments)
    {
        try {
            $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Requestedshipments');

            foreach($shipments as $shipment) {
                $request->ShipRequests->addShipment($shipment);
            }
            $method = 'ProcessShipments';
            $response = $this->sendSoapRequest($method, $request);
        } catch (Exception $e) {
            foreach ($shipments as $shipment) {
                $connectorData = Mage::getModel('moogento_courierrules/connector')->load($shipment->getId());
                $connectorData->setStatus(Moogento_CourierRules_Helper_Connector::SOAP_RESPONSE_STATUS_ERROR);
                $connectorData->setStatusMessage($e->getMessage());
                $connectorData->save();
            }

            return;
        }

        foreach ($response->getShipments() as $shipmentData) {

            $connectorData = Mage::getModel('moogento_courierrules/connector')->load($shipmentData->getMagentoShipmentId());
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentData->getMagentoShipmentId());
            $connectorData->setStatus($shipmentData->getStatus());
            $connectorData->fillLabels($shipmentData->getLabelImage());
            if ($shipmentData->getStatus() == Moogento_CourierRules_Helper_Connector::SOAP_RESPONSE_STATUS_WARNING
                && !$shipmentData->getStatusDescription()
                && $connectorData->getLabel()) {
                $connectorData->setStatusMessage(Mage::helper('moogento_courierrules')->__('GFS Connection Test - please get in touch before attempting to ship'));
            }  else {
                $connectorData->setStatusMessage($shipmentData->getStatusDescription());
            }
            $connectorData->setType('gfs');
            $connectorData->setConnectorData($shipment->getOrder()->getCourierrules());
            $connectorData->setTrackingNumber($shipmentData->getTrackingNumber());

            $connectorData->setConsignment($shipmentData->getConsignmentNo());
            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($connectorData->getConnectorData());
            if (isset($connectorInfo['service'])) {
                $connectorData->setDespatchDate(date('Y-m-d', $connectorInfo['service']->getDespatchDate()));
            }
            try {
                $connectorData->save();

                $connectorLog = Mage::getModel('moogento_courierrules/connector_log');
                $connectorLog->setData(array(
                    'connector_id' => $connectorData->getId(),
                    'status' => $connectorData->getStatus(),
                    'status_message' => $connectorData->getStatusMessage(),
                    'type' => 'gfs',
                    'connector_data' => $connectorData->getConnectorData(),
                    'request_method' => 'processShipments',
                    'response' => serialize($shipmentData),
                    'consignment' => $connectorData->getConsignment(),
                ));
                $connectorLog->save();

                if ($connectorData != Moogento_CourierRules_Helper_Connector::SOAP_RESPONSE_STATUS_ERROR) {
                    $connectorInfo = Mage::helper('moogento_courierrules/connector')
                                         ->parseConnectorMethod($shipment->getOrder()->getCourierrules());
                    $carrier       = null;
                    if ($connectorInfo) {
                        $carrier = $connectorInfo['carrier'];
                    }
                    if ($connectorData->getLabel()) {
                        $shipment->setShippingLabel($connectorData->getLabel());
                    }
                    Mage::helper('moogento_core/carriers')->addTrackingToShipment($shipment, $shipmentData->getTrackingNumber(), $carrier->getLabel());
                    $shipment->save();
                }
            } catch (Exception $ex) {
                //todo add logging
            }
        }

    }

    public function commitShipments($shipments)
    {
        $carrierServiceGroups = $this->_prepareCarrierGroups($shipments);

        foreach($carrierServiceGroups as $carrier => $shipmentIds) {
            $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Commitshipments');
            $request->addCarrierServiceGroup($carrier, "ANY");
            $method = 'CommitShipments';
            $response = $this->sendSoapRequest($method, $request, false, false);

            if (isset($response->CarrierDocuments->PrintDocument->Image)) {
                $this->_saveManifest($carrier, $response->CarrierDocuments->PrintDocument->Image);
            }

            foreach ($shipmentIds as $id) {
                $connectorData = Mage::getModel('moogento_courierrules/connector')->load($id);
                $connectorData->setCommitted(1);
                $connectorData->setStatus($response->Status);
                $statusDescription = $response->CarrierDocuments->ResponseDetails->StatusDescription;
                if (!$statusDescription) {
                    $statusDescription = 'Shipment committed';
                }
                $connectorData->setStatusMessage($statusDescription);

                $connectorLog = Mage::getModel('moogento_courierrules/connector_log');
                $connectorLog->setData(array(
                    'connector_id' => $connectorData->getId(),
                    'status' => $connectorData->getStatus(),
                    'status_message' => $connectorData->getStatusMessage(),
                    'type' => 'gfs',
                    'connector_data' => $connectorData->getConnectorData(),
                    'request_method' => 'commitShipments',
                    'response' => serialize($response),
                    'consignment' => $connectorData->getConsignment(),
                ));
                $connectorLog->save();
            }
        }
    }


    public function deleteShipment($shipment)
    {
        $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Deleteshipments');
        $method = 'deleteShipments';

        $request->addShipment($shipment);

        $response = $this->sendSoapRequest($method, $request);
        if ($response->Status == Moogento_CourierRules_Helper_Connector::SOAP_RESPONSE_STATUS_ERROR
            && $response->Shipments->Status->StatusCode != '0400') {
            throw new Exception($response->Shipments->Status->StatusMessage);
        } else {
            $shipment->setStatus('DELETED');
            $shipment->fillLabels(array(
                'label' => null,
                'label_2' => null,
                'label_3' => null,
                'label_4' => null,
                'label_5' => null,
            ));
            $shipment->setStatusMessage('');
            $shipment->save();

            $connectorLog = Mage::getModel('moogento_courierrules/connector_log');
            $connectorLog->setData(array(
                'connector_id'   => $shipment->getId(),
                'status'         => $shipment->getStatus(),
                'status_message' => $shipment->getStatusMessage(),
                'type'           => 'gfs',
                'connector_data' => $shipment->getConnectorData(),
                'request_method' => 'deleteShipment',
                'response'       => serialize($response),
                'consignment'    => $shipment->getConsignment(),
            ));
            $connectorLog->save();
        }
    }

    public function findServices($country, $postcode, $carrier = null)
    {
        $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Findavailableservices');
        $request->FindServicesRequest->setCountry($country);
        $request->FindServicesRequest->setPostcode($postcode);
        if(!is_null($carrier)) {
            $request->FindServicesRequest->setCarrier($carrier);
        }

        $method = 'FindAvailableServices';
        $response = $this->sendSoapRequest($method, $request);

        $services = array();
        foreach ($response->getAvailableServices() as $avaliableService) {
            if ($avaliableService) {
                if (!isset($services[ $avaliableService->Carrier ])) {
                    $services[ $avaliableService->Carrier ] = array();
                }
                $services[ $avaliableService->Carrier ][] = $avaliableService->ServiceCode;
            }
        }

        return $services;
    }

    public function validateAddress($address)
    {
        $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Validateaddress');
        $method = 'validateAddress';
        $request->setAddress($address);
        $response = $this->sendSoapRequest($method, $request);

        $status = $response->Status;
        if ($status == Moogento_CourierRules_Helper_Connector::SOAP_RESPONSE_STATUS_SUCCESS) {
            return true;
        } else {
            throw new Exception($response->Shipments->Status->StatusMessage);
        }
    }

    public function validateShipment($shipment)
    {
        $request = Mage::getModel('Moogento_CourierRules_Model_Connector_Gfs_Client_Validateshipment');
        $request->setShipment($shipment);
        $method = 'validateShipment';
        $response = $this->sendSoapRequest($method, $request);
    }

    public function validateOrder($order, $rule = null)
    {
        $shippingAddress = $order->getShippingAddress();

        if($rule) {
            $carrier = $rule->getCarrier()->getCode();
            $ruleService = $rule->getService()->getCode();

            $country = $shippingAddress->getCountryId();
            $postcode = $shippingAddress->getPostcode();

            try {
                if ($shippingAddress) {
                    $this->validateAddress($shippingAddress);
                }
                $availableServices = $this->findServices($country, $postcode, $carrier);
            } catch (Exception $e) {
                //todo add logging
                return false;
            }

            foreach ($availableServices as $services) {
                foreach ($services as $serviceCode) {
                    if ($serviceCode == $ruleService) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}