<?php


class Moogento_Clean_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Totals
{
    protected function _getPeriod()
    {
        $period = $this->getRequest()->getParam('period');

        if (!$period) {
            $period = Mage::getSingleton('core/cookie')->get('clean_dashboard_period');
            if (!$period) {
                $period = '24h';
            }
        } else {
            Mage::getSingleton('core/cookie')->set('clean_dashboard_period', $period, time()+86400,'/');
        }

        return $period;
    }

    protected function _prepareLayout()
    {
        $period = $this->_getPeriod();

        $totals = $this->_getTotalsData($period);

        $this->addTotal(Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ' ' . $this->__('Total'), $totals['revenue']);
        $this->addTotal($this->__('Tax'), $totals['tax']);
        $this->addTotal($this->__('Shipping Amount'), $totals['shipping']);
        $this->addTotal($this->__('Quantity'), $totals['qty']*1, true);
    }

    protected function _getTotalsData($period)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $aggregatesTable = $period == '24h' ? Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates_day') : Mage::getSingleton('core/resource')->getTableName('moogento_clean/aggregates');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $columns = array(
            'revenue' => new Zend_Db_Expr('SUM(orders_total)'),
            'tax' => new Zend_Db_Expr('SUM(orders_tax)'),
            'shipping' => new Zend_Db_Expr('SUM(orders_shipping)'),
            'qty' => new Zend_Db_Expr('SUM(orders_number)'),
        );

        switch ($period) {
            case '24h':
                $date = $date->modify('-24hour')->format('Y-m-d H:00:00');
                break;
            case '7d':
                $date = $date->modify('-7day')->format('Y-m-d H:i:s');
                break;
            case '1m':
                $date = $date->format('Y-m-01 00:00:00');
                break;
            case '1y':
                $date = $date->format('Y-01-01 00:00:00');
                break;
            case '2y':
                $date = $date->modify('-1year')->format('Y-01-01 00:00:00');
                break;
        }

        /** @var Varien_Db_Select $select */
        $select = $adapter->select();
        $select->from($aggregatesTable, $columns);

        $select->where('date >= ?', $date);
        if ($storeId = Mage::app()->getRequest()->getParam('store')) {
            $select->where('store_id = ?', $storeId);
        }

        return $adapter->fetchRow($select);
    }
} 