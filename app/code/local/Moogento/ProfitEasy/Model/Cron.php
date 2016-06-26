<?php

class Moogento_ProfitEasy_Model_Cron
{
    public function updateProfit()
    {
        $helper = Mage::helper('moogento_profiteasy');

		$orders = Mage::getResourceModel('sales/order_collection')
					    ->addFieldToFilter('status', array('nin' => array('canceled','closed')))
					    ->addFieldToFilter('profit_calculated', array('neq' => 1));
        $orders->setOrder('created_at', 'DESC');
        $orders->setPageSize(50);

        foreach ($orders as $order) {
            $profitData = $helper->calculateProfit($order);

            $order->setData('profit_calculated', 1);
            $order->setProfitAmount($profitData['profit']);
            $order->save();
        }
    }

    public function updateProfitMidnight()
    {
        $month = Mage::getModel('core/date')->date('Y-m');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $query = 'UPDATE ' . $orderTable . ' SET profit_calculated = 0 WHERE (status NOT IN("canceled", "closed")) AND (DATE_FORMAT(created_at, "%Y-%m") = "' . $month . '")';
        $write->query($query);

        $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');
        $query = 'UPDATE ' . $orderGridTable . ' SET profit_calculated = 0 WHERE (status NOT IN("canceled", "closed")) AND (DATE_FORMAT(created_at, "%Y-%m") = "' . $month . '")';
        $write->query($query);
    }

    public function updateProfitMonth()
    {
        $month = Mage::getModel('core/date')->date('Y-m', strtotime('first day of previous month'));

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $query = 'UPDATE ' . $orderTable . ' SET profit_calculated = 0 WHERE (status NOT IN("canceled", "closed")) AND (DATE_FORMAT(created_at, "%Y-%m") = "' . $month . '")';
        $write->query($query);

        $orderGridTable = Mage::getSingleton('core/resource')->getTableName('sales/order_grid');
        $query = 'UPDATE ' . $orderGridTable . ' SET profit_calculated = 0 WHERE (status NOT IN("canceled", "closed")) AND (DATE_FORMAT(created_at, "%Y-%m") = "' . $month . '")';
        $write->query($query);
    }
}