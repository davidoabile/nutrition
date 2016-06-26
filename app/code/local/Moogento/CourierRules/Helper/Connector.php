<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 09.02.15
 * Time: 15:54
 */

class Moogento_CourierRules_Helper_Connector extends Mage_Core_Helper_Abstract
{
    const SOAP_RESPONSE_STATUS_SUCCESS = 'SUCCESS';

    const SOAP_RESPONSE_STATUS_NOTE = 'NOTE';

    const SOAP_RESPONSE_STATUS_WARNING = 'WARNING';

    const SOAP_RESPONSE_STATUS_ERROR = 'ERROR';

    public function parseConnectorMethod($method)
    {
        $methodArray = explode(':', $method);

        if((count($methodArray) == 4) && ($methodArray[0] == 'connect')) {
            $manager = Mage::getSingleton('moogento_courierrules/connector_manager');
            list(, $connectorCode, $carrierCode, $servicesId) = $methodArray;
            $connector = $manager->getConnector($connectorCode);
            if($connector) {
                $return['connector'] = $connector;
                $return['carrier'] = $connector->getCarrier($carrierCode);
                $return['service'] = $connector->getCarrier($carrierCode)->getService($servicesId);
                return $return;
            }
        }
        return false;
    }

    public function loadSuggestions($order)
    {

        $manager = Mage::getSingleton('moogento_courierrules/connector_manager');
        $connectors = $manager->getConnectorCollection();

        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress) {
            $suggestions = array();
            $country = $shippingAddress->getCountryId();
            $postcode = $shippingAddress->getPostcode();
            foreach ($connectors as $connector) {
                try {
                    $services = $connector->findServices($country, $postcode);
                    if (count($services)) {
                        $suggestions[$connector->getCode()] = $services;
                    }
                } catch (Exception $e) {
                    // todo add logging
                }
            }
            $connectorSuggestion = Mage::getModel('moogento_courierrules/connector_suggestion')->load($order->getId(), 'order_id');

            if (!$connectorSuggestion->getId()) {
                $connectorSuggestion = Mage::getModel('moogento_courierrules/connector_suggestion');
                $connectorSuggestion->setOrderId($order->getId());
            }
            $connectorSuggestion->addData(array(
                'suggestion' => Mage::helper('core')->jsonEncode($suggestions),
            ));;
            $connectorSuggestion->save();
            return true;
        }

        return false;
    }

    public function getUsedInRules($connectorCode, $carrierCode)
    {
        $rules = Mage::getResourceModel('moogento_courierrules/rule_collection');
        $rules->getSelect()->where('courierrules_method LIKE "connect:' . $connectorCode . ':' . $carrierCode . '%"');

        $services = array();
        foreach ($rules as $rule) {
            $services[$rule->getService()->getCode()] = 'Used in rule #' . $rule->getSort() . ' ' . $rule->getName();
        }

        return $services;
    }

    public function getConnectorLabels($order)
    {
        if (is_numeric($order)) {
            $orderId = $order;
        } else {
            $orderId = $order->getId();
        }
        $collection = Mage::getModel('moogento_courierrules/connector')->getCollection();
        $collection->getSelect()->join(
            array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
            'main_table.shipment_id = shipment.entity_id',
            array('shipment.increment_id')
        );
        $collection->getSelect()->where('shipment.order_id = ?', $orderId);

        $labels = array();
        foreach ($collection as $connector) {
            $labels = array_merge($labels, $connector->getLabels());
        }

        return $labels;
    }
}