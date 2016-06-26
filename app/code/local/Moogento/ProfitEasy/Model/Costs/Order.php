<?php


class Moogento_ProfitEasy_Model_Costs_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('moogento_profiteasy/costs_order');
    }

    public function calculate($amount)
    {
        switch ($this->getCalculationType()) {
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED:
                return $this->getCost();
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_PERCENT:
                return $amount * $this->getCost() * 0.01;
        }

        return 0;
    }

    public function calculateTimed($amount, $count)
    {
        switch ($this->getCalculationType()) {
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_FIXED:
                return $this->getCost() / $count;
            case Moogento_ProfitEasy_Helper_Data::CALCULATE_PERCENT:
                return $amount * $this->getCost() * 0.01;
        }

        return 0;
    }
} 