<?php


class Moogento_ProfitEasy_Model_Costs extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_profiteasy/costs');
    }

    public function getOverrides($order)
    {
        $collection = Mage::getResourceModel('moogento_profiteasy/costs_order_collection')
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('rule_id', $this->getId());

        return $collection;
    }

    public function calculate($order, $amount)
    {
        $overrides = $this->getOverrides($order);
        if (count($overrides)) {
            $result = array();
            foreach ($overrides as $one) {
                if (!isset($result[$one->getLabel()])) {
                    $result[$one->getLabel()] = 0;
                }
                $result[$one->getLabel()] += $one->calculate($amount);
            }

            return $result;
        }
        switch ($this->getCalculationType($order)) {
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED:
                return $this->getCost($order);
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_PERCENT:
                return $amount * $this->getCost($order) * 0.01;
        }

        return 0;
    }

    public function calculateTimed($order)
    {
        $overrides = $this->getOverrides($order);
        $count = $this->_countOrders($order);
        if (count($overrides)) {
            $result = array();
            foreach ($overrides as $one) {
                if (!isset($result[$one->getLabel()])) {
                    $result[$one->getLabel()] = 0;
                }
                $result[$one->getLabel()] += $one->calculateTimed($order->getBaseSubtotal(), $count);
            }

            return $result;
        }
        $calculationType = $this->getCalculationType($order);
        $ruleCosts       = $this->getCost($order);
        $cost            = 0;
        switch ($calculationType) {
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED:
                $cost = $ruleCosts / $count;
                break;
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_PERCENT:
                $cost = $order->getBaseSubtotal() * $ruleCosts * 0.01;
                break;
        }

        return $cost;
    }

    protected function _countOrders($order)
    {
        $orderDate = strtotime($order->getCreatedAt());
        $year = date('Y', $orderDate);

        $orders = Mage::getResourceModel('sales/order_collection')
                      ->addFieldToFilter('status', array('nin' => array('canceled','closed')));
        if (count($this->getMonth())) {
            $month = date('m', $orderDate);
            $orders->getSelect()
                   ->where('DATE_FORMAT(created_at, "%Y-%m") = ?', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
        } else {
            $orders->getSelect()
                   ->where('DATE_FORMAT(created_at, "%Y") = ?', $year);
        }

        $storeId = $this->getStoreId($order);
        if ($this->getStoreId($order)) {
            $orders->getSelect()->where('store_id = ?', $storeId);
        }

        return $orders->count();
    }

    public function valid($charge_type)
    {
        return $this->getEnable() && $this->getChargeType() == $charge_type;
    }

    public function getCalculationType($order = false)
    {
        if ($order) {
            if (count($this->getData('store_costs'))) {
                foreach ($this->getData('store_costs') as $row) {
                    if (isset($row['store']) && $row['store'] == $order->getStoreId()) {
                        return $row['calculation_type'];
                    }
                }
            }
            return $this->getData('calculation_type');
        }

        return $this->getData('calculation_type');
    }

    public function getCost($order = false)
    {
        if ($order) {
            if (count($this->getData('store_costs'))) {
                foreach ($this->getData('store_costs') as $row) {
                    if (isset($row['store']) && $row['store'] == $order->getStoreId()) {
                        return $row['cost'];
                    }
                }
            }
            return $this->getData('cost');
        }

        return $this->getData('cost');
    }

    public function getStoreId($order)
    {
        if ($order) {
            if (count($this->getData('store_costs'))) {
                foreach ($this->getData('store_costs') as $row) {
                    if (isset($row['store']) && $row['store'] == $order->getStoreId()) {
                        return $row['store'];
                    }
                }
            }
        }

        return false;
    }
} 