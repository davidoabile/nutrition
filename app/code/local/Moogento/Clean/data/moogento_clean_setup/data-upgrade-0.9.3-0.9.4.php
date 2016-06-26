<?php

$installer = $this;
$this->startSetup();

$aggregatesTable = $this->getTable('moogento_clean/aggregates');
$aggregatesTableDay = $this->getTable('moogento_clean/aggregates_day');
$aggregatesTableVisitors = $this->getTable('moogento_clean/aggregates_visitors');
$aggregatesTableBestsellers = $this->getTable('moogento_clean/aggregates_bestsellers');
$urlTable   = Mage::getSingleton('core/resource')->getTableName('log/url_table');
$orderTable = $this->getTable('sales/order');
$orderItemTable = $this->getTable('sales/order_item');

$offset = Mage::getModel('core/date')->getGmtOffset("hours");

$query = "INSERT INTO {$aggregatesTable} (date, store_id, is_dirty)
  SELECT DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), store_id, 1
  FROM $orderTable
  GROUP BY DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), store_id
  ON DUPLICATE KEY UPDATE is_dirty = 1";

$installer->run($query);

$query = "INSERT INTO {$aggregatesTableBestsellers} (date, store_id, sku, is_dirty)
  SELECT DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), store_id, sku, 1
  FROM $orderItemTable
  GROUP BY DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d'), store_id, sku
  ON DUPLICATE KEY UPDATE is_dirty = 1";

$installer->run($query);

$query = "INSERT INTO {$aggregatesTableDay} (date, store_id, is_dirty)
  SELECT DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00'), store_id, 1
  FROM $orderTable
  GROUP BY DATE_FORMAT(created_at + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00'), store_id
  ON DUPLICATE KEY UPDATE is_dirty = 1";

$installer->run($query);

$query = "INSERT INTO {$aggregatesTableVisitors} (date, visitors)
  SELECT DATE_FORMAT(visit_time + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00'), count(DISTINCT visitor_id)
  FROM $urlTable
  GROUP BY DATE_FORMAT(visit_time + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00')
  ON DUPLICATE KEY UPDATE visitors = VALUES(visitors)";

$installer->run($query);


$this->endSetup();