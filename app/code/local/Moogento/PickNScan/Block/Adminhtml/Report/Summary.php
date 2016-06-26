<?php


class Moogento_PickNScan_Block_Adminhtml_Report_Summary extends Mage_Adminhtml_Block_Template
{
    public function getCapacities()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('moogento_pickscan/picking_aggregated');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)));

        $to = $date->format('Y-m-d');
        $date = $date->modify('-7days');
        $from = $date->format('Y-m-d');

        $select = new Zend_Db_Select($read);
        $select->from($table, array(
                'week_orders'          => 'SUM(orders_count)',
                'day_orders'      => 'ROUND(SUM(orders_count)/7, 2)',
                'week_items'           => 'SUM(items_count)',
                'day_items'       => 'ROUND(SUM(items_count)/7, 2)',
            ))
            ->where('period >= ?', $from)
            ->where('period <= ?', $to);

        return $read->fetchRow($select);
    }
} 