<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 20:12
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requestedshipment
{
    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Recipient */
    public $Recipient;

    /** @var  Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Requestedshipmentdetails */
    public $Shipment;

    public function __construct()
    {
        $this->Recipient = Mage::getModel('moogento_courierrules/connector_gfs_client_request_recipient');

        $this->Shipment = Mage::getModel('moogento_courierrules/connector_gfs_client_request_requestedshipmentdetails');
    }

    public function setShipment($shipment)
    {
        $shippingAddress = $shipment->getShippingAddress();
        $order = $shipment->getOrder();

        $ruleId = $order->getCourierrulesRuleId();
        $rule = Mage::getModel('moogento_courierrules/rule')->load($ruleId);

        $carrier = false;
        $service = false;
        if($rule->getId()) {
            $carrier = $rule->getCarrier();
            $service = $rule->getService();
        } else {
            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($order->getCourierrules());
            if (isset($connectorInfo['carrier'])) {
                $carrier = $connectorInfo['carrier'];
            }
            if (isset($connectorInfo['service'])) {
                $service = $connectorInfo['service'];
            }
        }

        if (!$carrier) {
            $e = new Exception('Carrier not found');
            Mage::log($order->getCourierrules(), null, 'gfs.log', true);
            Mage::log($connectorInfo, null, 'gfs.log', true);
            Mage::log($e->getTraceAsString(), null, 'gfs.log', true);
            throw $e;
        }

        if (!$service) {
            throw new Exception('Service not found');
        }

        if (!$service->getEnabled()) {
            throw new Exception('Service not enabled');
        }

        $this->Recipient->setSequenceId($shipment->getId());
        $this->Recipient->setAddress($shippingAddress);
        $this->Recipient->setShipmentReference($shipment->getIncrementId());

        $this->Shipment->CarrierService->setCarrier($carrier->getConnectorCode());
        $this->Shipment->CarrierService->setService($service->getCode());

        $this->Shipment->TotalWeight = $order->getWeight();
        $this->Shipment->ShipmentID = $shipment->getEntityId();
        $this->Shipment->SaveNotValid = $service->getSaveNotValid() ? true : false;
        if($service->getDespatchDate()) {
            $this->Shipment->DespatchDate = date('Y-m-d', $service->getDespatchDate());
        }
    }
}