<?php


class Moogento_CourierRules_Model_Connector extends Mage_Core_Model_Abstract
{
    const STATUS_QUEUE = 'queue';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    protected function _construct()
    {
        $this->_init('moogento_courierrules/connector');
    }

    public function getLabelDataUri()
    {
        $uris = array();
        if ($this->getLabel()) {
            $uris[] = 'data:image/png;base64,' . base64_encode($this->getLabel());
        }
        if ($this->getData('label_2')) {
            $uris[] = 'data:image/png;base64,' . base64_encode($this->getData('label_2'));
        }
        if ($this->getData('label_3')) {
            $uris[] = 'data:image/png;base64,' . base64_encode($this->getData('label_3'));
        }
        if ($this->getData('label_4')) {
            $uris[] = 'data:image/png;base64,' . base64_encode($this->getData('label_4'));
        }
        if ($this->getData('label_5')) {
            $uris[] = 'data:image/png;base64,' . base64_encode($this->getData('label_5'));
        }
        return $uris;
    }

    public function getLabels()
    {
        $labels = array();
        if ($this->getLabel()) {
            $labels[] = $this->getLabel();
        }
        if ($this->getData('label_2')) {
            $labels[] = $this->getData('label_2');
        }
        if ($this->getData('label_3')) {
            $labels[] = $this->getData('label_3');
        }
        if ($this->getData('label_4')) {
            $labels[] = $this->getData('label_4');
        }
        if ($this->getData('label_5')) {
            $labels[] = $this->getData('label_5');
        }
        return $labels;
    }

    public function deleteShipment()
    {
        if ($this->getLabel()) {
            $connectorInfo = Mage::helper('moogento_courierrules/connector')
                                 ->parseConnectorMethod($this->getConnectorData());
            if ($connectorInfo) {
                $connectorInfo['connector']->deleteShipment($this);
            }
        }
    }

    public function fillLabels($labels)
    {
        foreach ($labels as $key => $value) {
            $this->setData($key, $value);
        }
    }
} 