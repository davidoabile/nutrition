<?php
class Moogento_ProfitEasy_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CHARGE_PER_ORDER = 'per_order';
    const CHARGE_PER_ITEM = 'per_item';
    const CHARGE_PER_PAYMENT = 'per_payment';
    const CHARGE_CURRENCY = 'currency';
    const CHARGE_TIMED = 'time_period';

    const CALCULATE_FIXED = 'fixed';
    const CALCULATE_PERCENT = 'percent';

    protected $_rules = null;

    public function calculateProfit($order)
    {
        $orderCosts = $this->_getOrderCosts($order);
        $itemCosts = $this->_getItemCosts($order);
        $paymentCosts = $this->_getPaymentCosts($order);
        $currencyCosts = $this->_getCurrencyCosts($order);
        $shippingCosts = $order->getBaseShippingAmount();
        if (!is_null($order->getShippingCost())) {
            $shippingCosts = $order->getShippingCost();
        }

        $timedCosts = $this->_getTimedCosts($order);

        $additionalCosts = $this->_getAdditionalCosts($order);
        $totalCosts = $shippingCosts
                      + $orderCosts['total']
                      + $itemCosts['total']
                      + $paymentCosts['total']
                      + $currencyCosts['total']
                      + $timedCosts['total']
                      + $additionalCosts['total'];

        return array(
            'order_revenue' => $order->getBaseSubtotal(),
            'shipping' => $order->getBaseShippingAmount(),
            'total_revenue' => $order->getBaseSubtotal() + $order->getBaseShippingAmount(),
            'shipping_costs' => $shippingCosts,
            'order_costs' => $orderCosts,
            'item_costs' => $itemCosts,
            'payment_costs' => $paymentCosts,
            'currency_costs' => $currencyCosts,
            'timed_costs' => $timedCosts,
            'additional_costs' => $additionalCosts,
            'total_costs' => $totalCosts,

            'profit' => $order->getBaseGrandTotal() - ($order->getBaseTaxAmount() + $totalCosts),
        );
    }

    /**
     * @return Moogento_ProfitEasy_Model_Costs[]
     */
    public function getCostRules()
    {
        if (is_null($this->_rules)) {
            $this->_rules = Mage::getResourceModel('moogento_profiteasy/costs_collection');
        }

        return $this->_rules;
    }

    protected function _calculateCosts($amount, $type, $cost)
    {
        switch ($type) {
            case self::CALCULATE_FIXED:
                return $cost;
            case self::CALCULATE_PERCENT:
                return $amount * $cost * 0.01;
        }

        return 0;
    }

    protected function _getOrderCosts($order)
    {
        $costs = array(
            'total' => 0,
        );
        foreach ($this->getCostRules() as $rule) {
            if ($rule->valid(self::CHARGE_PER_ORDER)) {
                $cost = $rule->calculate($order, $order->getBaseSubtotal());
                if (is_array($cost)) {
                    foreach ($cost as $label => $amount) {
                        $costs['total'] += $amount;
                        if (!isset($costs[ $rule->getLabel() . ' - ' . $label ])) {
                            $costs[ $rule->getLabel() . ' - ' . $label ] = 0;
                        }
                        $costs[ $rule->getLabel() . ' - ' . $label ] += $amount;
                    }
                } else {
                    $costs['total'] += $cost;
                    if (!isset($costs[ $rule->getLabel() ])) {
                        $costs[ $rule->getLabel() ] = 0;
                    }
                    $costs[ $rule->getLabel() ] += $cost;
                }
            }
        }
        return $costs;
    }

    protected function _getItemCosts($order)
    {
        $costs = array(
            'total' => 0,
        );
        foreach ($this->getCostRules() as $rule) {
            if ($rule->valid(self::CHARGE_PER_ITEM)) {
                foreach ($order->getAllVisibleItems() as $item) {
                    $cost = $rule->calculate($order, $item->getBaseRowTotal());
                    if (is_array($cost)) {
                        foreach ($cost as $label => $amount) {
                            $costs['total'] += $amount;
                            if (!isset($costs[ $rule->getLabel() . ' - ' . $label ])) {
                                $costs[ $rule->getLabel() . ' - ' . $label ] = 0;
                            }
                            $costs[ $rule->getLabel() . ' - ' . $label ] += $amount;
                        }
                    } else {
                        $costs['total'] += $cost;
                        if (!isset($costs[ $rule->getLabel() ])) {
                            $costs[ $rule->getLabel() ] = 0;
                        }
                        $costs[ $rule->getLabel() ] += $cost;
                    }
                }
            }
        }
        $costs[$this->__('Product costs')] = 0;
        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product->isComposite()) continue;
            $cost = $product->getCost() * ($item->getQtyOrdered() - $item->getQtyCancelled());
            $costs[$this->__('Product costs')] += $cost;
            $costs['total'] += $cost;
        }
        return $costs;
    }

    protected function _getPaymentCosts($order)
    {
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $costs = array(
            'total' => 0,
        );
        foreach ($this->getCostRules() as $rule) {
            if ($rule->valid(self::CHARGE_PER_PAYMENT) && $paymentCode == $rule->getPayment()) {
                $cost = $rule->calculate($order, $order->getBaseSubtotal());
                if (is_array($cost)) {
                    foreach ($cost as $label => $amount) {
                        $costs['total'] += $amount;
                        if (!isset($costs[ $rule->getLabel() . ' - ' . $label ])) {
                            $costs[ $rule->getLabel() . ' - ' . $label ] = 0;
                        }
                        $costs[ $rule->getLabel() . ' - ' . $label ] += $amount;
                    }
                } else {
                    $costs['total'] += $cost;
                    if (!isset($costs[ $rule->getLabel() ])) {
                        $costs[ $rule->getLabel() ] = 0;
                    }
                    $costs[ $rule->getLabel() ] += $cost;
                }
            }
        }
        return $costs;
    }

    protected function _getCurrencyCosts($order)
    {
        $costs = array(
            'total' => '0',
        );;
        if($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
            foreach ($this->getCostRules() as $rule) {
                if ($rule->valid(self::CHARGE_CURRENCY)) {
                    $cost = $rule->calculate($order, $order->getBaseSubtotal());
                    if (is_array($cost)) {
                        foreach ($cost as $label => $amount) {
                            $costs['total'] += $amount;
                            if (!isset($costs[ $rule->getLabel() . ' - ' . $label ])) {
                                $costs[ $rule->getLabel() . ' - ' . $label ] = 0;
                            }
                            $costs[ $rule->getLabel() . ' - ' . $label ] += $amount;
                        }
                    } else {
                        $costs['total'] += $cost;
                        if (!isset($costs[ $rule->getLabel() ])) {
                            $costs[ $rule->getLabel() ] = 0;
                        }
                        $costs[ $rule->getLabel() ] += $cost;
                    }
                }
            }
        }
        return $costs;
    }

    protected function _getTimedCosts($order)
    {
        $costs = array(
            'total' => '0',
        );
        $orderDate = strtotime($order->getCreatedAt());
        $year = date('Y', $orderDate);
        $month = date('m', $orderDate);
        foreach ($this->getCostRules() as $rule) {
            if ($rule->valid(self::CHARGE_TIMED))
            {
                if ($rule->getYear() && $rule->getYear() != $year) continue;
                if ($rule->getMonth() && !in_array($month, $rule->getMonth())) continue;

                $cost            = $rule->calculateTimed($order);
                if (is_array($cost)) {
                    foreach ($cost as $label => $one) {
                        if (!isset($result[$rule->getLabel() . ' - ' . $label])) {
                            $costs[$rule->getLabel() . ' - ' . $label] = 0;
                        }
                        $costs['total'] += $one;
                        $costs[$rule->getLabel() . ' - ' . $label] += $one;
                    }

                } else {
                    $cost            = $rule->calculateTimed($order);
                    $costs['total'] += $cost;
                    if (!isset($costs[ $rule->getLabel() ])) {
                        $costs[ $rule->getLabel() ] = 0;
                    }
                    $costs[ $rule->getLabel() ] += $cost;
                }
            }
        }

        return $costs;
    }

    protected function _getAdditionalCosts($order)
    {
        $collection = Mage::getResourceModel('moogento_profiteasy/costs_order_collection')
            ->addFieldToFilter('order_id', $order->getId());
        $collection->getSelect()->where('rule_id is null');
        $result = array(
            'total' => 0,
        );

        foreach ($collection as $one) {
            $result['total'] += $one->calculate($order->getBaseSubtotal());
            if (!isset($result[$one->getLabel()])) {
                $result[$one->getLabel()] = 0;
            }

            $result[$one->getLabel()] += $one->calculate($order->getBaseSubtotal());
        }

        return $result;
    }

    protected function _countOrders($year, $month)
    {
        $orders = Mage::getResourceModel('sales/order_collection')
                      ->addFieldToFilter('status', array('nin' => array('canceled','closed')));
        if ($month) {
            $orders->getSelect()
                   ->where('DATE_FORMAT(created_at, "%Y-%m") = ?', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
        } else {
            $orders->getSelect()
                   ->where('DATE_FORMAT(created_at, "%Y") = ?', $year);
        }

        return $orders->count();
    }

    public function getAdditionalCosts($order)
    {
        $collection = Mage::getResourceModel('moogento_profiteasy/costs_order_collection')
            ->addFieldToFilter('order_id', $order->getId());

        return $collection;
    }

    public static function setShippingCostsFilter($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        if (isset($condition['to'])) {
            $collection->getSelect()->where('IFNULL(shipping_cost, base_shipping_amount) <= ?', $condition['to']);
        }
        if (isset($condition['from'])) {
            $collection->getSelect()->where('IFNULL(shipping_cost, base_shipping_amount) >= ?', $condition['from']);
        }
    }

    public function resetDefaults()
    {
        $currencies = explode(',', Mage::getStoreConfig('currency/options/allow'));
        if (count($currencies) > 1) {
            $rule = Mage::getModel('moogento_profiteasy/costs');
            $rule->setEnable(1);
            $rule->setLabel('Currency exchange');
            $rule->setChargeType('currency');
            $rule->setCalculationType('percent');
            $rule->setCost(0);
            $rule->save();
        }

        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        foreach ($payments as $paymentCode => $paymentModel) {
            if ($paymentCode == 'paypal_standard') {
                $rule = Mage::getModel('moogento_profiteasy/costs');
                $rule->setEnable(1);
                $rule->setLabel('Paypal Standard');
                $rule->setChargeType('per_payment');
                $rule->setPayment('paypal_standard');
                $rule->setCalculationType('fixed');
                $rule->setCost(0.35);
                $rule->save();

                $rule = Mage::getModel('moogento_profiteasy/costs');
                $rule->setEnable(1);
                $rule->setLabel('Paypal Standard');
                $rule->setChargeType('per_payment');
                $rule->setPayment('paypal_standard');
                $rule->setCalculationType('percent');
                $rule->setCost(3.5);
                $rule->save();
            } else if ($paymentCode == 'ccsave') {
                $rule = Mage::getModel('moogento_profiteasy/costs');
                $rule->setEnable(1);
                $rule->setLabel('Credit card');
                $rule->setChargeType('per_payment');
                $rule->setPayment('ccsave');
                $rule->setCalculationType('fixed');
                $rule->setCost(0.75);
                $rule->save();

                $rule = Mage::getModel('moogento_profiteasy/costs');
                $rule->setEnable(1);
                $rule->setLabel('Credit card');
                $rule->setChargeType('per_payment');
                $rule->setPayment('ccsave');
                $rule->setCalculationType('percent');
                $rule->setCost(2.5);
                $rule->save();
            } else {
                $rule = Mage::getModel('moogento_profiteasy/costs');
                $rule->setEnable(1);
                $rule->setLabel(Mage::getStoreConfig('payment/'.$paymentCode.'/title'));
                $rule->setChargeType('per_payment');
                $rule->setPayment($paymentCode);
                $rule->setCalculationType('fixed');
                $rule->setCost(0);
                $rule->save();
            }
        }

        $amazonStores = array();
        $ebayStores = array();
        foreach (Mage::app()->getStores() as $store) {
            $name = strtolower($store->getName());
            if (strpos($name, 'amazon') !== false) {
                $amazonStores[] = $store->getId();
            }
            if (strpos($name, 'ebay') !== false) {
                $ebayStores[] = $store->getId();
            }
        }

        if (count($amazonStores)) {
            $rule = Mage::getModel('moogento_profiteasy/costs');
            $rule->setEnable(1);
            $rule->setLabel('Amazon');
            $rule->setChargeType('per_order');
            $rule->setCalculationType('percent');
            $rule->setCost(0);
            $storeCosts = array();
            foreach ($amazonStores as $id) {
                $storeCosts[] = array(
                    'store' => $id,
                    'calculation_type' => 'percent',
                    'cost' => 30
                );
            }
            $rule->setStoreCosts($storeCosts);
            $rule->save();

            $rule = Mage::getModel('moogento_profiteasy/costs');
            $rule->setEnable(1);
            $rule->setLabel('Amazon (charges)');
            $rule->setChargeType('time_period');
            $rule->setMonth(array('1','2','3','4','5','6','7','8','9','10','11','12'));
            $rule->setCalculationType('fixed');
            $rule->setCost(0);
            $storeCosts = array();
            foreach ($amazonStores as $id) {
                $storeCosts[] = array(
                    'store' => $id,
                    'calculation_type' => 'fixed',
                    'cost' => 39.99
                );
            }
            $rule->setStoreCosts($storeCosts);
            $rule->save();
        }

        if (count($ebayStores)) {
            $rule = Mage::getModel('moogento_profiteasy/costs');
            $rule->setEnable(1);
            $rule->setLabel('Ebay');
            $rule->setChargeType('per_order');
            $rule->setCalculationType('percent');
            $rule->setCost(0);
            $storeCosts = array();
            foreach ($ebayStores as $id) {
                $storeCosts[] = array(
                    'store' => $id,
                    'calculation_type' => 'percent',
                    'cost' => 15
                );
            }
            $rule->setStoreCosts($storeCosts);
            $rule->save();

            $rule = Mage::getModel('moogento_profiteasy/costs');
            $rule->setEnable(1);
            $rule->setLabel('Ebay');
            $rule->setChargeType('per_order');
            $rule->setCalculationType('fixed');
            $rule->setCost(0);
            $storeCosts = array();
            foreach ($ebayStores as $id) {
                $storeCosts[] = array(
                    'store' => $id,
                    'calculation_type' => 'fixed',
                    'cost' => 0.3
                );
            }
            $rule->setStoreCosts($storeCosts);
            $rule->save();
        }
    }
}
	 