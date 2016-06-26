<?php


class Moogento_CourierRules_Model_Connector_Cron
{
    public function process()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $shipmentTable = Mage::getSingleton('core/resource')->getTableName('sales/shipment');
        $connectorTable = Mage::getSingleton('core/resource')->getTableName('moogento_courierrules/connector');

        $sql = <<<HEREDOC
        SELECT s.entity_id as entity_id, o.courierrules, o.entity_id as order_id FROM {$shipmentTable} s
            LEFT JOIN {$orderTable} o on o.entity_id = s.order_id
            LEFT JOIN {$connectorTable} c on c.shipment_id = s.entity_id
        WHERE (c.shipment_id is null OR (c.status IN ('DELETED', 'ERROR') AND c.connector_data != o.courierrules))  AND o.courierrules LIKE 'connect:%'
HEREDOC;

        $newShipments = $write->fetchAll($sql);

        $outputShipments = array();
        foreach($newShipments as $shipmentInfo) {
            $shipmentId = $shipmentInfo['entity_id'];
            $method = $shipmentInfo['courierrules'];

            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($method);

            if($connectorInfo) {
                $connectorKey = $connectorInfo['connector']->getCode();
                if (!isset($outputShipments[$connectorKey])) {
                    $outputShipments[$connectorKey] = array();
                }
                $service = $connectorInfo['service'];
                if($service->getEnabled()) {
                    $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                    try {
                        $sql = "INSERT INTO $connectorTable(shipment_id, type, connector_data) values($shipmentId, '$connectorKey', '$method') ON DUPLICATE KEY UPDATE type = VALUES(type), connector_data = VALUES(connector_data)";
                        $write->query($sql);
                        $outputShipments[$connectorKey][] = $shipment;
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }

        $this->_processShipments($outputShipments);
    }

    protected function _processShipments($shipments)
    {
        foreach ($shipments as $connectorKey => $shipmentsList) {
            if (count($shipmentsList)) {
                $connector = Mage::getModel('moogento_courierrules/connector_manager')->getConnector($connectorKey);
                $connector->processShipments($shipmentsList);
            }
        }
    }

    public function commitShipments()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $shipmentTable = Mage::getSingleton('core/resource')->getTableName('sales/shipment');
        $connectorTable = Mage::getSingleton('core/resource')->getTableName('moogento_courierrules/connector');

        $despatchDate = date('Y-m-d');
        $sql = <<<HEREDOC
        SELECT s.entity_id as entity_id, o.courierrules, o.entity_id as order_id FROM {$shipmentTable} s
            LEFT JOIN {$orderTable} o on o.entity_id = s.order_id
            INNER JOIN {$connectorTable} c on c.shipment_id = s.entity_id
        WHERE c.committed = 0 AND o.courierrules LIKE 'connect:%' AND c.despatch_date = '{$despatchDate}'
HEREDOC;

        $newShipments = $write->fetchAll($sql);

        $outputShipments = array();
        foreach($newShipments as $shipmentInfo) {
            $shipmentId = $shipmentInfo['entity_id'];
            $method = $shipmentInfo['courierrules'];

            $connectorInfo = Mage::helper('moogento_courierrules/connector')->parseConnectorMethod($method);

            if($connectorInfo) {
                $connectorKey = $connectorInfo['connector']->getCode();
                if (!isset($outputShipments[$connectorKey])) {
                    $outputShipments[$connectorKey] = array();
                }
                $service = $connectorInfo['service'];
                if($service->getEnabled()) {
                    $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                    if ($shipment->getId()) {
                        $outputShipments[ $connectorKey ][] = $shipment;
                    }
                }
            }
        }

        foreach ($outputShipments as $connectorKey => $shipmentsList) {
            $connector = Mage::getModel('moogento_courierrules/connector_manager')->getConnector($connectorKey);
            $connector->commitShipments($shipmentsList);
        }
    }
} 