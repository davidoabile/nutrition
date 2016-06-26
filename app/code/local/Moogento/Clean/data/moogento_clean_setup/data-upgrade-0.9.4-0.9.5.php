<?php

$installer = $this;
$this->startSetup();

$aggregatesTableVisitors = $this->getTable('moogento_clean/aggregates_visitors');
$urlTable   = Mage::getSingleton('core/resource')->getTableName('log/url_table');

$offset = Mage::getModel('core/date')->getGmtOffset("hours");

$query = "INSERT INTO {$aggregatesTableVisitors} (date, visitors)
  SELECT DATE_FORMAT(visit_time + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00'), count(DISTINCT visitor_id)
  FROM $urlTable
  GROUP BY DATE_FORMAT(visit_time + INTERVAL $offset HOUR, '%Y-%m-%d %H:00:00')
  ON DUPLICATE KEY UPDATE visitors = VALUES(visitors)";

$installer->run($query);


$this->endSetup();