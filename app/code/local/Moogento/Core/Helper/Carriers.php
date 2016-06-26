<?php


class Moogento_Core_Helper_Carriers extends Mage_Core_Helper_Abstract
{
    protected $_carriersConfig = null;

    public function addTrackingToShipment($shipment, $trackingNumber, $carrier = false, $onlyCreateModel = false)
    {
        $track = Mage::getModel('sales/order_shipment_track')
                     ->setData($this->getTrackingData($shipment->getOrder(), $trackingNumber, $carrier))
                     ->setNumber($trackingNumber);

        if (!$onlyCreateModel) {
            $shipment->addTrack($track);
        }

        return $track;
    }

    public function getTrackingData($order, $trackingNumber, $carrier = false)
    {
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
            $order->getStoreId()
        );

        $carrierConfig = $this->getCarriersConfig();

        $carrierCode = 'custom';
        $title = $order->getCourierrulesDescription() ? $order->getCourierrulesDescription() : $order->getShippingDescription();
        if ($carrier) {
            if ($carrierConfig && isset($carrierConfig[$carrier])) {
                $title = $carrierConfig[$carrier]['title'];
                if (isset($carrierInstances[strtolower($carrier)])) {
                    $carrierCode = strtolower($carrier);
                }
            }
        } else if (count($carrierConfig)) {
            $carrierInfo = $this->getCarrierForTrackingNumber($trackingNumber);
            if ($carrierInfo) {
                $title = $carrierInfo['title'];
                if (isset($carrierInstances[strtolower($carrierInfo['title'])])) {
                    $carrierCode = strtolower($carrierInfo['title']);
                }
            }
        } else {
            $method = explode('_', $order->getShippingMethod());
            $method = $method[0];
            foreach ($carrierInstances as $code => $carrier) {
                if ($carrier->isTrackingAvailable() && $code == $method) {
                    $carrierCode = $code;
                    $title       = $carrier->getConfigData('title');
                }
            }
        }

        return array(
            'title' => $title,
            'carrier_code' => $carrierCode,
        );
    }

    public function getTrackLinkData($track)
    {
        $carrierInfo = $this->getCarrierForTrackingNumber($track->getNumber());
        $data = array(
            'id' => $track->getId(),
            'title' => $this->getDefaultTitle(),
            'code'   => '',
            'number' => $track->getNumber(),
            'url' => $this->getDefaultLink(),
        );

        if ($carrierInfo) {
            $data['code'] = $carrierInfo['code'];
            $data['url'] = (!empty($carrierInfo['link'])) ? $carrierInfo['link'] : $this->getDefaultLink();
            $data['title'] = (!empty($carrierInfo['title'])) ? $carrierInfo['title'] : $this->getDefaultTitle();
            if (!empty($carrierInfo['file'])) {
                $data['image'] = '<img src="' . Mage::getBaseUrl('media') . 'moogento/core/carriers/'
                      . $carrierInfo['file'] . '" class="szy_grid_image" alt="' . $data['title'] . '" />';
            }
        }
        $data['url'] = str_replace('#tracking#', $data['number'], $data['url']);
        $postcode = $track->getShipment()->getOrder()->getShippingAddress()->getPostcode();
        $data['url'] = str_replace('#zipcode#', $postcode, $data['url']);
        $data['url'] = str_replace('#postcode#', $postcode, $data['url']);

        return $data;
    }

    public function getCarrierForTrackingNumber($trackingNumber)
    {
        foreach ($this->getCarriersConfig() as $carrierCode => $carrierInfo) {
            if ($carrierCode) {
                $lowerCode = strtolower($carrierCode);
                if ((isset($carrierInfo['length']) && $carrierInfo['length'] ? ($carrierInfo['length']
                                                                                == strlen($trackingNumber))
                    : true)
                ) {
                    if (
                        strpos(strtolower($trackingNumber), $lowerCode) === 0 || strpos($lowerCode, 'length_') === 0
                    ) {
                        return $carrierInfo;
                    }
                }
            }
        }

        return false;
    }

    public function getCarriersConfig()
    {
        if (is_null($this->_carriersConfig)) {
            $this->_carriersConfig = array();
            $textConfig = Mage::getStoreConfig('moogento_carriers/formats/list');
            if (trim($textConfig)) {
                try {
                    $textConfig = unserialize(trim($textConfig));
                    if (!is_array($textConfig)) {
                        $textConfig = array();
                    }
                    foreach($textConfig as $carrierConfig) {
                        if (isset($carrierConfig['enable']) && $carrierConfig['enable']) {
                            if (trim($carrierConfig['code'])) {
                                $this->_carriersConfig[ trim($carrierConfig['code']) ] = $carrierConfig;
                            } elseif ($carrierConfig['length']) {
                                $this->_carriersConfig[ 'length_' . $carrierConfig['length'] ] = $carrierConfig;
                            }
                        }
                    }
                } catch (Exception $e) {
                }
            }
        }
        return $this->_carriersConfig;
    }

    protected function _sortMethod($arg1, $arg2)
    {
        if (isset($arg1['sort_order']) && $arg2['sort_order']) {
            return $arg1['sort_order'] <= $arg2['sort_order'] ? 1 : -1;
        }
        return 0;
    }

    public function getDefaultLink()
    {
        $carriersConfig = $this->getCarriersConfig();
        if (is_array($carriersConfig)) {
            foreach($carriersConfig as $carrierCode => $carrierConfigData) {
                $carrierCode = strtolower($carrierCode);
                if($carrierCode == 'default' || $carrierCode == 'custom')
                {
                    return $carrierConfigData['link'];
                }
            }
        }
        if(Mage::getStoreConfig('moogento_carriers/general/default_link')) {
            return Mage::getStoreConfig('moogento_carriers/general/default_link');
        }

        return false;
    }

    public function getDefaultTitle()
    {
        $carriersConfig = $this->getCarriersConfig();
        if (is_array($carriersConfig)) {
            foreach($carriersConfig as $carrierCode => $carrierConfigData) {
                $carrierCode = strtolower($carrierCode);
                if($carrierCode == 'default' || $carrierCode == 'custom')
                {
                    return $carrierConfigData['title'];
                }
            }
        }
        if(Mage::getStoreConfig('moogento_carriers/general/default_label')) {
            return Mage::getStoreConfig('moogento_carriers/general/default_label');
        }

        return 'Custom';
    }

    public function getTrackCode($track)
    {
        $carriersConfig = $this->getCarriersConfig();

        if(is_array($carriersConfig))
        {
            foreach($carriersConfig as $internalCode => $carrierInfo) {
                if ($internalCode && strpos(strtolower($track->getNumber()), strtolower($internalCode)) !== false) {
                    return $internalCode;
                }
            }
            return strtoupper($track->getCarrierCode());
        }
        else
            return '';
    }

    public function getTrackUrl($track)
    {
        $carrierCode = $track->getCarrierCode();
        $carriersConfig = $this->getCarriersConfig();

        $url = '';

        if ($carrierCode == 'custom') {
            $found = false;

            foreach($carriersConfig as $prefix => $carrierData) {
                if ($carrierData['title'] == $track->getTitle()) {
                    $url = str_replace(
                        '#tracking#',
                        $track->getNumber(),
                        $carrierData['link']
                    );
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $url = str_replace(
                    '#tracking#',
                    $track->getNumber(),
                    $this->getDefaultLink()
                );
            }
        }
        else {
            $url = str_replace(
                '#tracking#',
                $track->getNumber(),
                $this->getDefaultLink()
            );
            foreach($carriersConfig as $prefix => $carrierData) {
                if (strtolower($carrierData['title']) == $carrierCode) {
                    $url = str_replace(
                        '#tracking#',
                        $track->getNumber(),
                        $carrierData['link']
                    );
                    break;
                }
            }
        }
        return $url;
    }
} 