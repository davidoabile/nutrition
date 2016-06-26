<?php


class Moogento_CourierRules_Model_Connector_Gfs_Observer
{
    public function moogento_courierrules_config_connectors($observer)
    {
        $helper = Mage::helper('moogento_courierrules');
        $connectors = $observer->getEvent()->getConfig()->getConnectors();

        $connectorObject = Mage::getModel('moogento_courierrules/connector_manager')->getConnector('gfs');
        $connector = $connectorObject->getSystemConfig();
        $connectors[] = $connector;

        $observer->getEvent()->getConfig()->setConnectors($connectors);
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

    protected function _getCarrierAdditionalField($carrierCode, $serviceCode = null)
    {
        $helper = Mage::helper('moogento_courierrules');
        $fields = array();
        $connector = Mage::getModel('moogento_courierrules/connector_manager')->getConnector('gfs');
        $code = $connector->getCode();

        $fields[] = array(
            'key' => 'save_not_valid',
            'label' => $helper->__('Save error shipments?'),
            'type' => 'checkbox',
            'checked' => Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_save_not_valid'),
        );

        $fields[] = array(
            'key' => 'auto_create_shipment',
            'label' => $helper->__('Save error shipments?'),
            'type' => 'checkbox',
            'checked' => Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_auto_create_shipment'),
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

    public function controller_action_predispatch_adminhtml_system_config_save($observer)
    {
        $request = Mage::app()->getRequest();
        $section = $request->getParam('section');
        $connector = Mage::getModel('moogento_courierrules/connector_manager')->getConnector('gfs');
        $code = $connector->getCode();

        switch ($section) {
            case 'courierrules_connectors':
                $post_data = $request->getPost('connectors');
                $post_data = (array) $post_data;
                if (isset($post_data[$code])) {
                    $config = new Mage_Core_Model_Config();
                    foreach ($post_data[$code] as $key => $value) {
                        switch ($key) {
                            case 'carriers':
                                foreach ($value as $carrierCode => $data) {
                                    if (!isset($data['services'])) {
                                        $data['services'] = array();
                                    }
                                    foreach ($data as $field => $val) {
                                        switch ($field) {
                                            case 'services':
                                                $config->saveConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_services_used', Mage::helper('core')->jsonEncode(array_values($val)));
                                                break;
                                            default:
                                                $config->saveConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_' . $field, $val);
                                        }
                                    }
                                }
                                break;
                            default:
                                $config->saveConfig('moogento_connectors/'.$code.'/' . $key, $value);
                        }
                    }
                }
        }
    }

    public function moogento_courierrules_config_courierrules_list($observer)
    {
        $list = $observer->getEvent()->getList();
        $methods = $list->getMethods();
        $connector = Mage::getModel('moogento_courierrules/connector_manager')->getConnector('gfs');
        $code = $connector->getCode();

        if (Mage::getStoreConfigFlag('moogento_connectors/'.$code.'/enabled')) {
            foreach ($connector->getCarriers() as $data) {
                $carrierCode = $data->getCode();
                if (Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_enabled')) {
                    $courier = array(
                        'label' => 'GFS:' . $data->getLabel(),
                        'value' => array(),
                    );

                    $servicesUsed = Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_services_used') ? Mage::helper('core')->jsonDecode(Mage::getStoreConfig('moogento_connectors/'.$code.'/carriers_' . $carrierCode . '_services_used')) : array();
                    if ($servicesUsed && is_array($servicesUsed)) {
                        foreach ($servicesUsed as $service) {
                            if (isset($service['enabled']) && $service['enabled']) {
                                $courier['value'][] = array(
                                    'label' => $connector->getCarrier($carrierCode)->getService($service['code'])->getLabel(),
                                    'value' => 'connect:'.$code.':' . $carrierCode . ':' . $service['code'],
                                );
                            }
                        }
                        $methods[] = $courier;
                    }
                }
            }

        }

        $list->setMethods($methods);
    }
} 